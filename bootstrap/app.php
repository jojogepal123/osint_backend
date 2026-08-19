<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // The app always runs behind nginx (and, in production, a host-level
        // reverse proxy), so REMOTE_ADDR is the Docker bridge gateway —
        // 172.18.0.1 — for every request. Trusting the proxy makes
        // request()->ip() resolve the client's real address from
        // X-Forwarded-For instead.
        //
        // Only private / loopback ranges are trusted: a client on the public
        // internet can never present one of these as its peer address, so the
        // header cannot be spoofed from outside. Add your edge's ranges here
        // if you put Cloudflare or a cloud load balancer in front.
        $middleware->trustProxies(at: [
            '127.0.0.1',
            '10.0.0.0/8',
            '172.16.0.0/12',
            '192.168.0.0/16',
        ]);

        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
            'token.expire' => App\Http\Middleware\ExpireSanctumToken::class,
            'admin' => App\Http\Middleware\IsAdmin::class,
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
