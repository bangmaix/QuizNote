@extends('layouts.app')

@section('title', 'Edit Pertanyaan')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body p-5">
                <h3 class="card-title mb-4">Edit Pertanyaan</h3>

                <form method="POST" 
                      action="{{ route('quizzes.questions.update', [$quiz, $question]) }}"
                      enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="text" class="form-label">Teks Pertanyaan</label>
                        <textarea class="form-control @error('text') is-invalid @enderror" 
                                  id="text" name="text" rows="3" required>{{ old('text', $question->text) }}</textarea>
                        @error('text')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="score" class="form-label">Poin (Score)</label>
                            <input type="number" class="form-control @error('score') is-invalid @enderror" 
                                   id="score" name="score" value="{{ old('score', $question->score) }}" min="1" required>
                            @error('score')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="audio_file" class="form-label">File Audio (MP3, WAV, OGG)</label>
                            <input type="file" class="form-control @error('audio_file') is-invalid @enderror" 
                                   id="audio_file" name="audio_file" accept=".mp3,.wav,.ogg,.webm">
                            @if($question->audio_file)
                                <div class="alert alert-info mt-2">
                                    <small>✓ Audio sudah tersimpan</small>
                                </div>
                            @endif
                            @error('audio_file')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            <!-- Audio Recorder -->
                            <div class="mt-2 p-2 border rounded bg-light" id="audio-recorder-section">
                                <div class="text-center text-muted mb-2"><small>— atau rekam langsung —</small></div>
                                <div id="recorder-denied" class="d-none">
                                    <div class="alert alert-warning py-2 px-3 mb-2" style="font-size:.82rem;">
                                        <strong>⚠️ Akses mikrofon diblokir.</strong><br>
                                        Untuk mengizinkan kembali:<br>
                                        1. Klik ikon 🔒 di address bar browser<br>
                                        2. Pilih <strong>Microphone → Allow</strong><br>
                                        3. Muat ulang halaman ini (F5)
                                    </div>
                                </div>
                                <div id="recorder-idle">
                                    <button type="button" class="btn btn-outline-danger btn-sm w-100" id="btn-start-record">
                                        🎤 Rekam Suara
                                    </button>
                                </div>
                                <div id="recorder-active" class="d-none text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
                                        <span class="rec-dot"></span>
                                        <span id="record-timer" class="text-danger fw-bold">0:00</span>
                                        <button type="button" class="btn btn-danger btn-sm" id="btn-stop-record">⏹ Stop</button>
                                    </div>
                                </div>
                                <div id="recorder-preview" class="d-none">
                                    <audio id="recorded-audio-preview" controls class="w-100 mb-2" style="height:36px;"></audio>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-success btn-sm flex-fill" id="btn-use-recording">✓ Gunakan Rekaman</button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-rerecord">🔄 Ulang</button>
                                    </div>
                                </div>
                                <div id="recorder-used" class="d-none">
                                    <small class="text-success fw-semibold">✓ Rekaman siap dikirim</small>
                                    <button type="button" class="btn btn-link btn-sm text-danger p-0 ms-2" id="btn-clear-recording">✕ Hapus</button>
                                </div>
                                <small id="recorder-error" class="text-danger d-none"></small>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="image_file" class="form-label">File Gambar (JPG, PNG, GIF)</label>
                            <input type="file" class="form-control @error('image_file') is-invalid @enderror" 
                                   id="image_file" name="image_file" accept="image/*">
                            @if($question->image_file)
                                <div class="alert alert-info mt-2">
                                    <small>✓ Gambar sudah tersimpan</small>
                                </div>
                            @endif
                            @error('image_file')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr>
                    <h5 class="mb-3">Pilihan Jawaban</h5>
                    <div class="list-group mb-3">
                        @foreach($question->answers as $index => $answer)
                            <div class="list-group-item">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <strong>{{ $index + 1 }}. {{ $answer->text }}</strong>
                                        @if($answer->is_correct)
                                            <span class="badge bg-success">Benar ✓</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

    <p class="text-muted small">Untuk mengedit jawaban, silakan hapus pertanyaan dan buat ulang dengan jawaban yang benar.</p>

                    <hr>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Update Pertanyaan</button>
                        <a href="{{ route('quizzes.show', $quiz) }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .rec-dot {
        display: inline-block;
        width: 10px;
        height: 10px;
        background: red;
        border-radius: 50%;
        animation: rec-blink 1s step-start infinite;
    }
    @@keyframes rec-blink { 0%, 100% { opacity: 1; } 50% { opacity: 0; } }
