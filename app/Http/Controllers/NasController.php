<?php

namespace App\Http\Controllers;

use App\Models\Nas;
use App\Models\RadAcct;
use App\Services\MikrotikService;
use Illuminate\Http\Request;

class NasController extends Controller
{
    protected MikrotikService $mikrotikService;

    public function __construct(MikrotikService $mikrotikService)
    {
        $this->mikrotikService = $mikrotikService;
    }

    /**
     * Display a listing of NAS devices.
     */
    public function index(Request $request)
    {
        $query = Nas::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('shortname', 'like', "%{$search}%")
                  ->orWhere('nasname', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $nasDevices = $query->latest()->paginate(15)->withQueryString();

        return view('nas.index', compact('nasDevices'));
    }

    /**
     * Show the form for creating a new NAS.
     */
    public function create()
    {
        return view('nas.create');
    }

    /**
     * Store a newly created NAS.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'shortname' => 'required|string|max:255',
            'nasname' => 'required|ip|unique:nas,nasname',
            'type' => 'required|string|max:50',
            'ports' => 'nullable|integer',
            'secret' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'api_port' => 'nullable|integer|min:1|max:65535',
            'api_username' => 'nullable|string|max:255',
            'api_password' => 'nullable|string|max:255',
            'community' => 'nullable|string|max:255',
            'server' => 'nullable|string|max:255',
        ]);

        Nas::create($validated);

        return redirect()->route('nas.index')
            ->with('success', 'NAS/Router berhasil ditambahkan.');
    }

    /**
     * Display the specified NAS.
     */
    public function show(Nas $nas)
    {
        return view('nas.show', compact('nas'));
    }

    /**
     * Show the form for editing the specified NAS.
     */
    public function edit(Nas $nas)
    {
        return view('nas.edit', compact('nas'));
    }

    /**
     * Update the specified NAS.
     */
    public function update(Request $request, Nas $nas)
    {
        $validated = $request->validate([
            'shortname' => 'required|string|max:255',
            'nasname' => "required|ip|unique:nas,nasname,{$nas->id}",
            'type' => 'required|string|max:50',
            'ports' => 'nullable|integer',
            'secret' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'api_port' => 'nullable|integer|min:1|max:65535',
            'api_username' => 'nullable|string|max:255',
            'api_password' => 'nullable|string|max:255',
            'community' => 'nullable|string|max:255',
            'server' => 'nullable|string|max:255',
        ]);

        // Keep existing secret if not provided
        if (empty($validated['secret'])) {
            unset($validated['secret']);
        }

        // Keep existing API password if not provided
        if (empty($validated['api_password'])) {
            unset($validated['api_password']);
        }

        $nas->update($validated);

        return redirect()->route('nas.index')
            ->with('success', 'NAS/Router berhasil diperbarui.');
    }

    /**
     * Remove the specified NAS.
     */
    public function destroy(Nas $nas)
    {
        $nas->delete();

        return redirect()->route('nas.index')
            ->with('success', 'NAS/Router berhasil dihapus.');
    }

    /**
     * Test connection to a NAS device via MikroTik API.
     */
    public function testConnection(Nas $nas)
    {
        try {
            $systemInfo = $this->mikrotikService->getSystemInfo($nas);

            return response()->json([
                'success' => true,
                'message' => 'Koneksi berhasil.',
                'data' => $systemInfo,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Koneksi gagal: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Show NAS status with online users count and router info.
     */
    public function status(Nas $nas)
    {
        $onlineUsers = RadAcct::online()
            ->where('nasipaddress', $nas->nasname)
            ->count();

        $onlineSessions = RadAcct::online()
            ->where('nasipaddress', $nas->nasname)
            ->latest('acctstarttime')
            ->paginate(15);

        $routerInfo = null;
        try {
            $routerInfo = $this->mikrotikService->getSystemInfo($nas);
        } catch (\Exception $e) {
            $routerInfo = ['error' => $e->getMessage()];
        }

        return view('nas.status', compact('nas', 'onlineUsers', 'onlineSessions', 'routerInfo'));
    }
}
