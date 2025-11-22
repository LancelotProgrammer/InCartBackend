<?php

use App\Models\Role;
use App\Models\User;
use App\Notifications\ExceptionNotification;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api/v1',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo('/admin/login');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (Throwable $e) {
            try {
                $developer = User::whereHas('role', function ($query) {
                    $query->where('code', '=', Role::ROLE_DEVELOPER_CODE);
                })->first();
                $title = "Exception Occurred: ".class_basename($e);
                $body = $e->getMessage();
                $developer->notify(new ExceptionNotification($title, $body));
                Log::channel('app_log')->emergency('EXCEPTION: EXCEPTION REPORT NOTIFICATION CREATED',[
                    'exception_message' => $e->getMessage(),
                ]);
            } catch (Throwable $e) {
                Log::channel('app_log')->emergency('EXCEPTION: AN ERROR OCCURRED WHEN REPORTING AN EXCEPTION !!!!!', [
                    'exception_message' => $e->getMessage(),
                ]);
            }
        });
    })->create();
