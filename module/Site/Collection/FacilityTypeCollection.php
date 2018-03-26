<?php

namespace Site\Collection;

use Krystal\Stdlib\ArrayCollection;

final class FacilityTypeCollection extends ArrayCollection
{
    const TYPE_FREE = '1';
    const TYPE_CHARGED = '0';
    const TYPE_YEAR = '2';

    /**
     * {@inheritDoc}
     */
    protected $collection = array(
        self::TYPE_FREE => 'Yes, free of charge', 
        self::TYPE_CHARGED => 'Yes, but not free',
        self::TYPE_YEAR => 'Works within a year'
    );
}
