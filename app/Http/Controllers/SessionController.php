<?php

namespace App\Http\Controllers;

use App\Models\Nas;
use App\Models\RadAcct;
use App\Services\MikrotikService;
use App\Services\RadiusService;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    protected RadiusService $radiusService;
    protected MikrotikService $mikrotikService;

    public function __construct(RadiusService $radiusService, MikrotikService $mikrotikService)
    {
        $this->radiusService = $radiusService;
        $this->mikrotikService = $mikrotikService;
    }

    public function index(Request $request)
    {
        $query = RadAcct::online();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('framedipaddress', 'like', "%{$search}%");
            });
        }

        if ($request->filled('nas')) {
            $query->where('nasipaddress', $request->input('nas'));
        }

        $sessions = $query->latest('acctstarttime')->paginate(30)->withQueryString();
        $nasList = Nas::where('is_active', true)->get();

        return view('sessions.index', compact('sessions', 'nasList'));
    }

    public function history(Request $request)
    {
        $query = RadAcct::offline();

        if ($request->filled('search')) {
            $query->where('username', 'like', '%' . $request->input('search') . '%');
        }

        if ($request->filled('from')) {
            $query->where('acctstarttime', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->where('acctstarttime', '<=', $request->input('to') . ' 23:59:59');
        }

        $sessions = $query->latest('acctstoptime')->paginate(30)->withQueryString();

        return view('sessions.history', compact('sessions'));
    }

    public function disconnect(string $username)
    {
        $this->radiusService->disconnectUser($username);

        $session = RadAcct::online()->where('username', $username)->first();
        if ($session) {
            $nas = Nas::where('nasname', $session->nasipaddress)->first();
            if ($nas) {
                $this->mikrotikService->disconnectUser($nas, $username);
            }
        }

        return redirect()->route('sessions.index')
            ->with('success', "User {$username} berhasil di-disconnect.");
    }

    public function disconnectAll()
    {
        $onlineSessions = RadAcct::online()->get();
        $count = 0;

        foreach ($onlineSessions as $session) {
            $this->radiusService->disconnectUser($session->username);
            $nas = Nas::where('nasname', $session->nasipaddress)->first();
            if ($nas) {
                $this->mikrotikService->disconnectUser($nas, $session->username);
            }
            $count++;
        }

        return redirect()->route('sessions.index')
            ->with('success', "{$count} user berhasil di-disconnect.");
    }
}
