<?php

namespace Site\Service;

use Krystal\Stdlib\ArrayCollection;

final class PaymentTypeCollection extends ArrayCollection
{
    /**
     * {@inheritDoc}
     */
    protected $collection = array(
        '0' => 'Undefined',
        '1' => 'Cash',
        '2' => 'Transfer',
        '3' => 'Terminal'
    );
}
