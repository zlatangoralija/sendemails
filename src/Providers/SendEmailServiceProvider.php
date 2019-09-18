<?php

namespace Omnitask\SendEmailRepository\Providers;

use Illuminate\Support\ServiceProvider;
use Omnitask\SendEmailRepository\Interfaces\SendEmailInterface;
use Omnitask\SendEmailRepository\SendEmailRepository;

class SendEmailServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */

    public function register()
    {
        $this->app->bind(
            SendEmailInterface::class,
            SendEmailRepository::class
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../migrations');

        if (! $this->app->routesAreCached()) {
            require __DIR__.'/../routes/web.php';
        }

    }

}