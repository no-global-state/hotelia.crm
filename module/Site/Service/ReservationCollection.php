<?php

namespace Site\Service;

use Krystal\Stdlib\ArrayCollection;

final class ReservationCollection extends ArrayCollection
{
    /**
     * {@inheritDoc}
     */
    protected $collection = array(
        '0' => 'Undefined',
        '1' => 'Paid',
        '2' => 'Unpaid',
        '3' => 'Canceled'
    );
}
