<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureRateLimiters();
        $this->configurePagination();
    }

    /**
     * Rate limiter aplikasi: batasi spam formulir kontak publik.
     */
    protected function configureRateLimiters(): void
    {
        RateLimiter::for('kontak', function (Request $request): Limit {
            return Limit::perMinute(5)->by($request->ip());
        });
    }

    /**
     * Pagination: aplikasi memakai Bootstrap 5, bukan Tailwind.
     *
     * Tanpa ini, $paginator->links() memakai view Tailwind bawaan Laravel yang
     * merender ikon panah sebagai SVG dengan class `w-5 h-5`. Class tersebut
     * tidak ada di Bootstrap sehingga SVG tampil membesar/rusak. Default view
     * diarahkan ke view Bootstrap 5 milik aplikasi agar ikon memakai
     * bootstrap-icons (bi bi-chevron-left / bi bi-chevron-right).
     */
    protected function configurePagination(): void
    {
        Paginator::useBootstrapFive();
        Paginator::defaultView('vendor.pagination.bootstrap-5');
    }

    /**
     * Configure default behaviors production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
