<?php

namespace Site\Collection;

use Krystal\Stdlib\ArrayCollection;

final class DaysCollection extends ArrayCollection
{
    /**
     * {@inheritDoc}
     */
    protected $collection = array(
        '7' => 'Week',
        '14' => 'Two weeks',
        '21' => 'Three weeks',
        '31' => 'Month'
    );
}
