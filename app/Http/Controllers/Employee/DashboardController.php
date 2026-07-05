<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Enrollment;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Get materials
        $materials = Material::where('is_active', true)
            ->with(['creator', 'quiz'])
            ->latest()
            ->take(6)
            ->get();

        // Get enrolled materials
        $enrolledMaterials = Enrollment::where('user_id', $user->id)
            ->with('material')
            ->get();

        // Get quiz attempts
        $quizAttempts = QuizAttempt::where('user_id', $user->id)
            ->with('quiz')
            ->latest()
            ->take(5)
            ->get();

        // Statistics
        $stats = [
            'total_materials' => Material::where('is_active', true)->count(),
            'completed_materials' => Enrollment::where('user_id', $user->id)
                ->where('status', 'completed')
                ->count(),
            'total_quizzes' => QuizAttempt::where('user_id', $user->id)->count(),
            'average_score' => QuizAttempt::where('user_id', $user->id)
                ->whereNotNull('score')
                ->avg('score') ?? 0,
        ];

        return view('employee.dashboard', compact(
            'materials',
            'enrolledMaterials',
            'quizAttempts',
            'stats'
        ));
    }

    public function completeProfile()
    {
        $user = Auth::user();
        return view('employee.complete-profile', compact('user'));
    }

    public function saveProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'division_id' => 'required|exists:divisions,id',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $user = Auth::user();
        $user->update([
            'name' => $request->name,
            'division_id' => $request->division_id,
        ]);

        // Create or update employee profile
        $user->employee()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'full_name' => $request->name,
                'phone' => $request->phone,
                'address' => $request->address,
                'join_date' => now(),
                'is_active' => true,
            ]
        );

        return redirect()
            ->route('employee.dashboard')
            ->with('success', 'Profil berhasil dilengkapi.');
    }
}
