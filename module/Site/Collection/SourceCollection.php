<?php

namespace Site\Collection;

use Krystal\Stdlib\ArrayCollection;

final class SourceCollection extends ArrayCollection
{
    /**
     * {@inheritDoc}
     */
    protected $collection = array(
        '0' => 'Undefined',
        '1' => 'Site',
        '2' => 'Reception'
    );
}
