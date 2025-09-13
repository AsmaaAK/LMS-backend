<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    const TYPE_ASSIGNMENT_GRADED = 'assignment_graded';
    const TYPE_NEW_ASSIGNMENT = 'new_assignment';
    const TYPE_DEADLINE_REMINDER = 'deadline_reminder';
    const TYPE_COURSE_ANNOUNCEMENT = 'course_announcement';
    const TYPE_ENROLLMENT_APPROVED = 'enrollment_approved';
    const TYPE_SYSTEM_ALERT = 'system_alert';

    protected $fillable = [
        'type',
        'data',
        'read_at',
        'user_id',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    // العلاقات
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // السمات (Attributes)
    public function getIsReadAttribute()
    {
        return !is_null($this->read_at);
    }

    public function getIsUnreadAttribute()
    {
        return is_null($this->read_at);
    }

    public function getTitleAttribute()
    {
        $titles = [
            self::TYPE_ASSIGNMENT_GRADED => 'تم تقييم واجبك',
            self::TYPE_NEW_ASSIGNMENT => 'واجب جديد',
            self::TYPE_DEADLINE_REMINDER => 'تذكير بموعد التسليم',
            self::TYPE_COURSE_ANNOUNCEMENT => 'إعلان جديد',
            self::TYPE_ENROLLMENT_APPROVED => 'تم قبول تسجيلك',
            self::TYPE_SYSTEM_ALERT => 'تنبيه نظام',
        ];

        return $titles[$this->type] ?? 'إشعار جديد';
    }

    public function getMessageAttribute()
    {
        return $this->data['message'] ?? '';
    }

    public function getRelatedObjectAttribute()
    {
        return $this->data['related_object'] ?? null;
    }

    // النطاقات (Scopes)
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // الطرق المساعدة
    public function markAsRead()
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    public function markAsUnread()
    {
        $this->update(['read_at' => null]);
    }

    // الأحداث (Events)
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($notification) {
            if (empty($notification->type)) {
                $notification->type = self::TYPE_SYSTEM_ALERT;
            }
        });
    }
}