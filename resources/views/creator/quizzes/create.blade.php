@extends('layouts.app')

@section('title', 'Buat Quiz Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body p-5">
                <h3 class="card-title mb-4">Buat Quiz Baru</h3>

                <form method="POST" action="{{ route('quizzes.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="title" class="form-label">Judul Quiz</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                               id="title" name="title" value="{{ old('title') }}" placeholder="Contoh: Mengenal Angka" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi (Opsional)</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3" 
                                  placeholder="Jelaskan tentang quiz ini...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="time_limit" class="form-label">Batas Waktu (Opsional)</label>
                        <div class="input-group">
                            <input type="number" class="form-control @error('time_limit') is-invalid @enderror" 
                                   id="time_limit" name="time_limit" value="{{ old('time_limit') }}" 
                                   placeholder="Dalam detik (contoh: 300 untuk 5 menit)">
                            <span class="input-group-text">detik</span>
                        </div>
                        <small class="d-block mt-1">Kosongkan jika tidak ada batas waktu</small>
                        @error('time_limit')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Buat Quiz</button>
                        <a href="{{ route('creator.dashboard') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
