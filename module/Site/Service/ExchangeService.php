<?php

namespace Site\Service;

use Krystal\Session\SessionBagInterface;
use Krystal\Http\Client\CurlHttplCrawler;

final class ExchangeService
{
    const PARAM_CURRENCY = 'currency';
    const PARAM_CURRENCY_DATA = 'currency_data';
    const PARAM_DEFAULT_CURRENCY = 'USD';
    const PARAM_SERVICE_URL = 'http://exchange.local/';

    /**
     * Session bag service
     * 
     * @var \Krystal\Session\SessionBagInterface
     */
    private $sessionBag;

    /**
     * State initialization
     * 
     * @param \Krystal\Session\SessionBagInterface $sessionBag
     * @return void
     */
    public function __construct(SessionBagInterface $sessionBag)
    {
        $this->sessionBag = $sessionBag;
    }

    /**
     * Grab data from remote source
     * 
     * @return array
     */
    private function fetchData() : array
    {
        $response = (new CurlHttplCrawler)->get(self::PARAM_SERVICE_URL);
        return json_decode($response, true);
    }

    /**
     * Returns data only once
     * 
     * @return array
     */
    private function getData() : array
    {
        return $this->sessionBag->getOnce(self::PARAM_CURRENCY_DATA, function(){
            return self::fetchData();
        });
    }

    /**
     * Returns popular rates
     * 
     * @return array
     */
    public function getPopularRates() : array
    {
        return [
            'USD',
            'RUB',
            'EUR',
            'CNY',
            'JPY',
            'KRW',
            'TRY',
            'KZT',
            'TJS',
            'ILS',
            'INR',
            'CAD',
            'GBP',	
            'CHF',
            'DKK'
        ];
    }

    /**
     * Checks whether there's a stored currency in session
     * 
     * @return boolean
     */
    public function hasCurrency()
    {
        return $this->sessionBag->has(self::PARAM_CURRENCY);
    }

    /**
     * Returns current currency
     * 
     * @return string
     */
    public function getCurrency()
    {
        return $this->sessionBag->get(self::PARAM_CURRENCY, self::PARAM_DEFAULT_CURRENCY);
    }

    /**
     * Save currency
     * 
     * @param string $currency
     * @return boolean
     */
    public function saveCurrency(string $currency) : bool
    {
        $data = $this->getData();

        if (isset($data['rates'][$currency])) {
            $this->sessionBag->set(self::PARAM_CURRENCY, $currency);
            return true;

        } else {
            return false;
        }
    }

    /**
     * Calculates currency
     * 
     * @param mixed $value
     * @param boolean $rate Whether to append rate on final result
     * @return string
     */
    public function calculate($value, bool $rate = true)
    {
        $currency = $this->getCurrency();
        $data = $this->getData();

        if (isset($data['rates'][$currency])) {
            $result = number_format($data['rates'][$currency] * (float) $value);

            if ($rate === true) {
                return sprintf('%s %s', $result, $currency);
            } else {
                return $result;
            }

        } else {
            return false;
        }
    }
}