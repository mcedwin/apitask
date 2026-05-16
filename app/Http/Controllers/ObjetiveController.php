<?php

namespace App\Http\Controllers;

use App\Models\Objetive;
use App\Models\Task;
use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ObjetiveController extends Controller
{
  public function index()
  {
    $user = Auth::user();
    $objetivos = Objetive::where('user_id', $user->id)
        ->with('tasks') // <--- IMPORTANTE: 'tareas' es el nombre del método en tu modelo Objetive
        ->get();

    return response()->json($objetivos);
  }
  public function store(Request $request, AIService $ai)
  {
    $goal = Objetive::create([
      'user_id' => $request->user()->id,
      'title' => $request->title,
      'description' => $request->description,
      'start_date' => now(),
      'end_date' => $request->end_date,
      'status' => 'active'
    ]);

    // IA genera tareas
    $aiResult = $ai->generateTasks($goal->title, $goal->end_date);

    // Ejemplo simple: dividir por líneas
    $lines = explode("\n", $aiResult);

    foreach ($lines as $line) {
      if (trim($line) === '') continue;

      Task::create([
        'objective_id' => $goal->id,
        'title' => trim($line),
        'status' => 'pending'
      ]);
    }

    return response()->json([
      'goal' => $goal,
      'tasks' => $goal->tasks
    ]);
  }
}
