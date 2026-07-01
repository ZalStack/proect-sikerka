<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HRDashboardController extends Controller
{
    public function index()
    {
        $totalKaryawan = Karyawan::count();
        $totalHr = Karyawan::where('role', 'hr')->count();
        $totalKaryawanAktif = Karyawan::where('status', 'Full-time')->count();
        $karyawanTerbaru = Karyawan::latest()->take(5)->get();

        return view('hr.dashboard', compact(
            'totalKaryawan',
            'totalHr',
            'totalKaryawanAktif',
            'karyawanTerbaru'
        ));
    }
}
