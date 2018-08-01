<?php

namespace Site\Collection;

use Krystal\Stdlib\ArrayCollection;

final class DaysCollection extends ArrayCollection
{
    // Day constants
    const PARAM_WEEK_DAYS = 7;
    const PARAM_TWO_WEEK_DAYS = 14;
    const PARAM_THREE_WEEKS = 21;
    const PARAM_MONTH = 31;

    /**
     * {@inheritDoc}
     */
    protected $collection = [
        self::PARAM_WEEK_DAYS => 'Week',
        self::PARAM_TWO_WEEK_DAYS => 'Two weeks',
        self::PARAM_THREE_WEEKS => 'Three weeks',
        self::PARAM_MONTH => 'Month'
    ];
}
