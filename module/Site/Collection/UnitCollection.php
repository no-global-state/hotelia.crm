<?php

namespace Site\Collection;

use Krystal\Stdlib\ArrayCollection;

final class UnitCollection extends ArrayCollection
{
    /**
     * {@inheritDoc}
     */
    protected $collection = array(
        '0' => 'Undefined',
        '1' => 'Day',
        '2' => 'Hour',
        '3' => 'Minute',
        '4' => 'One unit'
    );
}
