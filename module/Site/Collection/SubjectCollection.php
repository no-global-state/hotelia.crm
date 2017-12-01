<?php

namespace Site\Collection;

use Krystal\Stdlib\ArrayCollection;

final class SubjectCollection extends ArrayCollection
{
    /**
     * {@inheritDoc}
     */
    protected $collection = array(
        '1' => 'Problem',
        '2' => 'Suggestion',
        '3' => 'Claim'
    );
}
