<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(['data' => $request->user()->tasks()->latest()->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:pending,done'],
            'due_date' => ['nullable', 'date'],
        ]);

        $task = $request->user()->tasks()->create($data);
        return response()->json(['message' => 'Tugas dibuat', 'data' => $task], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $task = $request->user()->tasks()->findOrFail($id);
        return response()->json(['data' => $task]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $task = $request->user()->tasks()->findOrFail($id);
        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', 'in:pending,done'],
            'due_date' => ['sometimes', 'nullable', 'date'],
        ]);
        $task->update($data);
        return response()->json(['message' => 'Tugas diperbarui', 'data' => $task->refresh()]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $task = $request->user()->tasks()->findOrFail($id);
        $task->delete();
        return response()->json(['message' => 'Tugas dihapus']);
    }
}
