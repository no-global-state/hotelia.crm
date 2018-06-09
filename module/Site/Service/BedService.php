<?php

namespace Site\Service;

use Site\Storage\MySQL\RoomTypeBedMapper;

final class BedService
{
    /**
     * Any compliant room type bed mapper
     * 
     * @var \Site\Storage\MySQL\RoomTypeBedMapper
     */
    private $roomTypeBedMapper;

    /**
     * State initialization
     * 
     * @param \Site\Storage\MySQL\RoomTypeBedMapper $roomTypeBedMapper
     * @return void
     */
    public function __construct(RoomTypeBedMapper $roomTypeBedMapper)
    {
        $this->roomTypeBedMapper = $roomTypeBedMapper;
    }

    /**
     * Fetch all beds
     * 
     * @param int $languageId
     * @return array
     */
    public function fetchAll(int $languageId) : array
    {
        return $this->roomTypeBedMapper->fetchAll($languageId);
    }

    /**
     * Fetch bed by its ID
     * 
     * @param int $id Bed ID
     * @param int $langId Language ID filter
     * @return array
     */
    public function fetchById(int $id, int $langId = 0) : array
    {
        return $this->roomTypeBedMapper->fetchById($id, $langId);
    }

    /**
     * Delete a bed by its ID
     * 
     * @param int $id
     * @return boolean
     */
    public function deleteById(int $id) : bool
    {
        return $this->roomTypeBedMapper->deleteByPk($id);
    }

    /**
     * Saves a bed
     * 
     * @param array $input
     * @return boolean
     */
    public function save(array $input)
    {
        return $this->roomTypeBedMapper->saveEntity($input['bed'], $input['translation']);
    }
}
