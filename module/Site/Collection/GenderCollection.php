<?php

namespace Site\Collection;

use Krystal\Stdlib\ArrayCollection;

final class GenderCollection extends ArrayCollection
{
    /**
     * {@inheritDoc}
     */
    protected $collection = array(
        'M' => 'Male',
        'F' => 'Female'
    );
}
