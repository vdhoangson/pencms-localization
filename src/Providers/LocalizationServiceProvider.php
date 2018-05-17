<?php
namespace vdhoangson\Localization\Providers;

use Illuminate\Support\ServiceProvider;
use vdhoangson\Localization\Localization;
use \Illuminate\Foundation\AliasLoader;
use vdhoangson\Localization\Middlewares\Localization as LocalizationMiddleware;
use \vdhoangson\Localization\Facades\Localization as LocalizationFacade;

class LocalizationServiceProvider extends ServiceProvider {
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    public function boot(){
        $this->app['router']->aliasMiddleware('LocalizationMiddleware', LocalizationMiddleware::class);
    }
    
    public function register(){
        $this->publishes([
            realpath(__DIR__.'/../../config/localization.php') => config_path('localization.php'),
            realpath(__DIR__.'/../../database/create_language_table.php') => database_path('create_language_table.php'),
        ]);

        $this->app->singleton('Localization', function(){
            return new Localization();
        });

        $this->app->alias(Localization::class, 'localization');
    }
}
