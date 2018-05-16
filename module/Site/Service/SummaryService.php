<?php

namespace Site\Service;

use Krystal\Session\SessionBagInterface;

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
     * @param float $price Counted price
     * @return void
     */
    public function append(int $roomTypeId, int $id, int $qty, $price)
    {
        $params = $this->getData();

        if (!isset($params[$roomTypeId])) {
            $params[$roomTypeId] = [];
        }

        // Append
        $params[$roomTypeId][] = [
            'price' => $price,
            'qty' => $qty,
            'id' => $id,
            'price' => $price
        ];

        // Refresh
        $this->sessionBag->set(self::PARAM_STORAGE_KEY, $params);
    }
}
