@extends('layouts.app')

@section('title', 'Edit Quiz - ' . $quiz->title)

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body p-5">
                <h3 class="card-title mb-4">Edit Quiz</h3>

                <form method="POST" action="{{ route('quizzes.update', $quiz) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="title" class="form-label">Judul Quiz</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                               id="title" name="title" value="{{ old('title', $quiz->title) }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3">{{ old('description', $quiz->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="time_limit" class="form-label">Batas Waktu (Opsional)</label>
                        <div class="input-group">
                            <input type="number" class="form-control @error('time_limit') is-invalid @enderror" 
                                   id="time_limit" name="time_limit" value="{{ old('time_limit', $quiz->time_limit) }}" 
                                   placeholder="Dalam detik">
                            <span class="input-group-text">detik</span>
                        </div>
                        <small class="d-block mt-1">Kosongkan jika tidak ada batas waktu</small>
                        @error('time_limit')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Update Quiz</button>
                        <a href="{{ route('quizzes.show', $quiz) }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
