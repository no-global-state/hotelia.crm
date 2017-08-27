<?php

namespace Site\Service;

use Krystal\Stdlib\ArrayCollection;

final class CleaningCollection extends ArrayCollection
{
    /**
     * {@inheritDoc}
     */
    protected $collection = array(
        '0' => 'Undefined',
        '1' => 'Cleaned',
        '2' => 'Not cleaned',
        '3' => 'Checking'
    );
}
