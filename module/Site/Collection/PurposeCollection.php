<?php

namespace Site\Collection;

use Krystal\Stdlib\ArrayCollection;

class PurposeCollection extends ArrayCollection
{
    /**
     * {@inheritDoc}
     */
    protected $collection = array(
        '0' => 'Undefined',
        '1' => 'Tourism',
        '2' => 'Business trip',
        '3' => 'Treatment',
        '4' => 'Recreation'
    );
}
