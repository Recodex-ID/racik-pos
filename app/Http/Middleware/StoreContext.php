<?php

namespace App\Http\Middleware;

use App\Models\Store;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class StoreContext
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Super Admin dapat mengakses semua store
        if ($user->hasRole('Super Admin')) {
            return $next($request);
        }

        // User harus memiliki store_id
        if (! $user->store_id) {
            abort(403, 'User tidak memiliki akses ke toko manapun.');
        }

        // Verifikasi store aktif dan milik tenant yang sama
        $store = Store::where('id', $user->store_id)
            ->where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->first();

        if (! $store) {
            abort(403, 'Toko tidak aktif atau tidak dapat diakses.');
        }

        // Simpan store context untuk digunakan di aplikasi
        $request->attributes->set('current_store', $store);
        app()->instance('current_store', $store);

        return $next($request);
    }
}
