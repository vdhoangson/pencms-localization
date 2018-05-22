<?php

namespace vdhoangson\Localization\Middlewares;

use Illuminate\Http\RedirectResponse;
use Closure;
use Illuminate\Routing\Redirector;
use Illuminate\Foundation\Application;

class LocalizationMiddleware {
    public function __construct(Application $app, Redirector $redirector, \Request $request) {
        $this->app = $app;
        $this->redirector = $redirector;
        $this->request = $request;
    }
    /**
     *
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $app = app('localization');
        
        $currentLocale = $app->currentLocale;
        $defaultLocale = $app->defaultLocale;

        $locale = $request->segment(1);
        $ignore = ['api'];

        if(in_array($locale, $ignore)){
            return $next($request);
        }
        
        if(is_null($locale) || !$app->getLanguageByCode($locale)){
            $segments = $request->segments();
            array_unshift($segments, $this->app->config->get('cms.defaultLocale'));
            
            return $this->redirector->to(implode('/', $segments));
        } else if($locale !== $currentLocale && $app->getLanguageByCode($locale)){
            $app->setLocale($locale);
        }

        return $next($request);
    }
}