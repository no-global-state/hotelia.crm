<?php

namespace Site\Collection;

use Krystal\Stdlib\ArrayCollection;

final class LegalStatusCollection extends ArrayCollection
{
    /**
     * {@inheritDoc}
     */
    protected $collection = array(
        '1' => 'Individual',
        '2' => 'Legal entity'
    );
}
