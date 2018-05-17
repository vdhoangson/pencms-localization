<?php

namespace vdhoangson\Localization;
use \DB;

class Localization {
	private $app;
	private $router;
	private $request;
	public $defaultLocale;
	public $currentLocale;

	protected $baseUrl;

	public function __construct(){
		$this->app = app();
		$this->router = $this->app['router'];
		$this->request = $this->app['request'];
    $this->defaultLocale = 'en';
	}

  public function setLocale( $locale = null ) {
    if ( empty( $locale ) || !is_string($locale) ) {
        $locale = $this->request->segment(1);
    }

    if ($this->getLanguageByCode($locale)) {
        $this->currentLocale = $locale;
    } else {
        $locale = null;
        if ($this->hideDefaultLocaleInURL()) {
            $this->currentLocale = $this->defaultLocale;
        }
        else {
            $this->currentLocale = $this->getCurrentLocale();
        }
    }

    $this->app->setLocale($this->currentLocale);

    return $this->currentLocale;
  }

  public function getCurrentLocale() {
    if($this->currentLocale) {
        return $this->currentLocale;
    }

    return $this->defaultLocale;
  }

  public function hideDefaultLocaleInURL() {
    return config('localization.hideDefaultLocaleInURL');
  }

  public function getURL($locale = null, $url = null) {
    if ($locale === null) {
        $locale = $this->currentLocale;
    }

    if (!$this->getLanguageByCode($locale)) {
        throw new UnsupportedLocaleException('Locale \'' . $locale . '\' is not in the list of supported locales.');
    }

    if (empty($url)) {
        $url = $this->request->fullUrl();
    }

    $base_path = $this->request->getBaseUrl();
    $parsed_url = parse_url($url);
    $url_locale = $this->defaultLocale;

    if ( !$parsed_url || empty( $parsed_url['path'] ) ) {
      $path = $parsed_url[ 'path' ] = "";
    } else {
      $parsed_url['path'] = str_replace($base_path, '', '/' . ltrim($parsed_url[ 'path' ], '/'));
      $path = $parsed_url[ 'path' ];
      $languages = $this->getLanguages();

      foreach ($languages  as $language ) {
        $parsed_url['path'] = preg_replace('%^/?' . $language->code . '/%', '$1', $parsed_url[ 'path' ]);
        if ( $parsed_url[ 'path' ] !== $path ) {
          $url_locale = $language->code;
          break;
        }

        $parsed_url[ 'path' ] = preg_replace('%^/?' . $language->code . '$%', '$1', $parsed_url[ 'path' ]);

        if ( $parsed_url[ 'path' ] !== $path ) {
            $url_locale = $language->code;
            break;
        }
      }
    }

    $parsed_url[ 'path' ] = ltrim($parsed_url[ 'path' ], '/');

    if ( !empty( $locale ) && ( $locale != $this->defaultLocale || !$this->hideDefaultLocaleInURL())) {
        $parsed_url[ 'path' ] = $locale . '/' . ltrim($parsed_url[ 'path' ], '/');
    }
    $parsed_url[ 'path' ] = ltrim(ltrim($base_path, '/') . '/' . $parsed_url[ 'path' ], '/');

    if ( starts_with($path, '/') === true ) {
        $parsed_url[ 'path' ] = '/' . $parsed_url[ 'path' ];
    }

    $parsed_url['path'] = rtrim($parsed_url[ 'path' ], '/');

    $url = $this->unparseUrl($parsed_url);

    if ($this->checkUrl($url)) {
        return $url;
    }

    return $this->createUrlFromUri($url);
  }

  protected function checkUrl( $url ) {
    return filter_var($url, FILTER_VALIDATE_URL);
  }

  protected function unparseUrl($parsed_url) {
    if (empty($parsed_url)) {
        return "";
    }

    $url = "";
    $url .= isset( $parsed_url[ 'scheme' ] ) ? $parsed_url[ 'scheme' ] . '://' : '';
    $url .= isset( $parsed_url[ 'host' ] ) ? $parsed_url[ 'host' ] : '';
    $url .= isset( $parsed_url[ 'port' ] ) ? ':' . $parsed_url[ 'port' ] : '';
    $user = isset( $parsed_url[ 'user' ] ) ? $parsed_url[ 'user' ] : '';
    $pass = isset( $parsed_url[ 'pass' ] ) ? ':' . $parsed_url[ 'pass' ] : '';
    $url .= $user . ( ( $user || $pass ) ? "$pass@" : '' );

    if (!empty($url)) {
      $url .= isset( $parsed_url[ 'path' ] ) ? '/' . ltrim($parsed_url[ 'path' ], '/') : '';
    } else {
      $url .= isset( $parsed_url[ 'path' ] ) ? $parsed_url[ 'path' ] : '';
    }

    $url .= isset( $parsed_url[ 'query' ] ) ? '?' . $parsed_url[ 'query' ] : '';
    $url .= isset( $parsed_url[ 'fragment' ] ) ? '#' . $parsed_url[ 'fragment' ] : '';

    return $url;
  }

  public function createUrlFromUri($uri) {
    $uri = ltrim($uri, "/");

    if (empty($this->baseUrl)) {
      return app('url')->to($uri);
    }

    return $this->baseUrl . $uri;
  }

  /* Model */
  public function getLanguageByCode($code){
    $results = DB::table('language')->where('code', $code)->first();
  	if($results){
  		return true;
  	}

  	return false;
  }

  public function getLanguages(){
  	$results = DB::table('language')->get();
  	return $results->toArray();
  }
}
?>