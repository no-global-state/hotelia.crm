<?php

namespace Site\Service;

use Site\Storage\MySQL\RoomTypeMapper;
use Site\Storage\MySQL\PriceGroupMapper;
use Site\Storage\MySQL\RoomTypePriceMapper;
use Site\Storage\MySQL\FacilitiyCategoryMapper;
use Site\Module;
use Krystal\Stdlib\ArrayUtils;

final class RoomTypeService
{
    const PARAM_PRICE_GROUP_IDS = 'price_group_ids';

    /**
     * Any compliant room type mapper
     * 
     * @var \Site\Storage\MySQL\RoomTypeMapper
     */
    private $roomTypeMapper;

    /**
     * Room type price mapper
     * 
     * @var \Site\Storage\MySQL\RoomTypePriceMapper
     */
    private $roomTypePriceMapper;

    /**
     * Facility category mapper
     * 
     * @var \Site\Storage\MySQL\FacilitiyCategoryMapper
     */
    private $facilityCategoryMapper;

    /**
     * State initialization
     * 
     * @param \Site\Storage\MySQL\RoomTypeMapper $roomTypeMapper
     * @param \Site\Storage\MySQL\RoomTypePriceMapper $roomTypePriceMapper
     * @param \Site\Storage\MySQL\FacilitiyCategoryMapper $facilityCategoryMapper
     * @return void
     */
    public function __construct(RoomTypeMapper $roomTypeMapper, RoomTypePriceMapper $roomTypePriceMapper, FacilitiyCategoryMapper $facilityCategoryMapper)
    {
        $this->roomTypeMapper = $roomTypeMapper;
        $this->roomTypePriceMapper = $roomTypePriceMapper;
        $this->facilityCategoryMapper = $facilityCategoryMapper;
    }

    /**
     * Creates image path URL
     * 
     * @param mixed $id Image ID
     * @param mixed $file
     * @param string $size
     * @return string
     */
    public static function createImagePath($id, $file, string $size) : string
    {
        return sprintf('%s/%s/%s', Module::PARAM_ROOM_GALLERY_PATH . $id, $size, $file);
    }

    /**
     * Update relational data
     * 
     * @param int $hotelId
     * @param array $data
     * @return boolean
     */
    public function updateRelation(int $typeId, array $data)
    {
        $output = [];
        $ids = array_keys($data['checked']);

        foreach ($ids as $id) {
            // Special type
            $type = isset($data['type'][$id]) ? $data['type'][$id] : null;
            // Append prepared data
            $output[] = [$typeId, $id, $type];
        }

        return $this->roomTypeMapper->updateRelation($typeId, $output);
    }

    /**
     * Find all items attached to particular category
     * 
     * @param integer $hotelId typeId type ID
     * @param int $langId Language ID filter
     * @param integer $categoryId Optional category ID filter
     * @param bool $front Whether to fetch only front items
     * @return array
     */
    public function findFacilities($typeId, int $langId, $categoryId = null, $front = false) : array
    {
        $categories = $this->facilityCategoryMapper->fetchAll($langId);

        foreach ($categories as &$category) {
            $category['items'] = $this->roomTypeMapper->findFacilities($typeId, $langId, $category['id'], false);
        }

        return $categories;
    }

    /**
     * Find available room types based on dates
     * 
     * @param string $arrival
     * @param string $departure
     * @param int $priceGroupId Price group ID filter
     * @param int $langId
     * @param int $hotelId
     * @param mixed $typeId Optional type id filter
     * @return array
     */
    public function findAvailableTypes(string $arrival, string $departure, int $priceGroupId, int $langId, int $hotelId, $typeId = null) : array
    {
        $rows = $this->roomTypeMapper->findAvailableTypes($arrival, $departure, $priceGroupId, $langId, $hotelId, $typeId);

        // Process cover attribute
        foreach ($rows as &$row) {
            if (empty($row['cover'])) {
                $row['cover'] = Module::PARAM_DEFAULT_IMAGE;
            } else {
                $row['cover'] = self::createImagePath($row['cover_id'], $row['cover'], PhotoService::PARAM_IMAGE_SIZE_LARGE);
            }
        }

        return $rows;
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
        $collection = ArrayUtils::arrayPartition($this->roomTypePriceMapper->findAllPrices($hotelId), 'id');
        $output = [];

        foreach ($collection as $id => $items) {
            $output[$id] =  array_column($items, 'price', 'price_group_id');
        }

        return $output;
    }

    /**
     * Delete room type by its associated ID
     * 
     * @param int $id
     * @return boolean
     */
    public function deleteById(int $id)
    {
        return $this->roomTypeMapper->deleteByPk($id);
    }

    /**
     * Returns room types as a list
     * 
     * @param int $langId
     * @param int $hotelId
     * @return array
     */
    public function fetchList(int $langId, int $hotelId) : array
    {
        $rows = $this->roomTypeMapper->fetchAll($langId, $hotelId);
        $list = ArrayUtils::arrayList($rows, 'id', 'name');

        // Remove empty ones
        foreach ($list as $key => $value) {
            if (empty($value)) {
                unset($list[$key]);
            }
        }

        return $list;
    }

    /**
     * Fetch all room types
     * 
     * @param int $langId
     * @param int $hotelId
     * @return array
     */
    public function fetchAll(int $langId, int $hotelId) : array
    {
        $rows = $this->roomTypeMapper->fetchAll($langId, $hotelId);

        foreach ($rows as &$row) {
            $row['prices'] = $this->findPricesByRoomTypeId($row['id']);
        }

        return $rows;
    }

    /**
     * Find room by its type
     * 
     * @param int $typeId Room type Id
     * @param int $priceGroupId Optional price group ID filter
     * @param int $hotelId Hotel Id
     * @param int $langId Language Id filter
     * @return array
     */
    public function findByTypeId(int $typeId, int $priceGroupId, int $hotelId, int $langId)
    {
        $row = $this->roomTypeMapper->findByTypeId($typeId, $priceGroupId, $hotelId, $langId);

        if (isset($row['cover_id'], $row['cover'])) {
            $row['cover'] = self::createImagePath($row['cover_id'], $row['cover'], '850x450');
        }

        return $row;
    }

    /**
     * Finds room type info by its id
     * 
     * @param int $id Room type id
     * @param int $langId
     * @return mixed
     */
    public function findById(int $id, int $langId = 0)
    {
        return $this->roomTypeMapper->fetchById($id, $langId);
    }

    /**
     * Find data by room type ID
     * 
     * @param int $roomTypeId
     * @return array
     */
    public function findPricesByRoomTypeId(int $roomTypeId) : array
    {
        return $this->roomTypePriceMapper->findAllByRoomTypeId($roomTypeId);
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

        $this->roomTypeMapper->saveEntity($input['type'], $input['translation']);

        return $priceGroupIds;
    }

    /**
     * Saves room type
     * 
     * @param array $input
     * @return boolean
     */
    public function save(array $input)
    {
        $priceGroupIds = $this->persist($input);

        // Get ID
        $id = !empty($input['type']['id']) ? $input['type']['id'] : $this->roomTypeMapper->getMaxId();

        $this->roomTypePriceMapper->save($id, $priceGroupIds);

        $facilities = isset($input['facility']) ? $input['facility'] : [];

        // Update facility relations
        return $this->updateRelation($id, $facilities);
    }
}
