<?php

namespace Site\Service;

use Krystal\Http\Client\CurlHttplCrawler;
use Krystal\Http\PersistentStorageInterface;
use Krystal\Stdlib\VirtualEntity;

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
     * Configuration data
     * 
     * @var array
     */
    private $configuration;

    /**
     * State initialization
     * 
     * @param string $ip
     * @param array $configuration
     * @return void
     */
    public function __construct(string $ip, array $configuration)
    {
        $this->ip = $ip;
        $this->configuration = $configuration;
    }

    /**
     * Returns defaults
     * 
     * @param \Krystal\Http\PersistentStorageInterface $session
     * @param array $languages Raw collection of languages to be filtered
     * @return \Krystal\Stdlib\VirtualEntity
     */
    public function getDefaults(PersistentStorageInterface $session = null, array $languages) : VirtualEntity
    {
        // Find active item
        $active = $this->findActive($session);

        // Filter languages
        $filteredLanguages = self::getIncludedLanguages($languages, $active);

        $entity = new VirtualEntity();
        $entity->setLanguages($filteredLanguages)
               ->setResident($active['resident'])
               ->setCountry($active['country'])
               ->setPriceGroupId($active['price_group_id']);

        return $entity;
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

        return json_decode($response, true);
    }

    /**
     * Returns country code
     * 
     * @return string
     */
    private function getCountryCode() : string
    {
        $data = $this->getInformationByIp();

        return $data['country']['code'];
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
     * @param \Krystal\Http\PersistentStorageInterface $session
     * @return array
     */
    private function findActive(PersistentStorageInterface $session = null) : array
    {
        $key = 'country';

        // This should be saved in session
        if ($session !== null) {
            $country = $session->getOnce($key, function(){
                return $this->getCountryCode();
            });
        } else {
            $country = $this->getCountryCode();
        }

        return $this->findLinear($key, $country);
    }
}
