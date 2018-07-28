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
     * State initialization
     * 
     * @param string $ip
     * @return void
     */
    public function __construct(string $ip)
    {
        $this->ip = $ip;
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
     * Parse configuration array
     * 
     * @param \Krystal\Http\PersistentStorageInterface $session
     * @param array $configuration
     * @return array
     */
    public function findActive(PersistentStorageInterface $session = null, array $configuration) : array
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

        // Current country
        foreach ($configuration as $item) {
            // Make sure both codes are in uppercase
            if (strtoupper($item[$key]) == strtoupper($country)) {
                return $item;
            }
        }

        // Default on no match
        foreach ($configuration as $item) {
            if ($item[$key] == '*') {
                return $item;
            }
        }
    }
}
