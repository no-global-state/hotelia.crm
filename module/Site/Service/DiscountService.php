<?php

namespace Site\Service;

use Site\Storage\MySQL\DiscountMapper;
use Krystal\Stdlib\ArrayUtils;

final class DiscountService
{
    /**
     * Any compliant discount mapper
     * 
     * @var \Site\Storage\MySQL\DiscountMapper
     */
    private $discountMapper;

    /**
     * State initialization
     * 
     * @param \Site\Storage\MySQL\DiscountMapper $discountMapper
     * @return void
     */
    public function __construct(DiscountMapper $discountMapper)
    {
        $this->discountMapper = $discountMapper;
    }

    /**
     * Persists a discount
     * 
     * @param array $data
     * @return boolean
     */
    public function save($data) : bool
    {
        return $this->discountMapper->persist($data);
    }

    /**
     * Delete discount by its ID
     * 
     * @param int $id
     * @return boolean
     */
    public function deleteById(int $id) : bool
    {
        return $this->discountMapper->deleteByPk($id);
    }

    /**
     * Fetch a discount by its associated id
     * 
     * @param int $id
     * @return array
     */
    public function fetchById(int $id) : array
    {
        return $this->discountMapper->findByPk($id);
    }

    /**
     * Fetch discounts as a list
     * 
     * @param int $hotelId
     * @return array
     */
    public function fetchList(int $hotelId) : array
    {
        return ArrayUtils::arrayList($this->fetchAll($hotelId), 'percentage', 'name');
    }

    /**
     * Fetch all discounts
     * 
     * @param int $hotelId
     * @return array
     */
    public function fetchAll(int $hotelId)
    {
        return $this->discountMapper->fetchAll($hotelId);
    }
}
