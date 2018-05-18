<?php

namespace vdhoangson\Localization;
use \DB;

class Localization {
    /**
     * Laravel app instance
     *
     * @var \Illuminate\Foundation\Application 
     */
    private $app;

    /**
     * Config repository.
     *
     * @var \Illuminate\Config\Repository
     */
    private $config;

    /**
     * Illuminate request class.
     *
     * @var \Illuminate\Routing\Request
     */
    private $request;

    public $defaultLocale;
    public $currentLocale = false;

    private $session;

    /**
     * Session variable name
     *
     * @var string 
     */
    private $sessionKey = 'locale';

    /**
     * Cache
     *
     * @var array
     */
    protected $cache = [];

    public function __construct(){
        $this->app = app();
        $this->config = $this->app['config'];
        $this->request = $this->app['request'];
        $this->session = $this->app['session'];
        $this->cache = $this->app['cache'];
        $this->defaultLocale = config('cms.defaultLocale');
    }

    public function setLocale( $locale = null) {
        if($this->session->has($this->sessionKey)){
            $locale = $this->session->get($this->sessionKey);
        } elseif ( empty( $locale ) || !is_string($locale) ) {
            $locale = $this->request->segment(1);
        } else {
            $locale = $this->getDefaultLocale();
        }
        
        if ($this->getLanguageByCode($locale)) {
            $this->currentLocale = $locale;
        }
        
        $this->app->setLocale($this->currentLocale);
        $this->session->put($this->sessionKey, $locale);

        return $this->currentLocale;
    }

    /**
     * Return deault locale
     * 
     * @return string
     */
    public function getDefaultLocale(){
        return $this->defaultLocale;
    }

    /**
     * Return current locale
     * 
     * @return string
     */
    public function getCurrentLocale() {
        if($this->currentLocale) {
            return $this->currentLocale;
        }

        return $this->defaultLocale;
    }

    /* Model */
    public function getLanguageByCode($code){
        
        if($this->cache->has('languages')){
            $results = $this->cache->get('languages');
        } else {
            $results = $this->getLanguages();
        }

        foreach($results as $result){
            if($result->code === $code){
                return true;
            }
        }

        return false;
    }

    public function getLanguages(){
        $results = $this->cache->rememberForever('languages', function(){
            return DB::table('language')->get();
        }); 
    }
}
?>