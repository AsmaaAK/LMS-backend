<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    const LEVEL_BEGINNER = 'beginner';
    const LEVEL_INTERMEDIATE = 'intermediate';
    const LEVEL_ADVANCED = 'advanced';

    protected $fillable = [
        'title', 'description', 'image', 'category_id', 'user_id',
        'level', 'price', 'is_published'
    ];

    // العلاقات
    public function instructor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    // app/Models/Course.php

public function students()
{
    return $this->belongsToMany(User::class, 'enrollments')
                ->withPivot('progress', 'enrolled_at')
                ->withTimestamps();
}


    // السمات (Attributes)
    public function getTotalLessonsAttribute()
    {
        return $this->lessons()->count();
    }

    public function getTotalStudentsAttribute()
    {
        return $this->enrollments()->count();
    }

    //(Scopes)
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }
}
