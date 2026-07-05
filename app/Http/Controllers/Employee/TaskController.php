<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $tasks = Task::where('user_id', $user->id)
            ->with('material')
            ->latest()
            ->paginate(10);

        return view('employee.tasks.index', compact('tasks'));
    }

    public function create(Material $material)
    {
        // Check if user already submitted task for this material
        $existingTask = Task::where('user_id', Auth::id())
            ->where('material_id', $material->id)
            ->first();

        return view('employee.tasks.create', compact('material', 'existingTask'));
    }

    public function store(Request $request, Material $material)
    {
        $validator = Validator::make($request->all(), [
            'video' => 'required|file|mimes:mp4,avi,mov,wmv,flv|max:2048', // 2MB max
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Check if user already submitted
        $existingTask = Task::where('user_id', Auth::id())
            ->where('material_id', $material->id)
            ->first();

        if ($existingTask && $existingTask->status !== 'rejected') {
            return back()->with('error', 'Anda sudah mengirimkan tugas untuk materi ini.');
        }

        $file = $request->file('video');
        $fileName = time() . '_' . Auth::id() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('tasks', $fileName, 'public');

        Task::create([
            'user_id' => Auth::id(),
            'material_id' => $material->id,
            'video_path' => $filePath,
            'video_size' => $file->getSize(),
            'status' => 'submitted',
            'submitted_at' => now(),
            'comment' => $request->comment,
        ]);

        return redirect()
            ->route('employee.tasks.index')
            ->with('success', 'Tugas berhasil dikirim.');
    }

    public function edit(Task $task)
    {
        // Check if task belongs to user
        if ($task->user_id !== Auth::id()) {
            abort(403);
        }

        return view('employee.tasks.edit', compact('task'));
    }

    public function update(Request $request, Task $task)
    {
        // Check if task belongs to user
        if ($task->user_id !== Auth::id()) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'video' => 'nullable|file|mimes:mp4,avi,mov,wmv,flv|max:2048',
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = [
            'comment' => $request->comment,
        ];

        if ($request->hasFile('video')) {
            // Delete old video
            if ($task->video_path) {
                Storage::disk('public')->delete($task->video_path);
            }

            $file = $request->file('video');
            $fileName = time() . '_' . Auth::id() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('tasks', $fileName, 'public');

            $data['video_path'] = $filePath;
            $data['video_size'] = $file->getSize();
            $data['submitted_at'] = now();
            $data['status'] = 'submitted';
        }

        $task->update($data);

        return redirect()
            ->route('employee.tasks.index')
            ->with('success', 'Tugas berhasil diperbarui.');
    }
}
