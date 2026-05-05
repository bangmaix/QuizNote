<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class QuizController extends Controller
{
    /**
     * Show creator dashboard with their quizzes
     */
    public function dashboard()
    {
        $quizzes = Auth::user()->quizzesCreated()
            ->with(['questions', 'studentSessions' => function ($q) {
                $q->whereNotNull('completed_at')->with('student');
            }])
            ->get();
        return view('creator.dashboard', compact('quizzes'));
    }

    /**
     * Display a listing of creator's quizzes
     */
    public function index()
    {
        $quizzes = Auth::user()->quizzesCreated()->get();
        return view('creator.quizzes.index', compact('quizzes'));
    }

    /**
     * Show the form for creating a new quiz
     */
    public function create()
    {
        return view('creator.quizzes.create');
    }

    /**
     * Store a newly created quiz
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_limit' => 'nullable|integer|min:30', // minimum 30 seconds
        ]);

        // Generate unique 6-digit access code
        do {
            $accessCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (Quiz::where('access_code', $accessCode)->exists());

        $quiz = Auth::user()->quizzesCreated()->create([
            'title' => $request->title,
            'description' => $request->description,
            'access_code' => $accessCode,
            'time_limit' => $request->time_limit,
        ]);

        return redirect()->route('quizzes.show', $quiz)->with('success', 'Quiz dibuat. Access code: ' . $accessCode);
    }

    /**
     * Display the specified quiz with its questions
     */
    public function show(Quiz $quiz)
    {
        $this->authorize('update', $quiz);
        $quiz->load(['questions.answers', 'studentSessions']);
        return view('creator.quizzes.show', compact('quiz'));
    }

    /**
     * Show the form for editing the quiz
     */
    public function edit(Quiz $quiz)
    {
        $this->authorize('update', $quiz);
        return view('creator.quizzes.edit', compact('quiz'));
    }

    /**
     * Update the quiz
     */
    public function update(Request $request, Quiz $quiz)
    {
        $this->authorize('update', $quiz);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_limit' => 'nullable|integer|min:30',
        ]);

        $quiz->update([
            'title' => $request->title,
            'description' => $request->description,
            'time_limit' => $request->time_limit,
        ]);

        return redirect()->route('quizzes.show', $quiz)->with('success', 'Quiz diperbarui');
    }

    /**
     * Delete the quiz
     */
    public function destroy(Quiz $quiz)
    {
        $this->authorize('delete', $quiz);
        $title = $quiz->title;
        $quiz->delete();

        return redirect()->route('creator.dashboard')->with('success', "Quiz '{$title}' dihapus");
    }

    /**
     * Start the quiz (make it active for students)
     */
    public function start(Quiz $quiz)
    {
        $this->authorize('update', $quiz);

        if ($quiz->questions()->count() === 0) {
            return back()->with('error', 'Quiz harus memiliki minimal 1 pertanyaan');
        }

        $quiz->update([
            'is_active' => true,
            'started_at' => now(),
        ]);

        return back()->with('success', 'Quiz dimulai! Access code: ' . $quiz->access_code);
    }

    /**
     * Stop the quiz (make it inactive)
     */
    public function stop(Quiz $quiz)
    {
        $this->authorize('update', $quiz);

        $quiz->update([
            'is_active' => false,
            'ended_at' => now(),
        ]);

        return back()->with('success', 'Quiz dihentikan');
    }
}
