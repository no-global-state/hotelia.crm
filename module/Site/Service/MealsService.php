<?php

namespace Site\Service;

use Site\Storage\MySQL\MealsMapper;
use Site\Storage\MySQL\MealsGlobalPriceMapper;

final class MealsService
{
    /**
     * Any compliant meals mapper
     * 
     * @var \Site\Storage\MySQL\MealsMapper
     */
    private $mealsMapper;

    /**
     * Any compliant global price mapper
     * 
     * @var \Site\Storage\MySQL\MealsGlobalPriceMapper
     */
    private $mealsGlobalPriceMapper;

    /**
     * State initialization
     * 
     * @param \Site\Storage\MySQL\MealsMapper $mealsMapper
     * @param \Site\Storage\MySQL\MealsGlobalPriceMapper
     * @return void
     */
    public function __construct(MealsMapper $mealsMapper, MealsGlobalPriceMapper $mealsGlobalPriceMapper)
    {
        $this->mealsMapper = $mealsMapper;
        $this->mealsGlobalPriceMapper = $mealsGlobalPriceMapper;
    }

    /**
     * Find global prices by hotel ID
     * 
     * @param int $hotelId
     * @return array
     */
    public function findGlobalPrices(int $hotelId)
    {
        return $this->mealsGlobalPriceMapper->findByHotelId($hotelId);
    }

    /**
     * Update global price
     * 
     * @param int $hotelId
     * @param array $input
     * @return boolean
     */
    public function updateGlobalPrice(int $hotelId, array $input)
    {
        $data = [];

        // Process and prepare
        foreach ($input['price'] as $priceGroupId => $price) {
            $data[] = [$hotelId, $priceGroupId, $price];
        }

        return $this->mealsGlobalPriceMapper->updateRelation($hotelId, $data);
    }

    /**
     * Update relation with hotel ID
     * 
     * @param int $hotelId
     * @param array $mealIds
     * @return boolean
     */
    public function updateRelation(int $hotelId, array $relations) : bool
    {
        $mealIds = isset($relations['checked']) ? array_keys($relations['checked']) : [];

        return $this->mealsMapper->updateRelation($hotelId, $mealIds);
    }

    /**
     * Save meals
     * 
     * @param array $input
     * @return boolean
     */
    public function save(array $input)
    {
        return $this->mealsMapper->saveEntity($input['meal'], $input['translation']);
    }

    /**
     * Delete entity by its id
     * 
     * @param int $id
     * @return boolean
     */
    public function deleteById(int $id)
    {
        return $this->mealsMapper->deleteByPk($id);
    }

    /**
     * Fetch meal by its ID
     * 
     * @param int $id Meal ID
     * @param int $langId Language ID filter
     * @return array
     */
    public function fetchById(int $id, int $langId = 0)
    {
        return $this->mealsMapper->fetchById($id, $langId);
    }

    /**
     * Fetch all meals
     * 
     * @param int $langId Language ID filter
     * @param mixed $hotelId
     * @return array
     */
    public function fetchAll(int $langId, $hotelId = null) : array
    {
        return $this->mealsMapper->fetchAll($langId, $hotelId);
    }
}
