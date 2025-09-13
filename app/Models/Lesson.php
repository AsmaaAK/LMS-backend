<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lesson extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'content',
        'video_url',
        'course_id',
        'order',
    ];

    // العلاقات
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    // السمات (Attributes)
    public function getNextLessonAttribute()
    {
        return $this->course->lessons()
            ->where('order', '>', $this->order)
            ->orderBy('order')
            ->first();
    }

    public function getPreviousLessonAttribute()
    {
        return $this->course->lessons()
            ->where('order', '<', $this->order)
            ->orderBy('order', 'desc')
            ->first();
    }

    public function getIsFirstLessonAttribute()
    {
        return $this->order === 1;
    }

    public function getIsLastLessonAttribute()
    {
        $maxOrder = $this->course->lessons()->max('order');
        return $this->order === $maxOrder;
    }

    // النطاقات (Scopes)
    public function scopeByCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    public function scopePublished($query)
    {
        return $query->whereHas('course', function ($query) {
            $query->where('is_published', true);
        });
    }

    // الأحداث (Events)
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($lesson) {
            if (empty($lesson->order)) {
                $maxOrder = Lesson::where('course_id', $lesson->course_id)->max('order');
                $lesson->order = $maxOrder ? $maxOrder + 1 : 1;
            }
        });
    }
}