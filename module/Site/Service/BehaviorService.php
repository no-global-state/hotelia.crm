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
    public function findActive(PersistentStorageInterface $session = null) : array
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
