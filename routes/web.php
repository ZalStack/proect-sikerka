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

// Home
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Authentication
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Admin Routes
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Employees
    Route::resource('employees', EmployeeController::class);
    Route::post('/employees/import', [EmployeeController::class, 'import'])->name('employees.import');
    Route::get('/employees/export', [EmployeeController::class, 'export'])->name('employees.export');

    // Divisions
    Route::resource('divisions', DivisionController::class);

    // Materials
    Route::resource('materials', MaterialController::class);
    Route::post('/materials/{material}/duplicate', [MaterialController::class, 'duplicate'])->name('materials.duplicate');

    // Results
    Route::get('/results', [ResultController::class, 'index'])->name('results.index');
    Route::get('/results/{attempt}', [ResultController::class, 'show'])->name('results.show');
    Route::post('/results/{attempt}/hide', [ResultController::class, 'hideScore'])->name('results.hide');
    Route::post('/results/{attempt}/show', [ResultController::class, 'showScore'])->name('results.show');
    Route::get('/results/export', [ResultController::class, 'export'])->name('results.export');
    
    // Results
    Route::get('/results', [ResultController::class, 'index'])->name('results.index');
    Route::get('/results/{attempt}', [ResultController::class, 'show'])->name('results.show');
    Route::post('/results/{attempt}/hide', [ResultController::class, 'hideScore'])->name('results.hide');
    Route::post('/results/{attempt}/show', [ResultController::class, 'showScore'])->name('results.show');
});

// Employee Routes
Route::prefix('employee')->middleware(['auth', 'employee'])->name('employee.')->group(function () {
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
    Route::get('/tasks/{material}', [TaskController::class, 'create'])->name('tasks.create');
    Route::post('/tasks/{material}', [TaskController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');

    // Quiz
    Route::get('/quiz', [EmployeeQuizController::class, 'index'])->name('quiz.index');
    Route::get('/quiz/{quiz}', [EmployeeQuizController::class, 'show'])->name('quiz.show');
    Route::post('/quiz/{quiz}/start', [EmployeeQuizController::class, 'start'])->name('quiz.start');
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

    Route::put('/profile/update', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password');
});
