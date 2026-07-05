<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAnswer;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $quizzes = Quiz::where('is_active', true)
            ->with(['material', 'questions'])
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->paginate(10);

        // Get user's attempts
        foreach ($quizzes as $quiz) {
            $quiz->attempt = QuizAttempt::where('user_id', $user->id)
                ->where('quiz_id', $quiz->id)
                ->first();
        }

        return view('employee.quiz.index', compact('quizzes'));
    }

    public function show(Quiz $quiz)
    {
        $user = Auth::user();

        // Check if quiz is available
        if ($quiz->start_date > now()) {
            return back()->with('error', 'Quiz belum dimulai.');
        }

        if ($quiz->end_date < now()) {
            return back()->with('error', 'Quiz sudah berakhir.');
        }

        // Check existing attempt
        $attempt = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->where('status', 'in_progress')
            ->first();

        if ($attempt) {
            // Check if time expired
            $timeSpent = now()->diffInMinutes($attempt->started_at);
            if ($timeSpent >= $quiz->duration_minutes) {
                $this->completeAttempt($attempt);
                return redirect()
                    ->route('employee.quiz.result', $attempt)
                    ->with('info', 'Waktu pengerjaan telah habis.');
            }

            return redirect()->route('employee.quiz.continue', $attempt);
        }

        // Check if user has completed max attempts
        $completedAttempts = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->where('status', 'completed')
            ->count();

        if ($completedAttempts >= $quiz->max_attempts) {
            return back()->with('error', 'Anda sudah mencapai batas maksimal pengerjaan.');
        }

        // Get questions
        $questions = $this->getQuestions($quiz);

        return view('employee.quiz.start', compact('quiz', 'questions'));
    }

    public function start(Request $request, Quiz $quiz)
    {
        $user = Auth::user();

        // Check if quiz is available
        if ($quiz->start_date > now() || $quiz->end_date < now()) {
            return response()->json(['error' => 'Quiz tidak tersedia.'], 400);
        }

        // Check existing attempt
        $existingAttempt = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->where('status', 'in_progress')
            ->first();

        if ($existingAttempt) {
            return response()->json([
                'attempt_id' => $existingAttempt->id,
                'message' => 'Lanjutkan pengerjaan sebelumnya'
            ]);
        }

        // Check max attempts
        $completedAttempts = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->where('status', 'completed')
            ->count();

        if ($completedAttempts >= $quiz->max_attempts) {
            return response()->json(['error' => 'Batas maksimal pengerjaan tercapai.'], 400);
        }

        // Create new attempt
        $attempt = QuizAttempt::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        return response()->json([
            'attempt_id' => $attempt->id,
            'message' => 'Quiz dimulai'
        ]);
    }

    public function continue(QuizAttempt $attempt)
    {
        // Check ownership
        if ($attempt->user_id !== Auth::id()) {
            abort(403);
        }

        // Check if attempt is still valid
        if ($attempt->status === 'completed') {
            return redirect()
                ->route('employee.quiz.result', $attempt)
                ->with('info', 'Quiz sudah selesai dikerjakan.');
        }

        // Check time
        $timeSpent = now()->diffInMinutes($attempt->started_at);
        if ($timeSpent >= $attempt->quiz->duration_minutes) {
            $this->completeAttempt($attempt);
            return redirect()
                ->route('employee.quiz.result', $attempt)
                ->with('info', 'Waktu pengerjaan telah habis.');
        }

        $questions = $this->getQuestions($attempt->quiz);
        $answers = QuizAnswer::where('user_id', Auth::id())
            ->where('quiz_attempt_id', $attempt->id)
            ->get()
            ->keyBy('question_id');

        return view('employee.quiz.attempt', compact('attempt', 'questions', 'answers'));
    }

    public function submit(Request $request, QuizAttempt $attempt)
    {
        // Check ownership
        if ($attempt->user_id !== Auth::id()) {
            abort(403);
        }

        if ($attempt->status === 'completed') {
            return response()->json(['error' => 'Quiz sudah selesai.'], 400);
        }

        $request->validate([
            'answers' => 'required|array',
            'answers.*' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            // Save answers
            foreach ($request->answers as $questionId => $answer) {
                $question = Question::find($questionId);
                if (!$question) continue;

                $isCorrect = false;
                $selectedOptionId = null;
                $answerText = null;

                if ($question->type === 'multiple_choice' || $question->type === 'true_false') {
                    $selectedOptionId = $answer;
                    $option = $question->options()->where('id', $answer)->first();
                    $isCorrect = $option && $option->is_correct;
                } else {
                    $answerText = $answer;
                    // For essay and short answer, check if answer matches
                    $correctOption = $question->options()->where('is_correct', true)->first();
                    $isCorrect = $correctOption && strtolower(trim($answer)) === strtolower(trim($correctOption->option_text));
                }

                QuizAnswer::updateOrCreate(
                    [
                        'user_id' => Auth::id(),
                        'quiz_attempt_id' => $attempt->id,
                        'question_id' => $questionId,
                    ],
                    [
                        'selected_option_id' => $selectedOptionId,
                        'answer_text' => $answerText,
                        'is_correct' => $isCorrect,
                    ]
                );
            }

            // Complete the attempt
            $this->completeAttempt($attempt);

            DB::commit();

            return response()->json([
                'success' => true,
                'redirect' => route('employee.quiz.result', $attempt)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menyimpan jawaban.'], 500);
        }
    }

    public function result(QuizAttempt $attempt)
    {
        // Check ownership
        if ($attempt->user_id !== Auth::id()) {
            abort(403);
        }

        $attempt->load(['quiz', 'answers.question.options']);

        // Calculate statistics
        $totalQuestions = $attempt->answers->count();
        $correctAnswers = $attempt->answers->where('is_correct', true)->count();

        return view('employee.quiz.result', compact('attempt', 'totalQuestions', 'correctAnswers'));
    }

    private function getQuestions(Quiz $quiz)
    {
        $questions = $quiz->questions()
            ->where('is_active', true)
            ->with(['options'])
            ->get();

        if ($quiz->is_random_questions) {
            $questions = $questions->shuffle();
        }

        return $questions;
    }

    private function completeAttempt(QuizAttempt $attempt)
    {
        // Calculate score
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

        $score = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100) : 0;

        $attempt->update([
            'completed_at' => now(),
            'score' => $score,
            'is_passed' => $score >= ($attempt->quiz->passing_score ?? 70),
            'status' => 'completed',
        ]);

        // Update enrollment progress
        $enrollment = \App\Models\Enrollment::where('user_id', $attempt->user_id)
            ->where('material_id', $attempt->quiz->material_id)
            ->first();

        if ($enrollment && $score >= ($attempt->quiz->passing_score ?? 70)) {
            $enrollment->update([
                'status' => 'completed',
                'completed_at' => now(),
                'progress' => 100,
            ]);
        }

        return true;
    }
}
