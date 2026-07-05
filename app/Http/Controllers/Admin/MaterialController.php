<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MaterialController extends Controller
{
    public function index()
    {
        $materials = Material::with(['creator', 'quiz'])
            ->latest()
            ->paginate(10);

        return view('admin.materials.index', compact('materials'));
    }

    public function create()
    {
        return view('admin.materials.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'file' => 'required|file|max:2048',
            'order_number' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('materials', $fileName, 'public');

        $material = Material::create([
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => $filePath,
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'created_by' => auth()->id(),
            'order_number' => $request->order_number ?? 0,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()
            ->route('admin.materials.index')
            ->with('success', 'Materi berhasil ditambahkan.');
    }

    public function edit(Material $material)
    {
        return view('admin.materials.edit', compact('material'));
    }

    public function update(Request $request, Material $material)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'file' => 'nullable|file|max:2048',
            'order_number' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = [
            'title' => $request->title,
            'description' => $request->description,
            'order_number' => $request->order_number ?? 0,
            'is_active' => $request->has('is_active'),
        ];

        if ($request->hasFile('file')) {
            // Delete old file
            if ($material->file_path) {
                Storage::disk('public')->delete($material->file_path);
            }

            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('materials', $fileName, 'public');

            $data['file_path'] = $filePath;
            $data['file_type'] = $file->getClientMimeType();
            $data['file_size'] = $file->getSize();
        }

        $material->update($data);

        return redirect()
            ->route('admin.materials.index')
            ->with('success', 'Materi berhasil diperbarui.');
    }

    public function destroy(Material $material)
    {
        // Delete associated file
        if ($material->file_path) {
            Storage::disk('public')->delete($material->file_path);
        }

        // Delete associated quiz and its questions
        if ($material->quiz) {
            foreach ($material->quiz->questions as $question) {
                $question->options()->delete();
                $question->delete();
            }
            $material->quiz->delete();
        }

        $material->delete();

        return redirect()
            ->route('admin.materials.index')
            ->with('success', 'Materi berhasil dihapus.');
    }

    public function show(Material $material)
    {
        $material->load(['creator', 'quiz.questions.options', 'tasks.user']);
        return view('admin.materials.show', compact('material'));
    }

    public function duplicate(Material $material)
    {
        // Duplicate material
        $newMaterial = $material->replicate();
        $newMaterial->title = $material->title . ' (Copy)';
        $newMaterial->created_by = auth()->id();
        $newMaterial->save();

        // Duplicate quiz if exists
        if ($material->quiz) {
            $newQuiz = $material->quiz->replicate();
            $newQuiz->material_id = $newMaterial->id;
            $newQuiz->save();

            foreach ($material->quiz->questions as $question) {
                $newQuestion = $question->replicate();
                $newQuestion->quiz_id = $newQuiz->id;
                $newQuestion->save();

                foreach ($question->options as $option) {
                    $newOption = $option->replicate();
                    $newOption->question_id = $newQuestion->id;
                    $newOption->save();
                }
            }
        }

        return redirect()
            ->route('admin.materials.index')
            ->with('success', 'Materi berhasil diduplikasi.');
    }
}
