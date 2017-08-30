<?php

namespace Site\Service;

use Krystal\Stdlib\ArrayCollection;

class StatusCollection extends ArrayCollection
{
    /**
     * {@inheritDoc}
     */
    protected $collection = array(
        '0' => 'Undefined',
        '1' => 'Regular',
        '2' => 'VIP'
    );
}
