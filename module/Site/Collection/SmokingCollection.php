<?php

namespace Site\Collection;

use Krystal\Stdlib\ArrayCollection;

final class SmokingCollection extends ArrayCollection
{
    const PARAM_NON_SMOKING = 1;
    const PARAM_SMOKING = 2;
    const PARAM_BOTH = 3;

    /**
     * {@inheritDoc}
     */
    protected $collection = [
        self::PARAM_NON_SMOKING => 'Non-smoking',
        self::PARAM_SMOKING => 'Smoking',
        self::PARAM_BOTH => 'Smoking and non-smoking'
    ];
}
