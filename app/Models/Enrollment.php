<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'progress',
        'enrolled_at',
        'completed_at',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // العلاقات
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // السمات (Attributes)
    public function getIsCompletedAttribute()
    {
        return !is_null($this->completed_at);
    }

    public function getProgressPercentageAttribute()
    {
        return $this->progress;
    }

    public function getEnrollmentDurationAttribute()
    {
        if ($this->completed_at) {
            return $this->enrolled_at->diffInDays($this->completed_at);
        }
        
        return $this->enrolled_at->diffInDays(now());
    }

    // النطاقات (Scopes)
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('completed_at');
    }

    public function scopeInProgress($query)
    {
        return $query->whereNull('completed_at');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    public function scopeWithHighProgress($query, $threshold = 80)
    {
        return $query->where('progress', '>=', $threshold);
    }

    // الطرق المساعدة
    public function markAsCompleted()
    {
        $this->update([
            'progress' => 100,
            'completed_at' => now(),
        ]);
    }

    public function updateProgress($progress)
    {
        $this->update(['progress' => min(100, max(0, $progress))]);
        
        if ($this->progress >= 100 && !$this->completed_at) {
            $this->markAsCompleted();
        }
    }
}