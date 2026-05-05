<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\StudentSession;
use App\Models\StudentResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StudentSessionController extends Controller
{
    /**
     * Show student dashboard with available quizzes
     */
    public function dashboard()
    {
        $activeSessions = Auth::user()->studentSessions()
            ->whereNull('completed_at')
            ->with('quiz')
            ->get();

        $completedSessions = Auth::user()->studentSessions()
            ->whereNotNull('completed_at')
            ->with('quiz')
            ->get();

        return view('student.dashboard', compact('activeSessions', 'completedSessions'));
    }

    /**
     * Join quiz with access code
     */
    public function joinQuiz(Request $request)
    {
        $request->validate([
            'access_code' => 'required|string|size:6',
        ]);

        $quiz = Quiz::where('access_code', $request->access_code)
            ->where('is_active', true)
            ->first();

        if (!$quiz) {
            return back()->with('error', 'Kode akses tidak valid atau quiz belum dimulai');
        }

        // Check if student already has an active session for this quiz
        $existingSession = StudentSession::where('quiz_id', $quiz->id)
            ->where('student_id', Auth::id())
            ->whereNull('completed_at')
            ->first();

        if ($existingSession) {
            return redirect()->route('student.quiz.show', $existingSession);
        }

        // Create new session
        $session = StudentSession::create([
            'quiz_id' => $quiz->id,
            'student_id' => Auth::id(),
            'started_at' => now(),
        ]);

        return redirect()->route('student.quiz.show', $session);
    }

    /**
     * Display quiz for student to answer
     */
    public function show(StudentSession $quizSession)
    {
        // Only student in this session can access
        if ($quizSession->student_id !== Auth::id()) {
            abort(403);
        }

        // If already completed, redirect to results
        if ($quizSession->completed_at) {
            return redirect()->route('student.quiz.results', $quizSession);
        }

        $quizSession->load(['quiz', 'responses.question.answers']);

        // Get current question (first unanswered or from query param)
        $currentQuestionIndex = request('question', 0);
        $questions = $quizSession->quiz->questions;

        if ($questions->isEmpty()) {
            return redirect()->route('student.dashboard')->with('error', 'Quiz tidak memiliki pertanyaan');
        }

        $currentQuestion = $questions[$currentQuestionIndex] ?? $questions->first();

        return view('student.quiz.show', [
            'session' => $quizSession,
            'quiz' => $quizSession->quiz,
            'currentQuestion' => $currentQuestion,
            'currentQuestionIndex' => $currentQuestionIndex,
            'totalQuestions' => $questions->count(),
            'questions' => $questions,
        ]);
    }

    /**
     * Save student's answer
     */
    public function answerQuestion(Request $request, StudentSession $quizSession)
    {
        if ($quizSession->student_id !== Auth::id()) {
            abort(403);
        }

        if ($quizSession->completed_at) {
            abort(403, 'Quiz sudah selesai');
        }

        $request->validate([
            'question_id' => 'required|integer',
            'answer_id' => 'required|integer',
        ]);

        $question = $quizSession->quiz->questions->find($request->question_id);
        if (!$question) {
            return response()->json(['error' => 'Pertanyaan tidak ditemukan'], 404);
        }

        $answer = $question->answers->find($request->answer_id);
        if (!$answer) {
            return response()->json(['error' => 'Jawaban tidak ditemukan'], 404);
        }

        // Check if already answered
        $existingResponse = $quizSession->responses()
            ->where('question_id', $question->id)
            ->where('is_second_attempt', false)
            ->first();

        if ($existingResponse) {
            // Update existing response
            $existingResponse->update([
                'answer_id' => $answer->id,
                'is_correct' => $answer->is_correct,
                'answered_at' => now(),
            ]);
        } else {
            // Create new response
            StudentResponse::create([
                'student_session_id' => $quizSession->id,
                'question_id' => $question->id,
                'answer_id' => $answer->id,
                'is_correct' => $answer->is_correct,
                'answered_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'is_correct' => $answer->is_correct,
        ]);
    }

    /**
     * Complete the quiz session and redirect to results
     */
    public function completeSession(StudentSession $quizSession)
    {
        if ($quizSession->student_id !== Auth::id()) {
            abort(403);
        }

        if ($quizSession->completed_at) {
            return redirect()->route('student.quiz.results', $quizSession);
        }

        // Calculate final scores
        $correctResponses = $quizSession->responses()
            ->where('is_correct', true)
            ->with('question')
            ->get();

        $earnedScore = $correctResponses->sum(fn($r) => $r->question->score);
        $correctCount = $correctResponses->count();

        $quizSession->update([
            'completed_at' => now(),
            'score_total' => $earnedScore,
            'correct_answers' => $correctCount,
        ]);

        return redirect()->route('student.quiz.results', $quizSession);
    }

    /**
     * Show quiz results
     */
    public function results(StudentSession $quizSession)
    {
        if ($quizSession->student_id !== Auth::id()) {
            abort(403);
        }

        $quizSession->load([
            'quiz',
            'responses.question',
            'responses.answer',
        ]);

        // Get wrong answers for second chance
        $wrongAnswers = $quizSession->responses()
            ->where('is_correct', false)
            ->where('is_second_attempt', false)
            ->get();

        // Get second attempt answers
        $secondAttempts = $quizSession->responses()
            ->where('is_second_attempt', true)
            ->get();

        // Calculate scores
        $totalScore = $quizSession->quiz->questions->sum('score');
        $earnedScore = $quizSession->responses()
            ->where('is_correct', true)
            ->with('question')
            ->get()
            ->sum(fn($response) => $response->question->score);

        $correctCount = $quizSession->responses()->where('is_correct', true)->count();

        return view('student.quiz.results', [
            'session' => $quizSession,
            'quiz' => $quizSession->quiz,
            'responses' => $quizSession->responses,
            'wrongAnswers' => $wrongAnswers,
            'secondAttempts' => $secondAttempts,
            'totalScore' => $totalScore,
            'earnedScore' => $earnedScore,
            'correctCount' => $correctCount,
            'totalQuestions' => $quizSession->quiz->questions->count(),
        ]);
    }
}
