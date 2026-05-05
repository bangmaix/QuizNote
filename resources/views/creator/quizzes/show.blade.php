@extends('layouts.app')

@section('title', $quiz->title)

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <a href="{{ route('creator.dashboard') }}" class="btn btn-link">&larr; Kembali</a>
            <h2>{{ $quiz->title }}</h2>
            <p class="text-muted">{{ $quiz->description }}</p>
            <div>
                <span class="badge {{ $quiz->is_active ? 'bg-success' : 'bg-secondary' }}">
                    {{ $quiz->is_active ? 'Aktif' : 'Tidak Aktif' }}
                </span>
                <span class="badge badge-access-code ms-2">
                    Kode: {{ $quiz->access_code }}
                </span>
            </div>
        </div>
        <div class="col-auto">
            <a href="{{ route('quizzes.create') }}" class="btn btn-primary">Buat Quiz Baru</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Pertanyaan ({{ $quiz->questions->count() }})</h5>
                    @if(!$quiz->is_active)
                        <a href="{{ route('quizzes.questions.create', $quiz) }}" class="btn btn-sm btn-success">
                            ➕ Tambah Pertanyaan
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    @if($quiz->questions->isEmpty())
                        <div class="text-center py-5">
                            <p class="text-muted mb-3">Belum ada pertanyaan</p>
                            @if(!$quiz->is_active)
                                <a href="{{ route('quizzes.questions.create', $quiz) }}" class="btn btn-outline-primary">
                                    Tambah Pertanyaan Pertama
                                </a>
                            @endif
                        </div>
                    @else
                        <div class="list-group">
                            @foreach($quiz->questions as $index => $question)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1">
                                                {{ $index + 1 }}. {{ $question->text }}
                                            </h6>
                                            @if($question->image_file)
                                                <small class="d-block text-muted">📸 Punya gambar</small>
                                            @endif
                                            @if($question->audio_file)
                                                <small class="d-block text-muted">🔊 Punya audio</small>
                                            @endif
                                        </div>
                                        <span class="badge bg-info">{{ $question->score }} poin</span>
                                    </div>

                                    <div class="mb-2">
                                        <small class="text-muted">Pilihan Jawaban:</small>
                                        <ul class="mb-2">
                                            @foreach($question->answers as $answer)
                                                <li>
                                                    <span class="text-muted">{{ $answer->text }}</span>
                                                    @if($answer->is_correct)
                                                        <span class="badge bg-success">Benar ✓</span>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>

                                    @if(!$quiz->is_active)
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('quizzes.questions.edit', [$quiz, $question]) }}" 
                                               class="btn btn-outline-primary">Edit</a>
                                            <form method="POST" action="{{ route('quizzes.questions.destroy', [$quiz, $question]) }}" 
                                                  class="d-inline" onsubmit="return confirm('Yakin?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger">Hapus</button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Statistik</h5>
                    <div class="row text-center">
                        <div class="col-6">
                            <strong class="d-block">{{ $quiz->questions->count() }}</strong>
                            <small class="text-muted">Pertanyaan</small>
                        </div>
                        <div class="col-6">
                            <strong class="d-block">{{ $quiz->studentSessions->count() }}</strong>
                            <small class="text-muted">Peserta</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Aksi</h5>
                    <div class="d-grid gap-2">
                        @if(!$quiz->is_active)
                            @if($quiz->questions->count() > 0)
                                <form method="POST" action="{{ route('quizzes.start', $quiz) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-success">▶️ Mulai Quiz</button>
                                </form>
                            @else
                                <button class="btn btn-success" disabled>
                                    ▶️ Tambah pertanyaan dulu
                                </button>
                            @endif
                        @else
                            <form method="POST" action="{{ route('quizzes.stop', $quiz) }}">
                                @csrf
                                <button type="submit" class="btn btn-warning">⏹️ Hentikan Quiz</button>
                            </form>
                        @endif
                        
                        <a href="{{ route('quizzes.edit', $quiz) }}" class="btn btn-outline-primary">
                            ✏️ Edit Quiz
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
