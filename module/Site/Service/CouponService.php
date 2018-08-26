<?php

namespace Site\Service;

use Krystal\Http\PersistentStorageInterface;
use Site\Service\CouponAdapterInterface;
use RuntimeException;

final class CouponService
{
    const STORAGE_KEY = 'coupon';

    /**
     * Session bag
     * 
     * @var \Krystal\Http\PersistentStorageInterface
     */
    private $sessionBag;

    /**
     * State initialization
     * 
     * @param \Krystal\Http\PersistentStorageInterface $sessionBag
     * @return void
     */
    public function __construct(PersistentStorageInterface $sessionBag)
    {
        $this->sessionBag = $sessionBag;
    }

    /**
     * Checks whether coupon has been applied
     * 
     * @return boolean
     */
    public function appliedCoupon() : bool
    {
        return $this->sessionBag->get(self::STORAGE_KEY) === true;
    }

    /**
     * Validates coupon code
     * 
     * @param array $params HTTP query params
     * @param \Site\Service\CouponAdapterInterface $implementation
     * @return array
     */
    public function apply(array $params, CouponAdapterInterface $implementation) : array
    {
        $result = $implementation->query($params);

        if (!isset($result['active']) || !isset($result['response'])) {
            throw new RuntimeException('Implementation must return an array that contain both "active" and "response" keys');
        }

        if ($result['active'] === true) {
            $this->sessionBag->set(self::STORAGE_KEY, true);
        }

        return $result['response'];
    }
}
