<?php

namespace Site\Service;

use Site\Storage\MySQL\FacilityItemDataMapper;

final class FacilityItemDataService
{
    /**
     * Facility item data mapper
     * 
     * @var \Site\Storage\MySQL\FacilityItemDataMapper
     */
    private $facilityItemDataMapper;

    /**
     * State initialization
     * 
     * @param \Site\Storage\MySQL\FacilityItemDataMapper $facilityItemDataMapper
     * @return void
     */
    public function __construct(FacilityItemDataMapper $facilityItemDataMapper)
    {
        $this->facilityItemDataMapper = $facilityItemDataMapper;
    }

    /**
     * Fetch all item data
     * 
     * @param int $itemId
     * @param int $langId
     * @return array
     */
    public function fetchAll(int $itemId, int $langId) : array
    {
        return $this->facilityItemDataMapper->fetchAll($itemId, $langId);
    }
}
