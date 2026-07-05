<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuizController extends Controller
{
    public function index()
    {
        $quizzes = Quiz::with(['material', 'questions'])
            ->latest()
            ->paginate(10);

        return view('admin.quizzes.index', compact('quizzes'));
    }

    public function create()
    {
        $materials = Material::where('is_active', true)->get();
        return view('admin.quizzes.create', compact('materials'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'material_id' => 'required|exists:materials,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date|after:now',
            'end_date' => 'required|date|after:start_date',
            'duration_minutes' => 'required|integer|min:1|max:480',
            'passing_score' => 'required|integer|min:0|max:100',
            'is_random_questions' => 'boolean',
            'show_score' => 'boolean',
            'show_correct_answers' => 'boolean',
            'max_attempts' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        Quiz::create([
            'material_id' => $request->material_id,
            'title' => $request->title,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'duration_minutes' => $request->duration_minutes,
            'passing_score' => $request->passing_score,
            'is_random_questions' => $request->is_random_questions ?? false,
            'show_score' => $request->show_score ?? true,
            'show_correct_answers' => $request->show_correct_answers ?? false,
            'max_attempts' => $request->max_attempts,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()
            ->route('admin.quizzes.index')
            ->with('success', 'Quiz berhasil dibuat.');
    }

    public function edit(Quiz $quiz)
    {
        $materials = Material::where('is_active', true)->get();
        return view('admin.quizzes.edit', compact('quiz', 'materials'));
    }

    public function update(Request $request, Quiz $quiz)
    {
        $validator = Validator::make($request->all(), [
            'material_id' => 'required|exists:materials,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'duration_minutes' => 'required|integer|min:1|max:480',
            'passing_score' => 'required|integer|min:0|max:100',
            'is_random_questions' => 'boolean',
            'show_score' => 'boolean',
            'show_correct_answers' => 'boolean',
            'max_attempts' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $quiz->update([
            'material_id' => $request->material_id,
            'title' => $request->title,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'duration_minutes' => $request->duration_minutes,
            'passing_score' => $request->passing_score,
            'is_random_questions' => $request->is_random_questions ?? false,
            'show_score' => $request->show_score ?? true,
            'show_correct_answers' => $request->show_correct_answers ?? false,
            'max_attempts' => $request->max_attempts,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()
            ->route('admin.quizzes.index')
            ->with('success', 'Quiz berhasil diperbarui.');
    }

    public function destroy(Quiz $quiz)
    {
        // Delete related questions and options
        foreach ($quiz->questions as $question) {
            $question->options()->delete();
            $question->delete();
        }

        $quiz->delete();

        return redirect()
            ->route('admin.quizzes.index')
            ->with('success', 'Quiz berhasil dihapus.');
    }
}
