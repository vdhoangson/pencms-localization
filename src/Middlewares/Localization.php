<?php

namespace vdhoangson\Localization\Middlewares;

use Illuminate\Http\RedirectResponse;
use Closure;
use Session;
use App;

class Localisation {
    /**
     *
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
    	$app = app('penlocalisation');
    	$currentLocale = $app->currentLocale;
        $defaultLocale = $app->defaultLocale;
        $params = explode('/', $request->path());

        $locale = session('locale', false);

        if ( count($params) > 0 ) {
            $localeCode = $params[0];
            $hideDefaultLocale = $app->hideDefaultLocaleInURL();
            $redirection = false;

            if ($app->getLanguageByCode($localeCode)) {
                if ( $localeCode === $defaultLocale && $hideDefaultLocale )
                {
                    $redirection = $app->getNonLocalizedURL();
                }
            } else if (!empty($currentLocale) && $currentLocale !== $defaultLocale || !$hideDefaultLocale ) {
                
                $redirection = $app->getURL(session('locale'), $request->fullUrl());
            } else {
                $redirection = '';
            }

            if ( $redirection ) {
                // Save any flashed data for redirect
                app('session')->reflash();

                return new RedirectResponse($redirection, 302, [ 'Vary' => 'Accept-Language' ]);
            }
        }

        return $next($request);
    }
}