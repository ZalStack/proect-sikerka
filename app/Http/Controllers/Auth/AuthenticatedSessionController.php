<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user();

        // Cek apakah karyawan sudah resign
        if ($user->is_resigned) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Akun Anda sudah tidak aktif karena telah resign. Silahkan hubungi HRD untuk informasi lebih lanjut.')
                ->with('resign', true);
        }

        $request->session()->regenerate();

        // Cek posisi user
        if ($user->posisi === 'hr') {
            return redirect()->intended(route('hr.dashboard'));
        }

        if ($user->posisi === 'karyawan') {
            return redirect()->intended(route('karyawan.dashboard'));
        }

        // Default redirect jika posisi tidak dikenal
        return redirect('/');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
