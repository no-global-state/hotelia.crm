<?php

namespace Site\Service;

use Site\Storage\MySQL\HotelMapper;
use Site\Storage\MySQL\UserMapper;
use Krystal\Db\Filter\FilterableServiceInterface;

final class HotelService implements FilterableServiceInterface
{
    /**
     * Any compliant hotel mapper
     * 
     * @var \Site\Storage\MySQL\HotelMapper
     */
    private $hotelMapper;

    /**
     * Any compliant user mapper
     * 
     * @var \Site\Storage\MySQL\UserMapper
     */
    private $userMapper;

    /**
     * State initialization
     * 
     * @param \Site\Storage\MySQL\HotelMapper $hotelMapper
     * @param \Site\Storage\MySQL\UserMapper $userMapper
     * @return void
     */
    public function __construct(HotelMapper $hotelMapper, UserMapper $userMapper)
    {
        $this->hotelMapper = $hotelMapper;
        $this->userMapper = $userMapper;
    }

    /**
     * Find similar hotels excluding provided one
     * 
     * @param int $id Hotel ID to be excluded
     * @param int $langId Language ID filter
     * @param int $priceGroupId Active price group ID
     * @param int $regionId Region ID filter
     * @param int $limit Limit of hotels to be returned
     * @return array
     */
    public function findSimilar(int $id, int $langId, int $priceGroupId, int $regionId, int $limit = 5) : array
    {
        $rows = $this->hotelMapper->findSimilar($id, $langId, $priceGroupId, $regionId, $limit);
        
        // Append $rows
        foreach ($rows as &$row) {
            $row['cover'] = PhotoService::createImagePath($row['cover_id'], $row['cover'], PhotoService::PARAM_IMAGE_SIZE_SMALL);
        }

        return $rows;
    }

    /**
     * Finds hotel name by its associated ID
     * 
     * @param int $hotelId
     * @param int $langId
     * @return string
     */
    public function findNameById(int $hotelId, int $langId)
    {
        return $this->hotelMapper->findNameById($hotelId, $langId);
    }

    /**
     * Finds hotel discount by its associated ID
     * 
     * @param int $hotelId
     * @return int
     */
    public function findDiscountById(int $hotelId) : int
    {
        return (int) $this->hotelMapper->findColumnByPk($hotelId, 'discount');
    }

    /**
     * Finds hotel email by its associated ID
     * 
     * @param int $hotelId
     * @return mixed
     */
    public function findEmailById(int $hotelId)
    {
        return $this->hotelMapper->findEmailById($hotelId);
    }

    /**
     * Checks whether wizard is finished
     * 
     * @param int $hotelId Hotel Id
     * @return boolean
     */
    public function isWizardFinished(int $hotelId) : bool
    {
        return $this->hotelMapper->isWizardFinished($hotelId);
    }

    /**
     * Makes wizard as finished
     * 
     * @param int $hotelId Hotel Id
     * @return boolean
     */
    public function markWizardAsFinished(int $hotelId) : bool
    {
        return $this->hotelMapper->markWizardAsFinished($hotelId);
    }

    /**
     * Deletes a hotel by its ID
     * 
     * @param int $id Hotel ID
     * @return boolean
     */
    public function deleteById(int $id)
    {
        return $this->hotelMapper->deleteByPk($id);
    }

    /**
     * Registers a new hotel
     * Required keys: email, phone, name, login, password
     * 
     * @param array $data
     * @return boolean
     */
    public function register(array $data) : bool
    {
        if ($this->userMapper->loginExists($data['login'])) {
            return false;
        } else {
            // Insert basic data
            $this->hotelMapper->persist([
                'phone' => $data['phone'],
                'email' => $data['email'],
                'active' => 0
            ]);

            // Insert relational data for user
            $this->userMapper->persist([
                'email' => $data['email'],
                'name' => $data['name'],
                'role' => UserService::USER_ROLE_USER,
                'login' => $data['login'],
                'password' => sha1($data['password'])
            ]);

            // Insert relation
            $this->userMapper->insertRelation($this->userMapper->getMaxId(), $this->hotelMapper->getMaxId());

            return true;
        }
    }

    /**
     * Update settings
     * 
     * @param array $settings
     * @return boolean
     */
    public function updateSettings($settings) : bool
    {
        return $this->hotelMapper->updateSettings($settings);
    }

    /**
     * Returns pagination object
     * 
     * @return \Krystal\Paginate\Paginator
     */
    public function getPaginator()
    {
        return $this->hotelMapper->getPaginator();
    }

    /**
     * Fetch hotel by its ID
     * 
     * @param int $id Hotel ID
     * @param int $langId Language ID filter
     * @param int|null $priceGroupId Optional price group ID filter
     * @return array
     */
    public function fetchById(int $id, int $langId = 0, $priceGroupId = null)
    {
        $hotel = $this->hotelMapper->fetchById($id, $langId, $priceGroupId);

        if ($langId !== 0) {
            $hotel['cover'] = PhotoService::createImagePath($hotel['cover_id'], $hotel['cover'], PhotoService::PARAM_IMAGE_SIZE_LARGE);
        }

        return $hotel;
    }

    /**
     * {@inheritDoc}
     */
    public function filter($input, $page, $itemsPerPage, $sortingColumn, $desc, array $parameters = array())
    {
        return $this->hotelMapper->filter($input, $page, $itemsPerPage, $sortingColumn, $desc, $parameters);
    }

    /**
     * Finds all hotels
     * 
     * @param int $langId
     * @param int $priceGroupId
     * @param array $filters Optional filters
     * @param bool|string $sort Optional sorting column
     * @param mixed $limit Optional limit
     * @return array
     */
    public function findAll(int $langId, int $priceGroupId, array $filters = [], $sort = false, $limit = null) : array
    {
        $rows = $this->hotelMapper->findAll($langId, $priceGroupId, $filters, $sort, $limit);

        // Append $rows
        foreach ($rows as &$row) {
            $row['cover'] = PhotoService::createImagePath($row['cover_id'], $row['cover'], PhotoService::PARAM_IMAGE_SIZE_LARGE);
        }

        return $rows;
    }

    /**
     * Fetch all hotels
     * 
     * @param int $langId Language ID filter
     * @return array
     */
    public function fetchAll(int $langId) : array
    {
        return $this->hotelMapper->fetchAll($langId);
    }

    /**
     * Saves hotel
     * 
     * @param array $input
     * @return boolean
     */
    public function save(array $input)
    {
        return $this->hotelMapper->saveEntity($input['hotel'], $input['translation']);
    }
}
