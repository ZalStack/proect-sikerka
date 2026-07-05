<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    public function index(Quiz $quiz)
    {
        $questions = $quiz->questions()->with('options')->orderBy('order_number')->get();
        return view('admin.quizzes.questions', compact('quiz', 'questions'));
    }

    public function store(Request $request, Quiz $quiz)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:multiple_choice,essay,true_false,short_answer',
            'question_text' => 'required|string',
            'points' => 'required|integer|min:1',
            'options' => 'required_if:type,multiple_choice|array|min:2',
            'options.*' => 'required|string',
            'correct_option' => 'required_if:type,multiple_choice|integer|min:0',
            'correct_answer' => 'required_if:type,true_false,short_answer|string',
            'order_number' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $question = Question::create([
            'quiz_id' => $quiz->id,
            'type' => $request->type,
            'question_text' => $request->question_text,
            'points' => $request->points,
            'order_number' => $request->order_number ?? 0,
            'is_active' => true,
        ]);

        // Handle options for multiple choice
        if ($request->type === 'multiple_choice') {
            foreach ($request->options as $index => $optionText) {
                Option::create([
                    'question_id' => $question->id,
                    'option_text' => $optionText,
                    'is_correct' => $index == $request->correct_option,
                ]);
            }
        }

        // Handle true/false or short answer
        if (in_array($request->type, ['true_false', 'short_answer'])) {
            Option::create([
                'question_id' => $question->id,
                'option_text' => $request->correct_answer,
                'is_correct' => true,
            ]);
        }

        return redirect()
            ->route('admin.quizzes.questions', $quiz)
            ->with('success', 'Pertanyaan berhasil ditambahkan.');
    }

    public function update(Request $request, Question $question)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:multiple_choice,essay,true_false,short_answer',
            'question_text' => 'required|string',
            'points' => 'required|integer|min:1',
            'options' => 'required_if:type,multiple_choice|array|min:2',
            'options.*' => 'required|string',
            'correct_option' => 'required_if:type,multiple_choice|integer|min:0',
            'correct_answer' => 'required_if:type,true_false,short_answer|string',
            'order_number' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $question->update([
            'type' => $request->type,
            'question_text' => $request->question_text,
            'points' => $request->points,
            'order_number' => $request->order_number ?? 0,
            'is_active' => $request->is_active ?? true,
        ]);

        // Update options
        if ($request->type === 'multiple_choice') {
            $question->options()->delete();
            foreach ($request->options as $index => $optionText) {
                Option::create([
                    'question_id' => $question->id,
                    'option_text' => $optionText,
                    'is_correct' => $index == $request->correct_option,
                ]);
            }
        } elseif (in_array($request->type, ['true_false', 'short_answer'])) {
            $question->options()->delete();
            Option::create([
                'question_id' => $question->id,
                'option_text' => $request->correct_answer,
                'is_correct' => true,
            ]);
        }

        return back()->with('success', 'Pertanyaan berhasil diperbarui.');
    }

    public function destroy(Question $question)
    {
        $quizId = $question->quiz_id;
        $question->options()->delete();
        $question->delete();

        return redirect()
            ->route('admin.quizzes.questions', $quizId)
            ->with('success', 'Pertanyaan berhasil dihapus.');
    }
}
