<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Course;
use App\Models\Lesson;
use App\Policies\UserPolicy;
use App\Policies\CoursePolicy;
use App\Policies\LessonPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

// use App\Provider\Route;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Course::class => CoursePolicy::class,
        Lesson::class => LessonPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
    // $this->configureRateLimiting();

    // $this->routes(function () {
    //         Route::prefix('api')
    //             ->middleware('api')
    //             ->namespace($this->namespace)
    //             ->group(base_path('routes/api.php'));

    //         Route::middleware('web')
    //             ->namespace($this->namespace)
    //             ->group(base_path('routes/web.php'));
    //     });
    RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(
                $request->user()?->id ?: $request->Ip()
            );
        });
    }

    protected function configureRateLimiting(): void
    {
        
    }
}