<?php

namespace Site\Service;

use Krystal\Stdlib\ArrayCollection;

final class CleaningCollection extends ArrayCollection
{
    const CODE_UNDEFINED = '0';
    const CODE_CLEANED = '1';
    const CODE_NONCLEANED = '2';
    const CODE_CHECKING = '3';

    /**
     * {@inheritDoc}
     */
    protected $collection = array(
        self::CODE_UNDEFINED => 'Undefined',
        self::CODE_CLEANED => 'Cleaned',
        self::CODE_NONCLEANED => 'Not cleaned',
        self::CODE_CHECKING => 'Checking'
    );
}
