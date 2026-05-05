<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionController extends Controller
{
    /**
     * Show form to create new question
     */
    public function create(Quiz $quiz)
    {
        $this->authorizeQuiz($quiz);
        return view('creator.questions.create', compact('quiz'));
    }

    /**
     * Store new question
     */
    public function store(Request $request, Quiz $quiz)
    {
        $this->authorizeQuiz($quiz);

        $request->validate([
            'text' => 'required|string',
            'score' => 'required|integer|min:1',
            'audio_file' => 'nullable|file|mimes:mp3,wav,ogg,webm',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'answers' => 'required|array|min:2',
            'answers.*.text' => 'required|string',
            'answers.*.is_correct' => 'nullable',
        ]);

        // Get next order number
        $nextOrder = $quiz->questions()->max('order') + 1 ?? 1;

        $question = $quiz->questions()->create([
            'text' => $request->text,
            'score' => $request->score,
            'order' => $nextOrder,
        ]);

        // Handle file uploads
        if ($request->hasFile('audio_file')) {
            $audioPath = $request->file('audio_file')->store('questions/audio', 'public');
            $question->update(['audio_file' => $audioPath]);
        }

        if ($request->hasFile('image_file')) {
            $imagePath = $request->file('image_file')->store('questions/images', 'public');
            $question->update(['image_file' => $imagePath]);
        }

        // Create answers
        foreach ($request->answers as $index => $answerData) {
            $question->answers()->create([
                'text' => $answerData['text'],
                'is_correct' => isset($answerData['is_correct']),
                'order' => $index + 1,
            ]);
        }

        return redirect()->route('quizzes.show', $quiz)->with('success', 'Pertanyaan ditambahkan');
    }

    /**
     * Show edit form for question
     */
    public function edit(Quiz $quiz, Question $question)
    {
        $this->authorizeQuiz($quiz);
        
        if ($question->quiz_id !== $quiz->id) {
            abort(404);
        }

        return view('creator.questions.edit', compact('quiz', 'question'));
    }

    /**
     * Update question
     */
    public function update(Request $request, Quiz $quiz, Question $question)
    {
        $this->authorizeQuiz($quiz);

        if ($question->quiz_id !== $quiz->id) {
            abort(404);
        }

        $request->validate([
            'text' => 'required|string',
            'score' => 'required|integer|min:1',
            'audio_file' => 'nullable|file|mimes:mp3,wav,ogg,webm',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ]);

        $question->update([
            'text' => $request->text,
            'score' => $request->score,
        ]);

        if ($request->hasFile('audio_file')) {
            $audioPath = $request->file('audio_file')->store('questions/audio', 'public');
            $question->update(['audio_file' => $audioPath]);
        }

        if ($request->hasFile('image_file')) {
            $imagePath = $request->file('image_file')->store('questions/images', 'public');
            $question->update(['image_file' => $imagePath]);
        }

        return redirect()->route('quizzes.show', $quiz)->with('success', 'Pertanyaan diperbarui');
    }

    /**
     * Delete question
     */
    public function destroy(Quiz $quiz, Question $question)
    {
        $this->authorizeQuiz($quiz);

        if ($question->quiz_id !== $quiz->id) {
            abort(404);
        }

        $question->delete();

        return back()->with('success', 'Pertanyaan dihapus');
    }

    /**
     * Authorize quiz ownership
     */
    private function authorizeQuiz(Quiz $quiz)
    {
        if ($quiz->creator_id !== Auth::id()) {
            abort(403);
        }
    }
}
