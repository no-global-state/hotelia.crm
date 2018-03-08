<?php

namespace Site\Service;

use Site\Storage\MySQL\MealsMapper;

final class MealsService
{
    /**
     * Any compliant meals mapper
     * 
     * @var \Site\Storage\MySQL\MealsMapper
     */
    private $mealsMapper;

    /**
     * State initialization
     * 
     * @param \Site\Storage\MySQL\MealsMapper $mealsMapper
     * @return void
     */
    public function __construct(MealsMapper $mealsMapper)
    {
        $this->mealsMapper = $mealsMapper;
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
