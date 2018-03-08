<?php

namespace Site\Collection;

use Krystal\Stdlib\ArrayCollection;

final class BreakfastCollection extends ArrayCollection
{
    /**
     * {@inheritDoc}
     */
    protected $collection = array(
        '1' => 'Yes, its included in the reservation price',
        '2' => 'Yes, but for extra charge',
        '3' => 'No'
    );
}
