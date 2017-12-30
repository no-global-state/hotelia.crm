<?php

namespace Site\Service;

use Site\Storage\MySQL\ServiceMapper;
use Krystal\Stdlib\ArrayUtils;

final class ServiceManager
{
    /**
     * Any compliant service mapper
     * 
     * @var \Site\Storage\MySQL\ServiceMapper
     */
    private $serviceMapper;

    /**
     * State initialization
     * 
     * @param \Site\Storage\MySQL\ServiceMapper $serviceMapper
     * @return void
     */
    public function __construct(ServiceMapper $serviceMapper)
    {
        $this->serviceMapper = $serviceMapper;
    }

    /**
     * Saves a service
     * 
     * @param array $input
     * @return boolean
     */
    public function save(array $input) : bool
    {
        return $this->serviceMapper->persist($input);
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
        return $this->serviceMapper->fetchAll($hotelId);
    }
}
