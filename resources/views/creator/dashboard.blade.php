@extends('layouts.app')

@section('title', 'Creator Dashboard')

@section('content')
<div class="container">
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h2 class="text-white">👨‍🏫 Dashboard Pembuat Soal</h2>
            <p class="text-white-50">Kelola quiz dan soal Anda</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('quizzes.create') }}" class="btn btn-light">
                ➕ Buat Quiz Baru
            </a>
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
                                    <strong>{{ $quiz->studentSessions()->whereNull('completed_at')->count() }}</strong>
                                    <small class="d-block text-muted">Sedang Dikerjakan</small>
                                </div>
                            </div>

                            {{-- Statistik Peserta --}}
                            @php
                                $completedSessions = $quiz->studentSessions;
                                $totalMax = $quiz->questions->sum('score');
                                $totalParticipants = $completedSessions->count();
                                $avgScore = $totalParticipants > 0 ? round($completedSessions->avg('score_total'), 1) : 0;
                                $avgPct   = $totalMax > 0 ? round(($avgScore / $totalMax) * 100) : 0;
                                $highScore = $totalParticipants > 0 ? $completedSessions->max('score_total') : 0;
                                $lowScore  = $totalParticipants > 0 ? $completedSessions->min('score_total') : 0;
                                $avgDuration = $totalParticipants > 0
                                    ? round($completedSessions->map(fn($s) =>
                                        \Carbon\Carbon::parse($s->started_at)->diffInSeconds(\Carbon\Carbon::parse($s->completed_at))
                                      )->avg())
                                    : 0;
                                $avgMin = floor($avgDuration / 60);
                                $avgSec = $avgDuration % 60;
                                $passCount = $completedSessions->filter(fn($s) => $totalMax > 0 && ($s->score_total / $totalMax) >= 0.6)->count();
                            @endphp

                            @if($totalParticipants > 0)
                                <div class="border rounded p-2 mb-3" style="background:#f8f9fa;">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="fw-semibold text-muted">📊 Statistik Peserta</small>
                                        <small class="text-muted">{{ $totalParticipants }} selesai</small>
                                    </div>

                                    {{-- Rata-rata skor --}}
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between" style="font-size:.78rem;">
                                            <span>Rata-rata skor</span>
                                            <span class="fw-bold">{{ $avgScore }}/{{ $totalMax }} ({{ $avgPct }}%)</span>
                                        </div>
                                        <div class="progress" style="height:6px;">
                                            <div class="progress-bar {{ $avgPct >= 80 ? 'bg-success' : ($avgPct >= 60 ? 'bg-warning' : 'bg-danger') }}"
                                                 style="width:{{ $avgPct }}%"></div>
                                        </div>
                                    </div>

                                    <div class="row text-center g-0" style="font-size:.76rem;">
                                        <div class="col-3 border-end">
                                            <div class="fw-bold text-success">{{ $highScore }}</div>
                                            <div class="text-muted">Tertinggi</div>
                                        </div>
                                        <div class="col-3 border-end">
                                            <div class="fw-bold text-danger">{{ $lowScore }}</div>
                                            <div class="text-muted">Terendah</div>
                                        </div>
                                        <div class="col-3 border-end">
                                            <div class="fw-bold text-primary">{{ $passCount }}</div>
                                            <div class="text-muted">Lulus ≥60%</div>
                                        </div>
                                        <div class="col-3">
                                            <div class="fw-bold">{{ $avgMin }}:{{ str_pad($avgSec, 2, '0', STR_PAD_LEFT) }}</div>
                                            <div class="text-muted">Rata waktu</div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Tabel peserta --}}
                                <details class="mb-3">
                                    <summary style="font-size:.8rem;cursor:pointer;color:#6c757d;">
                                        Lihat detail per peserta ({{ $totalParticipants }})
                                    </summary>
                                    <div class="table-responsive mt-2">
                                        <table class="table table-sm table-bordered mb-0" style="font-size:.76rem;">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Peserta</th>
                                                    <th class="text-center">Skor</th>
                                                    <th class="text-center">%</th>
                                                    <th class="text-center">Waktu</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($completedSessions->sortByDesc('score_total') as $s)
                                                    @php
                                                        $pct = $totalMax > 0 ? round(($s->score_total / $totalMax) * 100) : 0;
                                                        $dur = \Carbon\Carbon::parse($s->started_at)->diffInSeconds(\Carbon\Carbon::parse($s->completed_at));
                                                        $lulus = $pct >= 60;
                                                    @endphp
                                                    <tr class="{{ $lulus ? '' : 'table-danger' }}">
                                                        <td>{{ $s->student->name ?? '-' }}</td>
                                                        <td class="text-center fw-bold">{{ $s->score_total }}/{{ $totalMax }}</td>
                                                        <td class="text-center">
                                                            <span class="badge {{ $lulus ? 'bg-success' : 'bg-danger' }}">{{ $pct }}%</span>
                                                        </td>
                                                        <td class="text-center">{{ floor($dur/60) }}:{{ str_pad($dur%60, 2, '0', STR_PAD_LEFT) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </details>
                            @else
                                <p class="text-muted small mb-3">Belum ada peserta yang menyelesaikan quiz ini.</p>
                            @endif

                            <div class="btn-group w-100" role="group">
                                <a href="{{ route('quizzes.show', $quiz) }}" class="btn btn-sm btn-outline-primary">
                                    Edit
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
                                      class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
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
