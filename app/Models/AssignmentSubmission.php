<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssignmentSubmission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'assignment_id',
        'file_path',
        'comment',
        'score',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'score' => 'integer',
    ];

    // العلاقات
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    // السمات (Attributes)
    public function getIsGradedAttribute()
    {
        return !is_null($this->score);
    }

    public function getGradePercentageAttribute()
    {
        if (!$this->score || !$this->assignment->max_score) {
            return null;
        }
        
        return ($this->score / $this->assignment->max_score) * 100;
    }

    public function getGradeStatusAttribute()
    {
        if (!$this->is_graded) {
            return 'pending';
        }
        
        $percentage = $this->grade_percentage;
        
        if ($percentage >= 90) return 'excellent';
        if ($percentage >= 80) return 'very_good';
        if ($percentage >= 70) return 'good';
        if ($percentage >= 60) return 'pass';
        
        return 'fail';
    }

    public function getIsLateAttribute()
    {
        if (!$this->assignment->deadline || !$this->submitted_at) {
            return false;
        }
        
        return $this->submitted_at->greaterThan($this->assignment->deadline);
    }

    public function getDaysLateAttribute()
    {
        if (!$this->is_late) {
            return 0;
        }
        
        return $this->assignment->deadline->diffInDays($this->submitted_at);
    }

    // النطاقات (Scopes)
    public function scopeGraded($query)
    {
        return $query->whereNotNull('score');
    }

    public function scopeUngraded($query)
    {
        return $query->whereNull('score');
    }

    public function scopeLate($query)
    {
        return $query->whereHas('assignment', function ($query) {
            $query->whereNotNull('deadline')
                  ->whereColumn('assignment_submissions.submitted_at', '>', 'assignments.deadline');
        });
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByAssignment($query, $assignmentId)
    {
        return $query->where('assignment_id', $assignmentId);
    }

    // الطرق المساعدة
    public function grade($score, $comment = null)
    {
        $this->update([
            'score' => min($score, $this->assignment->max_score),
            'comment' => $comment ?? $this->comment,
        ]);
    }
}