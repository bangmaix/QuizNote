<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\StudentSessionController;

// Welcome page
Route::get('/', function () {
    return view('welcome');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
    
    Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.post');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Creator Routes
Route::middleware(['auth', 'creator'])->prefix('creator')->group(function () {
    Route::get('dashboard', [QuizController::class, 'dashboard'])->name('creator.dashboard');
    Route::resource('quizzes', QuizController::class);
    Route::post('quizzes/{quiz}/start', [QuizController::class, 'start'])->name('quizzes.start');
    Route::post('quizzes/{quiz}/stop', [QuizController::class, 'stop'])->name('quizzes.stop');
    Route::resource('quizzes.questions', QuestionController::class);
});

// Student Routes
Route::middleware(['auth', 'student'])->prefix('student')->group(function () {
    Route::get('dashboard', [StudentSessionController::class, 'dashboard'])->name('student.dashboard');
    Route::post('join-quiz', [StudentSessionController::class, 'joinQuiz'])->name('student.join-quiz');
    Route::get('quiz/{quizSession}', [StudentSessionController::class, 'show'])->name('student.quiz.show');
    Route::post('quiz/{quizSession}/answer', [StudentSessionController::class, 'answerQuestion'])->name('student.quiz.answer');
    Route::post('quiz/{quizSession}/complete', [StudentSessionController::class, 'completeSession'])->name('student.quiz.complete');
    Route::get('quiz/{quizSession}/results', [StudentSessionController::class, 'results'])->name('student.quiz.results');
});