</style>
<script>
    let mediaRecorder = null;
    let recordedChunks = [];
    let recordingBlob = null;
    let timerInterval = null;
    let recordingSeconds = 0;

    function fmtTime(s) {
        return `${Math.floor(s / 60)}:${String(s % 60).padStart(2, '0')}`;
    }

    document.getElementById('btn-start-record').addEventListener('click', async () => {
        const errEl = document.getElementById('recorder-error');
        errEl.classList.add('d-none');

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            errEl.textContent = 'Browser tidak mendukung perekaman audio. Gunakan Chrome atau Firefox terbaru.';
            errEl.classList.remove('d-none');
            return;
        }

        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            recordedChunks = [];

            const mimeType = ['audio/webm', 'audio/ogg', ''].find(t => !t || MediaRecorder.isTypeSupported(t));
            mediaRecorder = new MediaRecorder(stream, mimeType ? { mimeType } : {});

            mediaRecorder.ondataavailable = e => { if (e.data.size > 0) recordedChunks.push(e.data); };
            mediaRecorder.onstop = () => {
                stream.getTracks().forEach(t => t.stop());
                recordingBlob = new Blob(recordedChunks, { type: mediaRecorder.mimeType });
                document.getElementById('recorded-audio-preview').src = URL.createObjectURL(recordingBlob);
                document.getElementById('recorder-active').classList.add('d-none');
                document.getElementById('recorder-preview').classList.remove('d-none');
                clearInterval(timerInterval);
            };

            mediaRecorder.start();
            recordingSeconds = 0;
            document.getElementById('record-timer').textContent = fmtTime(0);
            document.getElementById('recorder-idle').classList.add('d-none');
            document.getElementById('recorder-active').classList.remove('d-none');
            timerInterval = setInterval(() => {
                recordingSeconds++;
                document.getElementById('record-timer').textContent = fmtTime(recordingSeconds);
            }, 1000);

        } catch (err) {
            let msg = 'Tidak dapat mengakses mikrofon.';
            if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                msg = 'Akses mikrofon ditolak. Klik ikon 🔒 di address bar browser lalu izinkan mikrofon, kemudian muat ulang halaman.';
            } else if (err.name === 'NotFoundError') {
                msg = 'Mikrofon tidak ditemukan. Pastikan perangkat audio terhubung.';
            } else {
                msg = 'Tidak dapat mengakses mikrofon: ' + err.message;
            }
            errEl.textContent = msg;
            errEl.classList.remove('d-none');
        }
    });

    document.getElementById('btn-stop-record').addEventListener('click', () => {
        if (mediaRecorder && mediaRecorder.state !== 'inactive') mediaRecorder.stop();
    });

    document.getElementById('btn-use-recording').addEventListener('click', () => {
        if (!recordingBlob) return;
        const ext = recordingBlob.type.includes('ogg') ? 'ogg' : 'webm';
        const file = new File([recordingBlob], `rekaman.${ext}`, { type: recordingBlob.type });
        const dt = new DataTransfer();
        dt.items.add(file);
        document.getElementById('audio_file').files = dt.files;
        document.getElementById('recorder-preview').classList.add('d-none');
        document.getElementById('recorder-used').classList.remove('d-none');
    });

    document.getElementById('btn-rerecord').addEventListener('click', () => {
        recordingBlob = null;
        document.getElementById('recorded-audio-preview').src = '';
        document.getElementById('recorder-preview').classList.add('d-none');
        document.getElementById('recorder-idle').classList.remove('d-none');
    });

    document.getElementById('btn-clear-recording').addEventListener('click', () => {
        recordingBlob = null;
        document.getElementById('audio_file').value = '';
        document.getElementById('recorder-used').classList.add('d-none');
        document.getElementById('recorder-idle').classList.remove('d-none');
    });
</script>
@endsection
