<?php

namespace Site\Service;

use Site\Storage\MySQL\RoomMapper;
use Site\Storage\MySQL\RoomTypeMapper;
use Site\Storage\MySQL\RoomTypeGalleryMapper;
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
     * @var \Site\Storage\MySQL\RoomMapper
     */
    private $roomMapper;

    /**
     * Any compliant room type mapper
     * 
     * @var \Site\Storage\MySQL\RoomTypeMapper
     */
    private $roomTypeMapper;

    /**
     * Room type gallery mapper
     * 
     * @var \Site\Storage\MySQL
     */
    private $roomTypeGalleryMapper;

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
     * @param \Site\Storage\MySQL\RoomTypeGalleryMapper $roomTypeGalleryMapper
     * @param \Site\Storage\MySQL\RoomMapper $roomMapper
     * @param \Site\Storage\MySQL\RoomTypePriceMapper $roomTypePriceMapper
     * @param \Site\Storage\MySQL\FacilitiyCategoryMapper $facilityCategoryMapper
     * @return void
     */
    public function __construct(
        RoomTypeMapper $roomTypeMapper, 
        RoomTypeGalleryMapper $roomTypeGalleryMapper, 
        RoomMapper $roomMapper, 
        RoomTypePriceMapper $roomTypePriceMapper, 
        FacilitiyCategoryMapper $facilityCategoryMapper
    ){
        $this->roomTypeMapper = $roomTypeMapper;
        $this->roomTypeGalleryMapper = $roomTypeGalleryMapper;
        $this->roomMapper = $roomMapper;
        $this->roomTypePriceMapper = $roomTypePriceMapper;
        $this->facilityCategoryMapper = $facilityCategoryMapper;
    }

    /**
     * Normalizes price grouped entity
     * 
     * @param mixed $type
     * @param array $priceGroups
     * @return array
     */
    public static function normalizeEntity($type, array $priceGroups) : array
    {
        if (is_object($type)) {
            return [1 => $priceGroups];
        } else {
            return ArrayUtils::arrayPartition($priceGroups, 'capacity');
        }
    }

    /**
     * Parses raw price group
     * 
     * @param int $roomTypeId Static ID to be applied for every key
     * @param array $raw Raw price group
     * @return array
     */
    public static function parseRawPriceGroup(int $roomTypeId, array $raw) : array
    {
        $output = [];

        foreach ($raw as $key => $value) {
            if ($key == 'group') {
                foreach ($value as $priceGroupId => $prices) {
                    foreach ($prices as $index => $price) {
                        $output[] = [
                            'room_type_id' => $roomTypeId,
                            'price_group_id' => $priceGroupId, 
                            'price' => $price,
                            'capacity' => $raw['capacity'][$index]
                        ];
                    }
                }
            }
        }

        return $output;
    }

    /**
     * Create from wizard
     * 
     * @param int $hotelId
     * @param array $langIds
     * @param array $rooms
     * @param string $roomLabel
     * @return void
     */
    public function createFromWizard(int $hotelId, array $langIds, array $input, string $roomLabel)
    {
        $rooms = WizardService::parseRawRooms($input);

        foreach ($rooms as $room) {
            $type = [
                'hotel_id' => $hotelId,
                'category_id' => $room['type'],
                'persons' => $room['persons'],
                'children' => $room['children']
            ];

            // Translations
            $translation = WizardService::createSharedLocalization($langIds, $room['description']);

            $this->roomTypeMapper->saveEntity($type, $translation);

            // Last type ID
            $typeId = $this->roomTypeMapper->getMaxId();

            // Save related prices
            $this->roomTypePriceMapper->save($typeId, $room['prices']);

            // Now insert rooms
            for ($i = 0; $i < $room['qty']; $i++) {
                // Save room
                $this->roomMapper->persist([
                    'type_id' => $typeId,
                    'hotel_id' => $hotelId,
                    'floor' => 0,
                    'persons' => 0,
                    'name' => $roomLabel . ' ' . (string) ($i + 1),
                    'square' => 0,
                    'cleaned' => 0,
                    'quality' => 0
                ]);
            }
        }
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

        // Do update if only provided
        if (isset($data['checked'])) {
            $ids = array_keys($data['checked']);

            foreach ($ids as $id) {
                // Special type
                $type = isset($data['type'][$id]) ? $data['type'][$id] : null;
                // Append prepared data
                $output[] = [$typeId, $id, $type];
            }
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
     * @param boolean $strict Whether to get all rows, including non-matching ones
     * @return array
     */
    public function findFacilities($typeId, int $langId, $categoryId = null, $front = false, bool $strict = false) : array
    {
        $categories = $this->facilityCategoryMapper->fetchAll($langId);

        foreach ($categories as &$category) {
            $category['items'] = $this->roomTypeMapper->findFacilities($typeId, $langId, $category['id'], false, $strict);
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
     * @param boolean $withFacilities
     * @return array
     */
    public function findAvailableTypes(string $arrival, string $departure, int $priceGroupId, int $langId, int $hotelId, $typeId = null, bool $withFacilities = false) : array
    {
        $rows = $this->roomTypeMapper->findAvailableTypes($arrival, $departure, $priceGroupId, $langId, $hotelId, $typeId);

        // Process cover attribute
        foreach ($rows as &$row) {
            if (empty($row['cover'])) {
                $row['cover'] = Module::PARAM_DEFAULT_IMAGE;
            } else {
                $row['cover'] = self::createImagePath($row['cover_id'], $row['cover'], PhotoService::PARAM_IMAGE_SIZE_LARGE);
            }

            if ($withFacilities === true) {
                $row['facilities'] = $this->findFacilities($row['id'], $langId, null, false, true);
            }

            // Find gallery
            $row['gallery'] = $this->createGallery($row['id']);

            // Find attached prices
            $row['prices'] = $this->findPricesByRoomTypeId($row['id'], $priceGroupId);
        }

        return $rows;
    }

    /**
     * Prepares and parses gallery
     * 
     * @param int $roomTypeId
     * @return array
     */
    private function createGallery(int $roomTypeId)
    {
        $rows = $this->roomTypeGalleryMapper->fetchAll($roomTypeId);

        $output = [];

        foreach ($rows as $row) {
            $output[] = self::createImagePath($row['id'], $row['file'], PhotoService::PARAM_IMAGE_SIZE_LARGE);
        }

        return $output;
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
     * @param mixed $priceGroupId Optional price group ID filter
     * @return array
     */
    public function findPricesByRoomTypeId(int $roomTypeId, $priceGroupId = null) : array
    {
        return $this->roomTypePriceMapper->findAllByRoomTypeId($roomTypeId, $priceGroupId);
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

        // Save here
        $this->roomTypePriceMapper->save($id, self::parseRawPriceGroup($id, $priceGroupIds));

        $facilities = isset($input['facility']) ? $input['facility'] : [];

        // Update facility relations
        return $this->updateRelation($id, $facilities);
    }
}
