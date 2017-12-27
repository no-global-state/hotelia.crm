<?php

namespace Site\Service;

use Site\Storage\MySQL\PaymentSystemMapper;
use Krystal\Stdlib\ArrayUtils;

final class PaymentSystemService
{
    /**
     * Any compliant payment mapper
     * 
     * @var \Site\Storage\MySQL\PaymentSystemMapper
     */
    private $paymentSystemMapper;

    /**
     * State initialization
     * 
     * @param \Site\Storage\MySQL\PaymentSystemMapper $paymentSystemMapper
     * @return void
     */
    public function __construct(PaymentSystemMapper $paymentSystemMapper)
    {
        $this->paymentSystemMapper = $paymentSystemMapper;
    }

    /**
     * Saves payment system
     * 
     * @param array $input
     * @return boolean
     */
    public function save(array $input) : bool
    {
        return $this->paymentSystemMapper->persist($input);
    }

    /**
     * Delete payment system by its ID
     * 
     * @param int $id
     * @return boolean
     */
    public function deleteById($id) : bool
    {
        return $this->paymentSystemMapper->deleteByPk($id);
    }

    /**
     * Fetch payment system by its ID
     * 
     * @param int $id Payment system ID
     * @return array
     */
    public function fetchById(int $id) : array
    {
        return $this->paymentSystemMapper->findByPk($id);
    }

    /**
     * Fetch payment systems
     * 
     * @return array
     */
    public function fetchList() : array
    {
        return ArrayUtils::arrayList($this->fetchAll(), 'id', 'name');
    }

    /**
     * Fetch all payment systems
     * 
     * @return array
     */
    public function fetchAll() : array
    {
        return $this->paymentSystemMapper->fetchAll();
    }
}
