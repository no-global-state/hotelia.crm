<?php

namespace Site\Collection;

use Krystal\Stdlib\ArrayCollection;

final class RoomQualityCollection extends ArrayCollection
{
    const STATUS_UNDEFINED = 0;
    const STATUS_PERFECT = 1;
    const STATUS_GOOD = 2;
    const STATUS_MEDIUM = 3;
    const STATUS_BAD = 4;
    const STATUS_ON_REPAIR = 5;

    /**
     * {@inheritDoc}
     */
    protected $collection = [
        self::STATUS_UNDEFINED => 'Undefined',
        self::STATUS_PERFECT => 'Perfect',
        self::STATUS_GOOD => 'Good',
        self::STATUS_MEDIUM => 'Medium',
        self::STATUS_BAD => 'Bad',
        self::STATUS_ON_REPAIR => 'On repair'
    ];
}
