<?php

namespace Site\Service;

use Krystal\Http\Client\CurlHttplCrawler;
use Krystal\Http\PersistentStorageInterface;

/**
 * Based on client's IP it can return configuration values
 */
final class BehaviorService
{
    /**
     * Current IP
     * 
     * @var string
     */
    private $ip;

    /**
     * Session handler
     * 
     * @var \Krystal\Http\PersistentStorageInterface
     */
    private $session;

    /**
     * Configuration data
     * 
     * @var array
     */
    private $configuration;

    /**
     * State initialization
     * 
     * @param string $ip
     * @param \Krystal\Http\PersistentStorageInterface $session
     * @param array $configuration
     * @return void
     */
    public function __construct(string $ip, PersistentStorageInterface $session, array $configuration)
    {
        $this->ip = $ip;
        $this->session = $session;
        $this->configuration = $configuration;
    }

    /**
     * Returns defaults
     * 
     * @param array $languages Raw collection of languages to be filtered
     * @return array
     */
    public function getDefaults(array $languages) : array
    {
        if ($this->session->has('price_group_id')) {
            $active = $this->findLinear('price_group_id', $this->session->get('price_group_id'));
        } else {
            // Find active item
            $active = $this->findActiveByCurrentCountry();
        }
        
        // Filter and override languages
        $active['languages'] = self::getIncludedLanguages($languages, $active);
        $active['country'] = $this->session->get('country');

        return $active;
    }

    /**
     * Returns included languages
     * 
     * @param array $languages
     * @param array $active Active item
     * @return array
     */
    private static function getIncludedLanguages(array $languages, array $active) : array
    {
        // Shared generator
        $generator = function(array $constraints, bool $pluck) use ($languages){
            $output = [];

            foreach ($languages as $language) {
                // Language code
                $code = strtolower($language['code']);

                // Whether in collection
                $in = in_array($code, $constraints);
                
                if ($pluck) {
                    if ($in) {
                        $output[] = $language;
                    }
                } else {
                    if (!$in) {
                        $output[] = $language;
                    }
                }
            }

            return $output;
        };

        // Languages to be shown only
        if (isset($active['languages'])) {
            return $generator($active['languages'], true);
        }

        // Languages to be excluded
        if (isset($active['except'])) {
            return $generator($active['except'], false);
        }

        // Fatal error by default
    }

    /**
     * Find out information by IP
     * 
     * @return array
     */
    private function getInformationByIp() : array
    {
        // Build URL
        $url = sprintf('http://geoip.nekudo.com/api/%s', $this->ip);
        $response = (new CurlHttplCrawler())->get($url);

        if ($response !== false){
            return json_decode($response, true);
        } else {
            return [];
        }
    }

    /**
     * Returns country code
     * 
     * @return string
     */
    private function getCountryCode() : string
    {
        $data = $this->getInformationByIp();

        return $data['country']['code'] ?? '*';
    }

    /**
     * Performs linear search
     * 
     * @param string $key
     * @param string $value
     * @return array
     */
    private function findLinear($key, $value) : array
    {
        // Current value
        foreach ($this->configuration as $item) {
            // Make sure both codes are in uppercase
            if (strtoupper($item[$key]) == strtoupper($value)) {
                return $item;
            }
        }

        // Default on no match
        foreach ($this->configuration as $item) {
            if ($item[$key] == '*') {
                return $item;
            }
        }
    }

    /**
     * Parse configuration array
     * 
     * @return array
     */
    private function findActiveByCurrentCountry() : array
    {
        $key = 'country';

        $country = $this->session->getOnce($key, function(){
            return $this->getCountryCode();
        });

        return $this->findLinear($key, $country);
    }
}
