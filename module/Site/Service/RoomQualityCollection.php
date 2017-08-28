<?php

namespace Site\Service;

use Krystal\Stdlib\ArrayCollection;

final class RoomQualityCollection extends ArrayCollection
{
    /**
     * {@inheritDoc}
     */
    protected $collection = array(
        '0' => 'Undefined',
        '1' => 'Perfect',
        '2' => 'Good',
        '3' => 'Medium',
        '4' => 'Bad',
        '5' => 'On repair'
    );
}
