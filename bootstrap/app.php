<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Old URLs of renamed articles, pages, etc. answer with a permanent redirect instead of 404.
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, \Illuminate\Http\Request $request) {
            if (! $request->isMethod('GET') || $request->is('admin', 'admin/*', 'livewire/*')) {
                return null;
            }
            try {
                $redirect = \App\Models\Redirect::findFor($request->getPathInfo());
            } catch (\Throwable) {
                return null;
            }
            if (! $redirect) {
                return null;
            }
            $redirect->increment('hits', 1, ['last_hit_at' => now()]);
            $query = $request->getQueryString();

            return redirect($redirect->to_path.($query ? '?'.$query : ''), 301);
        });
    })->create();
