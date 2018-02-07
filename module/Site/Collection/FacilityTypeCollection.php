<?php

namespace Site\Collection;

use Krystal\Stdlib\ArrayCollection;

final class FacilityTypeCollection extends ArrayCollection
{
    /**
     * {@inheritDoc}
     */
    protected $collection = array(
        '1' => 'Yes, free of charge', 
        '0' => 'Yes, but not free'
    );
}
