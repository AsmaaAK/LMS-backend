<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    const ROLE_STUDENT = 'student';
    const ROLE_TEACHER = 'teacher';
    const ROLE_ADMIN = 'admin';
    const ROLE_MANAGER = 'manager';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', 
        'avatar'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
        // العلاقات
    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function assignmentSubmissions()
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // النطاقات (Scopes)
    public function scopeStudents($query)
    {
        return $query->where('role', self::ROLE_STUDENT);
    }

    public function scopeTeachers($query)
    {
        return $query->where('role', self::ROLE_TEACHER);
    }

    // الطرق المساعدة
    public function isAdmin()
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_MANAGER]);
    }

    public function isTeacher()
    {
        return $this->role === self::ROLE_TEACHER || $this->isAdmin();
    }

    public function isStudent()
    {
        return $this->role === self::ROLE_STUDENT;
    }
    
}
