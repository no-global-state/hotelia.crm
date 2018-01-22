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
                'hotel_id' => $this->hotelMapper->getMaxId(),
                'email' => $data['email'],
                'name' => $data['name'],
                'role' => UserService::USER_ROLE_USER,
                'login' => $data['login'],
                'password' => sha1($data['password'])
            ]);

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
     * @return array
     */
    public function fetchById(int $id, int $langId = 0) : array
    {
        return $this->hotelMapper->fetchById($id, $langId);
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
     * @return array
     */
    public function findAll(int $langId, int $priceGroupId, array $filters = []) : array
    {
        $rows = $this->hotelMapper->findAll($langId, $priceGroupId, $filters);

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
