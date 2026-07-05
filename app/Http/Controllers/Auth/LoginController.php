<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Employee;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'enroll_number' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('enroll_number', $request->enroll_number)->first();

        if (!$user) {
            return back()->withErrors([
                'enroll_number' => 'Nomor Enroll tidak ditemukan.',
            ]);
        }

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'Password yang Anda masukkan salah.',
            ]);
        }

        if (!$user->is_active) {
            return back()->withErrors([
                'enroll_number' => 'Akun Anda dinonaktifkan. Silakan hubungi HR.',
            ]);
        }

        Auth::login($user);

        // Update last login
        $user->update(['last_login_at' => now()]);

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        } else {
            // Check if employee profile is complete
            $employee = Employee::where('user_id', $user->id)->first();
            if (!$employee || !$employee->full_name) {
                return redirect()->route('employee.complete-profile');
            }
            return redirect()->route('employee.dashboard');
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
