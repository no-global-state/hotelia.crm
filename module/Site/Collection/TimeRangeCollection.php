<?php

namespace Site\Collection;

use Krystal\Stdlib\ArrayCollection;

final class TimeRangeCollection extends ArrayCollection
{
    const PARAM_MONTH = 'month';
    const PARAM_WEEK = 'week';
    
    /**
     * {@inheritDoc}
     */
    protected $collection = array(
        self::PARAM_WEEK => 'Week',
        self::PARAM_MONTH => 'Month'
    );
}
