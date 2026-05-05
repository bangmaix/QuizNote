<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentResponse extends Model
{
    protected $fillable = ['student_session_id', 'question_id', 'answer_id', 'is_correct', 'is_second_attempt', 'answered_at'];
    protected $casts = [
        'answered_at' => 'datetime',
        'is_correct' => 'boolean',
        'is_second_attempt' => 'boolean',
    ];

    public function session()
    {
        return $this->belongsTo(StudentSession::class, 'student_session_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function answer()
    {
        return $this->belongsTo(Answer::class);
    }
}
