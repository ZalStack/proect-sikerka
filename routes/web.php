<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\DivisionController;
use App\Http\Controllers\Admin\MaterialController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\ResultController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;
use App\Http\Controllers\Employee\LearningController;
use App\Http\Controllers\Employee\TaskController;
use App\Http\Controllers\Employee\QuizController as EmployeeQuizController;
use App\Http\Controllers\ProfileController;

// Home
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Authentication
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Admin Routes
Route::prefix('admin')
    ->middleware(['auth', 'admin'])
    ->name('admin.')
    ->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Employees
        Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
        Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
        Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
        Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
        Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
        Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
        Route::post('/employees/import', [EmployeeController::class, 'import'])->name('employees.import');
        Route::get('/employees/export', [EmployeeController::class, 'export'])->name('employees.export');

        // Divisions
        Route::get('/divisions', [DivisionController::class, 'index'])->name('divisions.index');
        Route::get('/divisions/create', [DivisionController::class, 'create'])->name('divisions.create');
        Route::post('/divisions', [DivisionController::class, 'store'])->name('divisions.store');
        Route::get('/divisions/{division}/edit', [DivisionController::class, 'edit'])->name('divisions.edit');
        Route::put('/divisions/{division}', [DivisionController::class, 'update'])->name('divisions.update');
        Route::delete('/divisions/{division}', [DivisionController::class, 'destroy'])->name('divisions.destroy');

        // Materials
        Route::get('/materials', [MaterialController::class, 'index'])->name('materials.index');
        Route::get('/materials/create', [MaterialController::class, 'create'])->name('materials.create');
        Route::post('/materials', [MaterialController::class, 'store'])->name('materials.store');
        Route::get('/materials/{material}', [MaterialController::class, 'show'])->name('materials.show');
        Route::get('/materials/{material}/edit', [MaterialController::class, 'edit'])->name('materials.edit');
        Route::put('/materials/{material}', [MaterialController::class, 'update'])->name('materials.update');
        Route::delete('/materials/{material}', [MaterialController::class, 'destroy'])->name('materials.destroy');
        Route::post('/materials/{material}/duplicate', [MaterialController::class, 'duplicate'])->name('materials.duplicate');

        // Quizzes
        Route::get('/quizzes', [QuizController::class, 'index'])->name('quizzes.index');
        Route::get('/quizzes/create', [QuizController::class, 'create'])->name('quizzes.create');
        Route::post('/quizzes', [QuizController::class, 'store'])->name('quizzes.store');
        Route::get('/quizzes/{quiz}/edit', [QuizController::class, 'edit'])->name('quizzes.edit');
        Route::put('/quizzes/{quiz}', [QuizController::class, 'update'])->name('quizzes.update');
        Route::delete('/quizzes/{quiz}', [QuizController::class, 'destroy'])->name('quizzes.destroy');

        // Questions
        Route::get('/quizzes/{quiz}/questions', [QuestionController::class, 'index'])->name('quizzes.questions');
        Route::post('/quizzes/{quiz}/questions', [QuestionController::class, 'store'])->name('quizzes.questions.store');
        Route::put('/questions/{question}', [QuestionController::class, 'update'])->name('questions.update');
        Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');

        // Results
        Route::get('/results', [ResultController::class, 'index'])->name('results.index');
        Route::get('/results/{attempt}/detail', [ResultController::class, 'show'])->name('results.show');
        Route::post('/results/{attempt}/hide', [ResultController::class, 'hideScore'])->name('results.hide');
        Route::post('/results/{attempt}/show-score', [ResultController::class, 'showScore'])->name('results.show-score');
        Route::get('/results/export', [ResultController::class, 'export'])->name('results.export');
    });

// Employee Routes
Route::prefix('employee')
    ->middleware(['auth', 'employee'])
    ->name('employee.')
    ->group(function () {
        // Complete Profile
        Route::get('/complete-profile', [EmployeeDashboardController::class, 'completeProfile'])->name('complete-profile');
        Route::post('/complete-profile', [EmployeeDashboardController::class, 'saveProfile']);

        // Dashboard
        Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');

        // Learning
        Route::get('/learning', [LearningController::class, 'index'])->name('learning');
        Route::get('/learning/{material}', [LearningController::class, 'show'])->name('learning.show');
        Route::post('/learning/{material}/enroll', [LearningController::class, 'enroll'])->name('learning.enroll');
        Route::post('/learning/{material}/progress', [LearningController::class, 'updateProgress'])->name('learning.progress');

        // Tasks
        Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
        Route::get('/tasks/{material}/create', [TaskController::class, 'create'])->name('tasks.create');
        Route::post('/tasks/{material}', [TaskController::class, 'store'])->name('tasks.store');
        Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
        Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');

        // Quiz
        Route::get('/quiz', [EmployeeQuizController::class, 'index'])->name('quiz.index');
        Route::get('/quiz/{quiz}', [EmployeeQuizController::class, 'show'])->name('quiz.show');
        Route::post('/quiz/{quiz}/start', [EmployeeQuizController::class, 'start'])->name('quiz.start');
        Route::get('/quiz/continue/{attempt}', [EmployeeQuizController::class, 'continue'])->name('quiz.continue');
        Route::post('/quiz/{attempt}/submit', [EmployeeQuizController::class, 'submit'])->name('quiz.submit');
        Route::get('/quiz/{attempt}/result', [EmployeeQuizController::class, 'result'])->name('quiz.result');
    });

// Profile Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', function () {
        return view('profile.index');
    })->name('profile');

    Route::get('/profile/settings', function () {
        return view('profile.settings');
    })->name('profile.settings');

    Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});
