<?php

namespace Site\Service;

use Site\Storage\MySQL\PriceGroupMapper;
use Krystal\Stdlib\ArrayUtils;

final class PriceGroupService
{
    /**
     * Any compliant
     * 
     * @var \Site\Storage\MySQL\PriceGroupMapper
     */
    private $priceGroupMapper;

    /**
     * State initialization
     * 
     * @param \Site\Storage\MySQL\PriceGroupMapper $priceGroupMapper
     * @return void
     */
    public function __construct(PriceGroupMapper $priceGroupMapper)
    {
        $this->priceGroupMapper = $priceGroupMapper;
    }

    /**
     * Saves price group
     * 
     * @param array $input
     * @return boolean
     */
    public function save(array $input)
    {
        return $this->priceGroupMapper->persist($input);
    }

    /**
     * Deletes price group by its ID
     * 
     * @param int $id
     * @return boolean
     */
    public function deleteById(int $id) : bool
    {
        return $this->priceGroupMapper->deleteByPk($id);
    }

    /**
     * Fetches price group by its associated id
     * 
     * @param int $id
     * @return mixed
     */
    public function fetchById(int $id)
    {
        return $this->priceGroupMapper->findByPk($id);
    }

    /**
     * Fetch all price groups
     * 
     * @param boolean $sort Whether to sort
     * @return array
     */
    public function fetchAll($sort = true) : array
    {
        return $this->priceGroupMapper->fetchAll($sort);
    }

    /**
     * Fetch populated data
     * 
     * @param array $data
     * @return array
     */
    public function fetchPopulated(array $data) : array
    {
        $priceGroups = $this->fetchAll(false);

        // Populate
        $priceGroups = array_replace_recursive($priceGroups, $data);

        return $priceGroups;
    }

    /**
     * Fetches as a list
     * 
     * @return array
     */
    public function fetchList() : array
    {
        return ArrayUtils::arrayList($this->fetchAll());
    }
}
