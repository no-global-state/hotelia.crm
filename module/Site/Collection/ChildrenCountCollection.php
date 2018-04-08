<?php

namespace Site\Collection;

use Krystal\Stdlib\ArrayCollection;

final class ChildrenCountCollection extends ArrayCollection
{
    /**
     * {@inheritDoc}
     */
    public function __construct()
    {
        $this->collection[] = 'Without children';

        for ($i = 1; $i < 11; $i++) {
            $this->collection[$i] = $i;
        }
    }
}
