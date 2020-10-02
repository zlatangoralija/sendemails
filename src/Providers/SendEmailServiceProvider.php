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

        $this->mergeConfigFrom(
            __DIR__ . '/../config/sendemails.php',
            'sendemails'
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

        $this->registerPublishing();
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    private function registerPublishing()
    {
        $this->publishes([
            __DIR__.'/../config/sendemails.php' => base_path('config/sendemails.php'),
        ], 'config');
    }

}