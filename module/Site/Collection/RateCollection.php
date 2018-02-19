<?php

namespace Site\Collection;

use Krystal\Stdlib\ArrayCollection;

final class RateCollection extends ArrayCollection
{
    /**
     * {@inheritDoc}
     */
    protected $collection = array(
        '1' => '1 ✯',
        '2' => '2 ✯✯',
        '3' => '3 ✯✯✯',
        '4' => '4 ✯✯✯✯',
        '5' => '5 ✯✯✯✯✯'
    );
}
