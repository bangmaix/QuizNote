@extends('layouts.app')

@section('title', 'Hasil Quiz - ' . $quiz->title)

@section('content')
<div class="container">
    <div class="card mb-4">
        <div class="card-body text-center p-5">
            <h3 class="card-title">Selamat! 🎉</h3>
            <p class="text-muted">Kamu telah menyelesaikan quiz: <strong>{{ $quiz->title }}</strong></p>
            
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="mb-3">
                        <small class="text-muted">SKOR</small>
                        <h3 class="text-success">{{ $earnedScore }}/{{ $totalScore }}</h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <small class="text-muted">JAWABAN BENAR</small>
                        <h3 class="text-primary">{{ $correctCount }}/{{ $totalQuestions }}</h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <small class="text-muted">PERSENTASE</small>
                        <h3>{{ $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100, 1) : 0 }}%</h3>
                    </div>
                </div>
            </div>

            @if($wrongAnswers->count() > 0)
                <div class="alert alert-warning mt-3">
                    <strong>Ada {{ $wrongAnswers->count() }} jawaban yang salah</strong>
                    <p class="mb-0">Kamu bisa mencoba lagi untuk kesempatan kedua</p>
                </div>
            @else
                <div class="alert alert-success mt-3">
                    <strong>Sempurna! 🌟</strong>
                    <p class="mb-0">Semua jawaban benar!</p>
                </div>
            @endif
        </div>
    </div>

    @if($wrongAnswers->count() > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Kesempatan Kedua - Jawaban yang Salah</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    @foreach($wrongAnswers as $response)
                        <div class="list-group-item">
                            <h6>{{ $response->question->text }}</h6>
                            
                            @if($response->question->image_file)
                                <img src="{{ asset('storage/' . $response->question->image_file) }}" 
                                     class="img-thumbnail" style="max-width: 150px; margin: 0.5rem 0;">
                            @endif
                            
                            <p class="mb-2">
                                <strong>Jawaban Anda:</strong>
                                <span class="badge bg-danger">{{ $response->answer->text }}</span>
                            </p>

                            <p class="mb-0">
                                <strong>Jawaban Benar:</strong>
                                <span class="badge bg-success">
                                    {{ $response->question->answers->where('is_correct', true)->first()->text }}
                                </span>
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Detail Jawaban</h5>
        </div>
        <div class="card-body">
            <div class="list-group">
                @foreach($responses as $response)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6>{{ $response->question->text }}</h6>
                            <span class="badge {{ $response->is_correct ? 'bg-success' : 'bg-danger' }}">
                                {{ $response->is_correct ? '✓ Benar' : '✗ Salah' }}
                                @if($response->is_second_attempt)
                                    (Kesempatan 2)
                                @endif
                            </span>
                        </div>
                        
                        <p class="mb-1">
                            <strong>Poin:</strong> 
                            {{ $response->is_correct ? $response->question->score : 0 }}/{{ $response->question->score }}
                        </p>
                        <p class="mb-0">
                            <strong>Jawaban Anda:</strong> {{ $response->answer->text }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="mt-4 text-center">
        <a href="{{ route('student.dashboard') }}" class="btn btn-primary btn-lg">
            ← Kembali ke Dashboard
        </a>
    </div>
</div>
@endsection
