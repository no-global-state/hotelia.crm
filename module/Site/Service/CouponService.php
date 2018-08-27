<?php

namespace Site\Service;

use Krystal\Http\PersistentStorageInterface;
use Site\Service\CouponAdapterInterface;
use RuntimeException;

final class CouponService
{
    const STORAGE_KEY = 'coupon';
    const STORAGE_VARS_KEY = 'coupon_params';

    /**
     * Session bag
     * 
     * @var \Krystal\Http\PersistentStorageInterface
     */
    private $sessionBag;

    /**
     * Any compliant coupon adapter
     * 
     * @var \Site\Service\CouponAdapterInterface
     */
    private $adapter;

    /**
     * State initialization
     * 
     * @param \Krystal\Http\PersistentStorageInterface $sessionBag
     * @param \Site\Service\CouponAdapterInterface $adapter
     * @return void
     */
    public function __construct(PersistentStorageInterface $sessionBag, CouponAdapterInterface $adapter)
    {
        $this->sessionBag = $sessionBag;
        $this->adapter = $adapter;
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
     * Discards a coupon
     * 
     * @return boolean
     */
    public function discardCoupon()
    {
        return $this->sessionBag->remove(self::STORAGE_KEY);
    }

    /**
     * Validates coupon code
     * 
     * @param array $params HTTP query params
     * @return array
     */
    public function apply(array $params) : array
    {
        $result = $this->adapter->query($params);

        if (!isset($result['active']) || !isset($result['response'])) {
            throw new RuntimeException('Implementation must return an array that contain both "active" and "response" keys');
        }

        if ($result['active'] === true) {
            $this->sessionBag->set(self::STORAGE_KEY, true);

            // Save parameters
            $this->sessionBag->set(self::STORAGE_VARS_KEY, $params);
        } else {
            $this->discardCoupon();
        }

        return $result['response'];
    }

    /**
     * Runs after order is complete
     * 
     * @return void
     */
    public function afterOrder()
    {
        $params = $this->sessionBag->get(self::STORAGE_VARS_KEY);

        if ($params) {
            return $this->adapter->after($params);
        }
    }
}
