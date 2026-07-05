<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Material;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_employees' => User::where('role', 'employee')->count(),
            'total_materials' => Material::count(),
            'total_quizzes' => Quiz::count(),
            'total_attempts' => QuizAttempt::count(),
            'recent_activities' => $this->getRecentActivities(),
            'quiz_results' => $this->getQuizResults(),
            'material_stats' => $this->getMaterialStats(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    private function getRecentActivities()
    {
        return QuizAttempt::with(['user', 'quiz'])
            ->latest()
            ->take(10)
            ->get();
    }

    private function getQuizResults()
    {
        return DB::table('quiz_attempts')
            ->selectRaw('DATE(completed_at) as date, COUNT(*) as total, AVG(score) as avg_score')
            ->whereNotNull('completed_at')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(7)
            ->get();
    }

    private function getMaterialStats()
    {
        return Material::withCount(['tasks'])
            ->latest()
            ->take(5)
            ->get();
    }
}
