<?php

namespace Site\Service;

use Site\Storage\MySQL\HotelMapper;
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
     * State initialization
     * 
     * @param \Site\Storage\MySQL\HotelMapper $hotelMapper
     * @return void
     */
    public function __construct(HotelMapper $hotelMapper)
    {
        $this->hotelMapper = $hotelMapper;
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
