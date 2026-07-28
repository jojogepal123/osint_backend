<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Throwable;

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
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        $this->ensureStorageDirectories();
    }

    /**
     * Make sure every Storage sub-directory the app writes to exists.
     *
     * Some controllers (cibil reports, social intel reports, dompdf temp/cache,
     * and the laravel-dompdf font cache) call Storage::put / file_put_contents
     * against paths like `storage/app/private/reports/...` without first calling
     * Storage::makeDirectory. PHP's file_put_contents does not create
     * intermediate directories, so the first write fails with
     * "No such file or directory". Bootstrapping the directories here is
     * idempotent and avoids that failure mode.
     */
    private function ensureStorageDirectories(): void
    {
        $dirs = [
            'cibil_reports',
            'reports',
            'fonts',
        ];

        foreach ($dirs as $dir) {
            try {
                if (method_exists(Storage::disk('local'), 'makeDirectory')) {
                    Storage::disk('local')->makeDirectory($dir);
                } else {
                    /** @var FilesystemAdapter $disk */
                    $disk = Storage::disk('local');
                    $fullPath = $disk->path($dir);
                    if (! is_dir($fullPath)) {
                        @mkdir($fullPath, 0755, true);
                    }
                }
            } catch (Throwable $e) {
                // Don't break the app boot if a directory can't be created —
                // log and move on.
                logger()->warning('Failed to ensure storage directory', [
                    'directory' => $dir,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Also make sure the storage/app/public directory exists for any
        // files that get symlinked out via `php artisan storage:link`.
        try {
            $publicStorage = storage_path('app/public');
            if (! is_dir($publicStorage)) {
                @mkdir($publicStorage, 0755, true);
            }
        } catch (Throwable $e) {
            // ignore
        }
    }
}
