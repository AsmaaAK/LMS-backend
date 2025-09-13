<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'course_id',
        'lesson_id',
        'deadline',
        'max_score',
    ];

    protected $casts = [
        'deadline' => 'datetime',
    ];

    // العلاقات
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function submissions()
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    // السمات (Attributes)
    public function getTotalSubmissionsAttribute()
    {
        return $this->submissions()->count();
    }

    public function getGradedSubmissionsCountAttribute()
    {
        return $this->submissions()->whereNotNull('score')->count();
    }

    public function getIsPastDeadlineAttribute()
    {
        return $this->deadline && now()->greaterThan($this->deadline);
    }

    public function getDaysUntilDeadlineAttribute()
    {
        if (!$this->deadline) return null;
        
        return now()->diffInDays($this->deadline, false);
    }

    // النطاقات (Scopes)
    public function scopeByCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    public function scopeByLesson($query, $lessonId)
    {
        return $query->where('lesson_id', $lessonId);
    }

    public function scopeWithDeadline($query)
    {
        return $query->whereNotNull('deadline');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('deadline', '>', now());
    }

    public function scopePastDeadline($query)
    {
        return $query->where('deadline', '<', now());
    }
}