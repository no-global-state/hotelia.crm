<?php

namespace Site\Collection;

use Krystal\Stdlib\ArrayCollection;

final class BookingStatusCollection extends ArrayCollection
{
    /* Status codes */
    const STATUS_NEW = 0;
    const STATUS_CONFIRMED = 1;
    const STATUS_REJECTED = 2;
    const STATUS_ACCEPTED = 3;

    /**
     * {@inheritDoc}
     */
    protected $collection = [
        self::STATUS_NEW => 'New',
        self::STATUS_CONFIRMED => 'Confirmed',
        self::STATUS_REJECTED => 'Rejected',
        self::STATUS_ACCEPTED => 'Accepted'
    ];
}
