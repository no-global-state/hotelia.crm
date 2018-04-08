<?php

namespace Site\Service;

use Site\Storage\MySQL\RoomCategoryMapper;
use Krystal\Stdlib\ArrayUtils;

final class RoomCategoryService
{
    /**
     * Any compliant room category mapper
     * 
     * @var \Site\Storage\MySQL\RoomCategoryMapper
     */
    private $roomCategoryMapper;

    /**
     * State initialization
     * 
     * @param \Site\Storage\MySQL\RoomCategoryMapper $roomCategoryMapper
     * @return void
     */
    public function __construct(RoomCategoryMapper $roomCategoryMapper)
    {
        $this->roomCategoryMapper = $roomCategoryMapper;
    }

    /**
     * Delete room category by its ID
     * 
     * @param int $id
     * @return boolean
     */
    public function deleteById(int $id) : bool
    {
        return $this->roomCategoryMapper->deleteByPk($id);
    }

    /**
     * Saves room category
     * 
     * @param array $input
     * @return boolean
     */
    public function save(array $input)
    {
        return $this->roomCategoryMapper->saveEntity($input['category'], $input['translation']);
    }

    /**
     * Fetch all room categories
     * 
     * @param int $langId Language ID filter
     * @return array
     */
    public function fetchAll(int $langId) : array
    {
        return $this->roomCategoryMapper->fetchAll($langId);
    }

    /**
     * Fetch room category by its ID
     * 
     * @param int $id Region ID
     * @param int $langId Language ID filter
     * @return array
     */
    public function fetchById(int $id, int $langId = 0) : array
    {
        return $this->roomCategoryMapper->fetchById($id, $langId);
    }

    /**
     * Fetch all room categories
     * 
     * @param int $langId Language ID filter
     * @param int $hotelId Hotel ID filter
     * @param mixed $activeCategoryId Active category ID to be excluded
     * @return array
     */
    public function fetchFilteredList(int $langId, int $hotelId, $activeCategoryId) : array
    {
        $list = $this->fetchList($langId);
        $attached = $this->roomCategoryMapper->findAttachedIds($hotelId);

        if (in_array($activeCategoryId, $attached)) {
            // Delete by value
            $key = array_search($activeCategoryId, $attached);

            if (false !== $key) {
                unset($attached[$key]);
            }
        }

        return ArrayUtils::arrayWithout($list, $attached);
    }

    /**
     * Fetch all room categories
     * 
     * @param int $langId Language ID filter
     * @return array
     */
    public function fetchList(int $langId)
    {
        return ArrayUtils::arrayList($this->fetchAll($langId), 'id', 'name');
    }
}
