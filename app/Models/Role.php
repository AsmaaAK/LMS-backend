<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    /**
     * الحقول التي يمكن تعبئتها جماعياً (Mass Assignment)
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * الحقول التي يجب إخفاؤها عند التحويل إلى مصفوفة أو JSON
     *
     * @var array<string>
     */
    protected $hidden = [
        'pivot',
        'created_at',
        'updated_at',
    ];

    /**
     * العلاقة: الدور له العديد من المستخدمين
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user');
                    // ->withTimestamps();
    }

    /**
     * العلاقة: الدور له العديد من الصلاحيات
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role');
                    // ->withTimestamps();
    }

    /**
     * إضافة صلاحية للدور
     *
     * @param  string|array  $permissions
     * @return void
     */
    public function givePermissionTo($permissions)
    {
        $permissions = is_array($permissions) ? $permissions : [$permissions];
        
        $permissionIds = Permission::whereIn('name', $permissions)
            ->pluck('id')
            ->toArray();

        $this->permissions()->syncWithoutDetaching($permissionIds);
    }

    /**
     * إزالة صلاحية من الدور
     *
     * @param  string|array  $permissions
     * @return void
     */
    public function revokePermissionTo($permissions)
    {
        $permissions = is_array($permissions) ? $permissions : [$permissions];
        
        $permissionIds = Permission::whereIn('name', $permissions)
            ->pluck('id')
            ->toArray();

        $this->permissions()->detach($permissionIds);
    }

    /**
     * التحقق إذا كان الدور لديه صلاحية معينة
     *
     * @param  string  $permission
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        return $this->permissions()
            ->where('name', $permission)
            ->exists();
    }

    /**
     * الحصول على جميع أسماء الصلاحيات للدور
     *
     * @return array
     */
    public function getPermissionNames(): array
    {
        return $this->permissions->pluck('name')->toArray();
    }

    /**
     * نطاق الاستعلام: الحصول على الأدوار حسب الاسم
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $name
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByName($query, string $name)
    {
        return $query->where('name', $name);
    }

    /**
     * نطاق الاستعلام: الحصول على الأدوار التي تحتوي على صلاحية معينة
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $permission
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithPermission($query, string $permission)
    {
        return $query->whereHas('permissions', function ($q) use ($permission) {
            $q->where('name', $permission);
        });
    }

    /**
     * البحث عن دور حسب الاسم
     *
     * @param  string  $name
     * @return \App\Models\Role|null
     */
    public static function findByName(string $name): ?Role
    {
        return static::where('name', $name)->first();
    }

    /**
     * البحث عن دور حسب الاسم أو إنشائه إذا لم يوجد
     *
     * @param  string  $name
     * @param  string|null  $description
     * @return \App\Models\Role
     */
    public static function findOrCreate(string $name, ?string $description = null): Role
    {
        $role = static::findByName($name);

        if (!$role) {
            $role = static::create([
                'name' => $name,
                'description' => $description,
            ]);
        }

        return $role;
    }

    /**
     * التحقق إذا كان الدور هو دور المشرف
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->name === 'admin';
    }

    /**
     * التحقق إذا كان الدور هو دور المدرب
     *
     * @return bool
     */
    public function isInstructor(): bool
    {
        return $this->name === 'instructor';
    }

    /**
     * التحقق إذا كان الدور هو دور الطالب
     *
     * @return bool
     */
    public function isStudent(): bool
    {
        return $this->name === 'student';
    }

    /**
     * تنسيق البيانات عند التحويل إلى مصفوفة
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'permissions' => $this->getPermissionNames(),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}