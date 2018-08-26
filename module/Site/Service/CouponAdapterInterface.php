<?php

namespace Site\Service;

interface CouponAdapterInterface
{
    /**
     * Query remote server to validate discount code
     * 
     * @param string $serial
     * @return array
     */
    public function query(array $params);
}
