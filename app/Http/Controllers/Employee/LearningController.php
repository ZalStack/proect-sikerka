<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LearningController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $materials = Material::where('is_active', true)
            ->with(['creator', 'quiz', 'tasks'])
            ->paginate(12);

        // Get enrollment status for each material
        foreach ($materials as $material) {
            $material->enrolled = Enrollment::where('user_id', $user->id)
                ->where('material_id', $material->id)
                ->first();
        }

        return view('employee.learning.index', compact('materials'));
    }

    public function show(Material $material)
    {
        $user = Auth::user();

        // Check if user is enrolled
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('material_id', $material->id)
            ->first();

        if (!$enrollment) {
            // Auto-enroll if not enrolled
            $enrollment = Enrollment::create([
                'user_id' => $user->id,
                'material_id' => $material->id,
                'enrolled_at' => now(),
                'status' => 'in_progress',
                'progress' => 0,
            ]);
        }

        $material->load(['creator', 'quiz.questions.options', 'tasks']);

        return view('employee.learning.show', compact('material', 'enrollment'));
    }

    public function enroll(Material $material)
    {
        $user = Auth::user();

        $existing = Enrollment::where('user_id', $user->id)
            ->where('material_id', $material->id)
            ->first();

        if (!$existing) {
            Enrollment::create([
                'user_id' => $user->id,
                'material_id' => $material->id,
                'enrolled_at' => now(),
                'status' => 'in_progress',
                'progress' => 0,
            ]);
        }

        return redirect()
            ->route('employee.learning.show', $material)
            ->with('success', 'Anda berhasil mendaftar untuk materi ini.');
    }

    public function updateProgress(Request $request, Material $material)
    {
        $request->validate([
            'progress' => 'required|integer|min:0|max:100',
        ]);

        $enrollment = Enrollment::where('user_id', Auth::id())
            ->where('material_id', $material->id)
            ->first();

        if ($enrollment) {
            $enrollment->update([
                'progress' => $request->progress,
                'status' => $request->progress >= 100 ? 'completed' : 'in_progress',
                'completed_at' => $request->progress >= 100 ? now() : null,
            ]);
        }

        return response()->json(['success' => true]);
    }
}
