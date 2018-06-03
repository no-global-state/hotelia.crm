<?php

namespace Site\Service;

final class PriceFilterService
{
    /**
     * Current collection
     * 
     * @var array
     */
    private $collection = array();

    /**
     * State initialization
     * 
     * @param array $collection
     * @return void
     */
    public function __construct(array $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Formats price
     * 
     * @param string $price
     * @param string $currency
     * @return string
     */
    private function formatPrice($price, string $currency) : string
    {
        if ($price == null) {
            return sprintf('∞ %s', $currency);
        } else {
            return sprintf('%s %s', number_format($price), $currency);
        }
    }

    /**
     * Create and render in readable format
     * 
     * @return array
     */
    public function createReadable() : array
    {
        $output = [];

        foreach ($this->collection as $priceGroupId => $collection) {
            foreach ($collection['items'] as &$item) {

                $item['start'] = $this->formatPrice($item['start'], $collection['currency']);
                $item['end'] = $this->formatPrice($item['end'], $collection['currency']);
            }

            $output[$priceGroupId] = $collection['items'];
        }

        return $output;
    }

    /**
     * Finds price range by constants
     * 
     * @param int $id
     * @param array $constants
     * @return array
     */
    public function findByConstants($id, array $constants) : array
    {
        $output = [];

        foreach ($this->collection as $priceGroupId => $collection) {
            // Items
            $items =& $collection['items'];

            foreach ($items as $item) {
                if (in_array($item['const'], $constants) && $priceGroupId == $id) {
                    $output[] = $item;
                }
            }
        }

        return $output;
    }
}
