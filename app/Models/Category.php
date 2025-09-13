<?php

namespace App\Models;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    // العلاقات
    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    // السمات (Attributes)
    public function getTotalCoursesAttribute()
    {
        return $this->courses()->count();
    }

    public function getPublishedCoursesCountAttribute()
    {
        return $this->courses()->where('is_published', true)->count();
    }

    // النطاقات (Scopes)
    public function scopeWithCourses($query)
    {
        return $query->whereHas('courses');
    }

    public function scopeWithPublishedCourses($query)
    {
        return $query->whereHas('courses', function ($query) {
            $query->where('is_published', true);
        });
    }

    // الأحداث (Events)
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }
}