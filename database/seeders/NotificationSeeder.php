<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;

class NotificationSeeder extends Seeder
{
    public function run()
    {
        // الحصول على جميع المستخدمين
        $users = User::all();

        foreach ($users as $user) {
            // إنشاء 3-8 إشعارات لكل مستخدم
            $notificationCount = rand(3, 8);
            
            Notification::factory()->count($notificationCount)->create([
                'user_id' => $user->id,
            ]);
        }

        // إضافة إشعارات إضافية
        Notification::factory()->count(20)->create();
    }
}