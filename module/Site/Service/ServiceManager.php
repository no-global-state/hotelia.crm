<?php

namespace Site\Service;

use Site\Storage\MySQL\ServiceMapper;
use Site\Storage\MySQL\ServicePriceMapper;
use Krystal\Stdlib\ArrayUtils;

final class ServiceManager
{
    const PARAM_PRICE_GROUP_IDS = 'price_group_ids';

    /**
     * Any compliant service mapper
     * 
     * @var \Site\Storage\MySQL\ServiceMapper
     */
    private $serviceMapper;

    /**
     * Any compliant service price mapper
     * 
     * @var \Site\Storage\MySQL\ServicePriceMapper
     */
    private $servicePriceMapper;

    /**
     * State initialization
     * 
     * @param \Site\Storage\MySQL\ServiceMapper $serviceMapper
     * @param \Site\Storage\MySQL\ServicePriceMapper $servicePriceMapper
     * @return void
     */
    public function __construct(ServiceMapper $serviceMapper, ServicePriceMapper $servicePriceMapper)
    {
        $this->serviceMapper = $serviceMapper;
        $this->servicePriceMapper = $servicePriceMapper;
    }

    /**
     * Find data by room type ID
     * 
     * @param int $serviceId
     * @return array
     */
    public function findPricesServiceId(int $serviceId) : array
    {
        return $this->servicePriceMapper->findAllByServiceId($serviceId);
    }

    /**
     * Fetch all room prices
     * 
     * @param int $hotelId
     * @return array
     */
    public function findAllPrices(int $hotelId) : array
    {
        // Turn raw result-set into collection
        $collection = ArrayUtils::arrayPartition($this->servicePriceMapper->findAllPrices($hotelId), 'id');
        $output = [];

        foreach ($collection as $id => $items) {
            $output[$id] =  array_column($items, 'price', 'price_group_id');
        }

        return $output;
    }

    /**
     * Saves room type service
     * 
     * @param array $input
     * @return array
     */
    private function persist(array $input) : array
    {
        // Keep them
        $priceGroupIds = $input[self::PARAM_PRICE_GROUP_IDS];

        // No need to insert IDs
        unset($input[self::PARAM_PRICE_GROUP_IDS]);

        $this->serviceMapper->persist($input);

        return $priceGroupIds;
    }

    /**
     * Saves a service
     * 
     * @param array $input
     * @return boolean
     */
    public function save(array $input) : bool
    {
        $priceGroupIds = $this->persist($input);

        // Id depending on insert & update
        $id = $input['id'] ? $input['id'] : $this->serviceMapper->getMaxId();

        return $this->servicePriceMapper->save($id, $priceGroupIds);
    }

    /**
     * Delete a service by its ID
     * 
     * @param int $id
     * @return boolean
     */
    public function deleteById(int $id)
    {
        return $this->serviceMapper->deleteByPk($id);
    }

    /**
     * Fetch service by its id
     * 
     * @param int $id
     * @return array
     */
    public function fetchById(int $id)
    {
        return $this->serviceMapper->findByPk($id);
    }

    /**
     * Fetch all services as a list
     * 
     * @param int $hotelId
     * @return array
     */
    public function fetchList(int $hotelId) : array
    {
        return ArrayUtils::arrayList($this->fetchAll($hotelId), 'id', 'name');
    }

    /**
     * Fetch all services
     * 
     * @param int $hotelId
     * @return array
     */
    public function fetchAll(int $hotelId) : array
    {
        $rows = $this->serviceMapper->fetchAll($hotelId);

        foreach ($rows as &$row) {
            $row['prices'] = $this->findPricesServiceId($row['id']);
        }

        return $rows;
    }
}
