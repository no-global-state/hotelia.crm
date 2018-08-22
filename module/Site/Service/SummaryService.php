<?php

namespace Site\Service;

use Krystal\Session\SessionBagInterface;
use Krystal\Text\Math;

final class SummaryService
{
    const PARAM_STORAGE_KEY = 'summary';

    /**
     * Any compliant session bag interface
     * 
     * @var \Krystal\Storage\SessionBagInterface
     */
    private $sessionBag;

    /**
     * State initialization
     * 
     * @param \Krystal\Storage\SessionBagInterface $sessionBag
     * @return void
     */
    public function __construct(SessionBagInterface $sessionBag)
    {
        $this->sessionBag = $sessionBag;
        $this->init();
    }

    /**
     * Inits the storage
     * 
     * @return void
     */
    private function init()
    {
        if (!$this->sessionBag->has(self::PARAM_STORAGE_KEY)) {
            $this->sessionBag->set(self::PARAM_STORAGE_KEY, []);
        }
    }

    /**
     * Returns data
     * 
     * @return array
     */
    public function getData()
    {
        return $this->sessionBag->get(self::PARAM_STORAGE_KEY);
    }

    /**
     * Returns formatted summary
     * 
     * @param mixed $discount Optional discount to be applied for the final price
     * @param \Site\Service\ExchangeService $exchangeService
     * @param boolean $foreigner
     * @return array
     */
    public function getFormattedSummary($discount = null, ExchangeService $exchangeService, bool $foreigner) : array
    {
        $summary = $this->getSummary($discount);

        // Keys to be formatted
        $keys = [
            'price', 
            'discount_price', 
            'saved_price'
        ];

        foreach ($keys as $key) {
            if (isset($summary[$key])) {
                $summary[$key] = $exchangeService->renderPrice($foreigner, $summary[$key]);
            }
        }

        return $summary;
    }

    /**
     * Returns summary data
     * 
     * @param mixed $discount Optional discount to be applied for the final price
     * @return array
     */
    public function getSummary($discount = null) : array
    {
        // Defaults
        $qty = 0;
        $price = 0;

        // Guest counter
        $guests = 0;

        foreach ($this->getData() as $target => $params) {
            foreach ($params as $index => $item) {
                $qty += $item['qty'];
                $price += $item['price'];
                $guests += $item['guests'];
            }
        }

        $output = [
            'qty' => $qty,
            'price' => $price,
            'guests' => $guests
        ];

        // Count if provided
        if ($discount !== null) {
            $output['discount_price'] = Math::getDiscount($price, $discount);
            $output['saved_price'] = $price - $output['discount_price'];
        }

        return $output;
    }

    /**
     * Clears the stack
     * 
     * @return void
     */
    public function clear()
    {
        // Refresh
        $this->sessionBag->set(self::PARAM_STORAGE_KEY, []);
    }

    /**
     * Removes from collection
     * 
     * @param int $id Price ID
     * @return void
     */
    public function remove($id)
    {
        // Updated array
        $output = [];

        // Remove inner item by attached Price ID
        foreach ($this->getData() as $target => $params) {
            foreach ($params as $index => $item) {
                if ($item['id'] != $id) {
                    $output[$target][] = $item;
                }
            }
        }

        // Refresh
        $this->sessionBag->set(self::PARAM_STORAGE_KEY, $output);
    }

    /**
     * Appends data
     * 
     * @param int $roomTypeId Room Type ID
     * @param int $id Price ID
     * @param int $qty Quantity of rooms
     * @param int $capacity Total capacity of the room
     * @param float $price Counted price
     * @return void
     */
    public function append(int $roomTypeId, int $id, int $qty, int $capacity, $price)
    {
        $params = $this->getData();

        if (!isset($params[$roomTypeId])) {
            $params[$roomTypeId] = [];
        }

        // Collision checking
        foreach ($params as $target => $collection) {
            foreach ($collection as $index => $item) {
                // Remove on collision
                if ($item['id'] == $id) {
                    unset($params[$target][$index]);
                }
            }
        }

        // Append
        $params[$roomTypeId][] = [
            'price' => $price,
            'qty' => $qty,
            'id' => $id,
            'price' => $price,
            'capacity' => $capacity,
            'guests' => $qty * $capacity
        ];

        // And refresh
        $this->sessionBag->set(self::PARAM_STORAGE_KEY, $params);
    }
}
