@extends('layouts.app')

@section('title', $quiz->title)

@section('extra_css')
<style>
    .question-container {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .answer-option {
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
        cursor: pointer;
        transition: all 0.3s;
    }

    .answer-option:hover {
        border-color: #6366f1;
        background: #f3f4f6;
    }

    .answer-option.selected {
        border-color: #6366f1;
        background: #eef2ff;
    }

    .answer-option.correct {
        border-color: #10b981;
        background: #ecfdf5;
    }

    .answer-option.incorrect {
        border-color: #ef4444;
        background: #fef2f2;
    }

    .media-preview {
        max-width: 200px;
        max-height: 200px;
        margin: 1rem 0;
        border-radius: 8px;
    }

    .progress-bar-custom {
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: white;
    }

    .timer {
        font-size: 1.5rem;
        font-weight: bold;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        background: #fef2f2;
        color: #ef4444;
    }

    .timer.warning {
        animation: pulse 1s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ $quiz->title }}</h5>
                    
                    @if($quiz->time_limit)
                        <div class="mb-3 text-center">
                            <div id="timer" class="timer"></div>
                        </div>
                    @endif

                    <div class="mb-3">
                        <small class="text-muted">Pertanyaan {{ $currentQuestionIndex + 1 }} dari {{ $totalQuestions }}</small>
                        <div class="progress">
                            <div class="progress-bar progress-bar-custom bg-primary" 
                                 style="width: {{ (($currentQuestionIndex + 1) / $totalQuestions) * 100 }}%">
                                {{ $currentQuestionIndex + 1 }}/{{ $totalQuestions }}
                            </div>
                        </div>
                    </div>

                    <div class="list-group list-group-sm">
                        @foreach($questions as $index => $q)
                            <a href="{{ route('student.quiz.show', ['quizSession' => $session, 'question' => $index]) }}" 
                               class="list-group-item list-group-item-action {{ $index === $currentQuestionIndex ? 'active' : '' }}">
                                <div class="d-flex justify-content-between">
                                    <span>Q{{ $index + 1 }}</span>
                                    @php
                                        $response = $session->responses->firstWhere('question_id', $q->id);
                                    @endphp
                                    @if($response)
                                        <span class="badge {{ $response->is_correct ? 'bg-success' : 'bg-danger' }}">
                                            {{ $response->is_correct ? '✓' : '✗' }}
                                        </span>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9">
            <div class="question-container">
                <!-- Question -->
                <div class="mb-4">
                    <h4>Pertanyaan {{ $currentQuestionIndex + 1 }}</h4>
                    <h5 class="mb-3">{{ $currentQuestion->text }}</h5>

                    @if($currentQuestion->image_file)
                        <img src="{{ asset('storage/' . $currentQuestion->image_file) }}" 
                             class="media-preview" alt="Pertanyaan">
                    @endif

                    @if($currentQuestion->audio_file)
                        <div class="mb-3">
                            <audio controls class="w-100" style="max-width: 300px;">
                                <source src="{{ asset('storage/' . $currentQuestion->audio_file) }}">
                                Browser Anda tidak support audio
                            </audio>
                        </div>
                    @endif
                </div>

                <!-- Answers -->
                <div class="mb-4">
                    <h6>Pilih jawaban yang benar:</h6>
                    @php
                        $userResponse = $session->responses->firstWhere('question_id', $currentQuestion->id);
                    @endphp

                    @foreach($currentQuestion->answers as $answer)
                        <div class="answer-option" 
                             data-question-id="{{ $currentQuestion->id }}" 
                             data-answer-id="{{ $answer->id }}"
                             onclick="selectAnswer(this)">
                            <div class="d-flex align-items-start">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" 
                                           name="answer" 
                                           value="{{ $answer->id }}"
                                           {{ $userResponse && $userResponse->answer_id == $answer->id ? 'checked' : '' }}>
                                </div>
                                <div class="flex-grow-1">
                                    <div>{{ $answer->text }}</div>
                                    @if($answer->image_file)
                                        <img src="{{ asset('storage/' . $answer->image_file) }}" 
                                             class="media-preview" alt="Jawaban">
                                    @endif
                                    @if($answer->audio_file)
                                        <audio controls style="max-width: 200px; margin-top: 0.5rem;">
                                            <source src="{{ asset('storage/' . $answer->audio_file) }}">
                                        </audio>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Navigation -->
                <div class="d-flex justify-content-between">
                    @if($currentQuestionIndex > 0)
                        <a href="{{ route('student.quiz.show', ['quizSession' => $session, 'question' => $currentQuestionIndex - 1]) }}" 
                           class="btn btn-outline-primary">
                            ← Sebelumnya
                        </a>
                    @else
                        <div></div>
                    @endif

                    @if($currentQuestionIndex < $totalQuestions - 1)
                        <a href="{{ route('student.quiz.show', ['quizSession' => $session, 'question' => $currentQuestionIndex + 1]) }}" 
                           class="btn btn-outline-primary">
                            Selanjutnya →
                        </a>
                    @else
                        <form method="POST" action="{{ route('student.quiz.complete', $session) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success btn-lg">
                                ✓ Selesai
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if($session->quiz->time_limit)
    <script>
        let timeLimit = {{ $session->quiz->time_limit }};
        let startTime = new Date({{ json_encode($session->started_at) }}).getTime();
        let elapsedSeconds = Math.floor((new Date().getTime() - startTime) / 1000);
        let remainingSeconds = timeLimit - elapsedSeconds;

        function updateTimer() {
            remainingSeconds--;
            let minutes = Math.floor(remainingSeconds / 60);
            let seconds = remainingSeconds % 60;
            
            document.getElementById('timer').textContent = 
                String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
            
            if (remainingSeconds <= 60) {
                document.getElementById('timer').classList.add('warning');
            }
            
            if (remainingSeconds <= 0) {
                document.getElementById('timer').textContent = 'Waktu habis!';
                document.querySelectorAll('.answer-option').forEach(el => el.style.pointerEvents = 'none');
                setTimeout(() => document.querySelector('form').submit(), 2000);
            }
        }

        updateTimer();
        setInterval(updateTimer, 1000);
    </script>
@endif

<script>
    function selectAnswer(element) {
        const questionId = element.dataset.questionId;
        const answerId = element.dataset.answerId;
        const options = document.querySelectorAll(`[data-question-id="${questionId}"]`);
        
        options.forEach(opt => opt.classList.remove('selected', 'correct', 'incorrect'));
        element.classList.add('selected');

        // Save answer via AJAX
        fetch("{{ route('student.quiz.answer', $session) }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                question_id: questionId,
                answer_id: answerId
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.is_correct) {
                element.classList.remove('selected');
                element.classList.add('correct');
            } else {
                element.classList.remove('selected');
                element.classList.add('incorrect');
            }
        })
        .catch(err => console.error('Error:', err));
    }
</script>
@endsection
