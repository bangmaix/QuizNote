@extends('layouts.app')

@section('title', 'Student Dashboard')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h2 class="text-white">👧 Ruang Belajar</h2>
            <p class="text-white-50">Ikuti quiz dan pelajari hal-hal baru</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-5 mb-4">
            <div class="card">
                <div class="card-body p-5 text-center">
                    <h5 class="card-title">Masuk Quiz</h5>
                    <p class="text-muted">Punya kode akses?</p>
                    
                    <form method="POST" action="{{ route('student.join-quiz') }}">
                        @csrf
                        <div class="mb-3">
                            <input type="text" class="form-control form-control-lg @error('access_code') is-invalid @enderror" 
                                   name="access_code" placeholder="Masukkan 6 angka" maxlength="6" required 
                                   pattern="\d{6}" inputmode="numeric">
                            @error('access_code')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Masuk</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            @if($activeSessions->isNotEmpty())
                <div class="mb-4">
                    <h5 class="text-white">Quiz Sedang Dikerjakan</h5>
                    <div class="list-group">
                        @foreach($activeSessions as $session)
                            <a href="{{ route('student.quiz.show', $session) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">{{ $session->quiz->title }}</h6>
                                        <small class="text-muted">
                                            Dimulai: {{ $session->started_at->format('d M Y H:i') }}
                                        </small>
                                    </div>
                                    <span class="badge bg-warning">Lanjutkan →</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($completedSessions->isNotEmpty())
                <div>
                    <h5 class="text-white">Quiz Selesai</h5>
                    <div class="list-group">
                        @foreach($completedSessions as $session)
                            <a href="{{ route('student.quiz.results', $session) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">{{ $session->quiz->title }}</h6>
                                        <small class="text-muted">
                                            {{ $session->completed_at->format('d M Y H:i') }}
                                        </small>
                                    </div>
                                    <span class="badge bg-success">
                                        {{ $session->correct_answers }}/{{ $session->quiz->questions->count() }}
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
