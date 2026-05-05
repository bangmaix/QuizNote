@extends('layouts.app')

@section('title', 'Daftar Quiz')

@section('content')
<div class="container">
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h2 class="text-white">📝 Daftar Quiz Saya</h2>
            <p class="text-white-50">Kelola semua quiz yang telah Anda buat</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('creator.dashboard') }}" class="btn btn-light me-2">← Dashboard</a>
            <a href="{{ route('quizzes.create') }}" class="btn btn-light">➕ Buat Quiz Baru</a>
        </div>
    </div>

    @if($quizzes->isEmpty())
        <div class="card text-center">
            <div class="card-body p-5">
                <h5 class="card-title">Belum ada quiz</h5>
                <p class="card-text">Mulai dengan membuat quiz pertama Anda</p>
                <a href="{{ route('quizzes.create') }}" class="btn btn-primary">Buat Quiz</a>
            </div>
        </div>
    @else
        <div class="row">
            @foreach($quizzes as $quiz)
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="card-title">{{ $quiz->title }}</h5>
                                    <p class="card-text text-muted small">{{ $quiz->description }}</p>
                                </div>
                                <span class="badge {{ $quiz->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $quiz->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </div>

                            <div class="mb-3">
                                <small class="d-block text-muted">Kode Akses:</small>
                                <span class="badge-access-code">{{ $quiz->access_code }}</span>
                            </div>

                            <div class="row text-center mb-3">
                                <div class="col-6">
                                    <strong>{{ $quiz->questions->count() }}</strong>
                                    <small class="d-block text-muted">Pertanyaan</small>
                                </div>
                                <div class="col-6">
                                    <strong>{{ $quiz->studentSessions()->count() }}</strong>
                                    <small class="d-block text-muted">Total Peserta</small>
                                </div>
                            </div>

                            <div class="btn-group w-100" role="group">
                                <a href="{{ route('quizzes.show', $quiz) }}" class="btn btn-sm btn-outline-primary">
                                    Kelola
                                </a>
                                @if(!$quiz->is_active)
                                    <form method="POST" action="{{ route('quizzes.start', $quiz) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success">
                                            Mulai
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('quizzes.stop', $quiz) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-warning">
                                            Hentikan
                                        </button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('quizzes.destroy', $quiz) }}"
                                      class="d-inline" onsubmit="return confirm('Yakin ingin menghapus quiz ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
