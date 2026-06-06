<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Setting;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Tripay payment callback (server-to-server). Verifies the HMAC
     * signature against the invoice's tenant private key, then settles
     * the invoice and releases isolir.
     */
    public function tripayCallback(Request $request)
    {
        $raw = $request->getContent();
        $data = json_decode($raw, true) ?: [];
        $ref = $data['merchant_ref'] ?? null;

        if (! $ref) {
            return response()->json(['success' => false, 'message' => 'missing merchant_ref'], 400);
        }

        $invoice = Invoice::where('invoice_number', $ref)->first();
        if (! $invoice) {
            return response()->json(['success' => false, 'message' => 'invoice not found'], 404);
        }

        $privateKey = Setting::get($invoice->tenant_id, 'payment.tripay_private_key');
        $expected = hash_hmac('sha256', $raw, (string) $privateKey);
        $given = (string) $request->header('X-Callback-Signature');

        if (! $privateKey || ! hash_equals($expected, $given)) {
            return response()->json(['success' => false, 'message' => 'invalid signature'], 401);
        }

        if (($data['status'] ?? '') === 'PAID' && $invoice->status !== 'paid') {
            $invoice->update([
                'status' => 'paid',
                'paid_amount' => $invoice->amount,
                'paid_date' => now(),
                'payment_method' => 'tripay-qris',
            ]);

            app(InvoiceController::class)->reactivateAfterPayment($invoice);
        }

        return response()->json(['success' => true]);
    }
}
