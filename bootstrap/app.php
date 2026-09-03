<?php

use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Schemas\Schema;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

if (! class_exists(Form::class) && class_exists(Schema::class)) {
    class_alias(Schema::class, Form::class);
}

if (! class_exists(Infolist::class) && class_exists(Schema::class)) {
    class_alias(Schema::class, Infolist::class);
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
