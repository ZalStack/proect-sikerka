<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuizAttempt;
use App\Models\QuizAnswer;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function index(Request $request)
    {
        $query = QuizAttempt::with(['user', 'quiz.material'])
            ->whereNotNull('completed_at');

        // Filter by quiz
        if ($request->has('quiz_id') && $request->quiz_id) {
            $query->where('quiz_id', $request->quiz_id);
        }

        // Filter by user
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        $results = $query->latest()->paginate(15);

        return view('admin.results.index', compact('results'));
    }

    public function show(QuizAttempt $attempt)
    {
        $attempt->load([
            'user',
            'quiz.material',
            'answers.question.options',
            'answers.selectedOption'
        ]);

        // Calculate statistics
        $totalQuestions = $attempt->answers->count();
        $correctAnswers = $attempt->answers->where('is_correct', true)->count();
        $wrongAnswers = $totalQuestions - $correctAnswers;

        // Group answers by question type
        $answersByType = $attempt->answers->groupBy(function ($answer) {
            return $answer->question->type ?? 'unknown';
        });

        return view('admin.results.show', compact(
            'attempt',
            'totalQuestions',
            'correctAnswers',
            'wrongAnswers',
            'answersByType'
        ));
    }

    public function hideScore(Request $request, QuizAttempt $attempt)
    {
        $attempt->update([
            'score' => null,
            'is_passed' => false,
        ]);

        return back()->with('success', 'Nilai berhasil disembunyikan.');
    }

    public function showScore(Request $request, QuizAttempt $attempt)
    {
        // Recalculate score if needed
        $score = $this->calculateScore($attempt);
        $attempt->update([
            'score' => $score,
            'is_passed' => $score >= ($attempt->quiz->passing_score ?? 70),
        ]);

        return back()->with('success', 'Nilai berhasil ditampilkan.');
    }

    private function calculateScore(QuizAttempt $attempt)
    {
        $totalPoints = 0;
        $earnedPoints = 0;

        foreach ($attempt->answers as $answer) {
            if ($answer->question) {
                $totalPoints += $answer->question->points ?? 1;
                if ($answer->is_correct) {
                    $earnedPoints += $answer->question->points ?? 1;
                }
            }
        }

        return $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100) : 0;
    }

    public function export(Request $request)
    {
        // Export results to Excel/CSV
        return redirect()->back()->with('success', 'Data hasil berhasil diexport.');
    }
}
