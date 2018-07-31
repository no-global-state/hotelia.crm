<?php

namespace Site\Collection;

use Krystal\Stdlib\ArrayCollection;

final class CardRequirmentCollection extends ArrayCollection
{
    /**
     * {@inheritDoc}
     */
    protected $collection = [
        '0' => 'No, card is not required for online booking',
        '1' => 'Yes, card is required for online booking'
    ];
}
