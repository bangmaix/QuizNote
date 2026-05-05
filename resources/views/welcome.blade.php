@extends('layouts.app')

@section('title', 'QuizNote')

@section('content')
<div class="container">
    <div class="text-center mb-5">
        <h1 class="display-4 text-white mb-3">🎯 QuizNote</h1>
        <p class="lead text-white-50 mb-4">Sistem Quiz Interaktif untuk Balita dengan Suara dan Gambar</p>
    </div>

    <div class="row mb-5">
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body text-center p-5">
                    <h3 class="mb-3">👨‍🏫 Pembuat Soal?</h3>
                    <p class="text-muted mb-4">Buat quiz interaktif dengan audio dan gambar untuk anak-anak</p>
                    <ul class="list-unstyled mb-4">
                        <li>✓ Buat quiz dengan pertanyaan bersuara</li>
                        <li>✓ Tambah gambar untuk visual learning</li>
                        <li>✓ Atur poin untuk setiap pertanyaan</li>
                        <li>✓ Kelola akses peserta dengan kode</li>
                    </ul>
                    <a href="{{ route('register') }}?role=creator" class="btn btn-primary btn-lg">
                        Daftar Sebagai Creator
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body text-center p-5">
                    <h3 class="mb-3">👧 Peserta?</h3>
                    <p class="text-muted mb-4">Ikuti quiz interaktif dan pelajari hal-hal baru</p>
                    <ul class="list-unstyled mb-4">
                        <li>✓ Ikuti quiz dengan kode akses</li>
                        <li>✓ Dengarkan pertanyaan bersuara</li>
                        <li>✓ Lihat gambar dan jawab pertanyaan</li>
                        <li>✓ Dapatkan feedback langsung</li>
                    </ul>
                    <a href="{{ route('register') }}?role=student" class="btn btn-primary btn-lg">
                        Daftar Sebagai Peserta
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="mb-2">🎓</h4>
                    <h6>Pembelajaran Interaktif</h6>
                    <p class="text-muted small">Quiz dengan audio dan visual untuk anak yang belum bisa membaca</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="mb-2">📊</h4>
                    <h6>Penilaian Otomatis</h6>
                    <p class="text-muted small">Sistem scoring yang fleksibel dan feedback langsung</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="mb-2">🔐</h4>
                    <h6>Aman & Mudah</h6>
                    <p class="text-muted small">Akses quiz hanya dengan kode 6 digit</p>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-5">
        <p class="text-white">Sudah punya akun? <a href="{{ route('login') }}" class="link-light">Login di sini</a></p>
    </div>
</div>
@endsection
