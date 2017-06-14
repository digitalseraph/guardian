<?php

namespace WhiteSunrise\Guardian;

/**
 * This file is part of Entrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Zizaco\Entrust
 */

use Illuminate\Support\ServiceProvider;

class GuardianServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // // Loading routes file
        // require __DIR__ . '/routes.php';

        // Publish config files
        $this->publishes([__DIR__ . '/config/config.php' => config_path('guardian.php')], 'config');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \WhiteSunrise\Guardian\Console\Commands\MigrationCommand::class,
                \WhiteSunrise\Guardian\Console\Commands\SeederCommand::class,
                \WhiteSunrise\Guardian\Console\Commands\ModelCommand::class,
            ]);
        }

        // Register blade directives
        $this->bladeDirectives();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerGuardian();
        $this->mergeConfigFrom(__DIR__ . '/config/config.php', 'guardian');
    }

    /**
     * Register the blade directives
     *
     * @return void
     */
    private function bladeDirectives()
    {
        if (!class_exists('\Blade')) {
            return;
        };

        // Call to Guardian::hasRole
        \Blade::directive('role', function ($expression) {
            return "<?php if (\\Guardian::hasRole({$expression})) : ?>";
        });
        \Blade::directive('endrole', function ($expression) {
            return "<?php endif; // Guardian::hasRole ?>";
        });
        // Call to Guardian::can
        \Blade::directive('permission', function ($expression) {
            return "<?php if (\\Guardian::can({$expression})) : ?>";
        });
        \Blade::directive('endpermission', function ($expression) {
            return "<?php endif; // Guardian::can ?>";
        });
        // Call to Guardian::ability
        \Blade::directive('ability', function ($expression) {
            return "<?php if (\\Guardian::ability({$expression})) : ?>";
        });
        \Blade::directive('endability', function ($expression) {
            return "<?php endif; // Guardian::ability ?>";
        });

        /*
    \Blade::directive('can', function ($expression) {
    return "<?php if(Auth::user()->can({$expression})) { ?>";
    });
    \Blade::directive('endCan', function () {
    return "<?php } ?>";
    });

    \Blade::directive('hasRole', function ($expression) {
    return "<?php if(Auth::user()->hasRole({$expression})) { ?>";
    });
    \Blade::directive('endHasRole', function () {
    return "<?php } ?>";
    });
     */
    }

    /**
     * Register the application bindings.
     *
     * @return void
     */
    private function registerGuardian()
    {
        $this->app->bind('guardian', function ($app) {
            return new Guardian($app);
        });
        $this->app->alias('guardian', 'WhiteSunrise\Guardian\Guardian');
    }
}
