<?php

namespace Site\Service;

use Site\Storage\MySQL\PaymentSystemFieldDataMapper;
use Site\Storage\MySQL\PaymentSystemFieldMapper;

final class PaymentFieldService
{
    /**
     * Payment system field data mapper
     * 
     * @var \Site\Storage\MySQL\PaymentSystemFieldDataMapper
     */
    private $paymentSystemFieldDataMapper;

    /**
     * Payment system field mapper
     * 
     * @var \Site\Storage\MySQL\PaymentSystemFieldMapper
     */
    private $paymentSystemFieldMapper;

    /**
     * State initialization
     * 
     * @param $paymentSystemFieldMapper \Site\Storage\MySQL\PaymentSystemFieldMapper
     * @param $paymentSystemFieldDataMapper \Site\Storage\MySQL\PaymentSystemFieldDataMapper
     * @return void
     */
    public function __construct(PaymentSystemFieldMapper $paymentSystemFieldMapper, PaymentSystemFieldDataMapper $paymentSystemFieldDataMapper)
    {
        $this->paymentSystemFieldMapper = $paymentSystemFieldMapper;
        $this->paymentSystemFieldDataMapper = $paymentSystemFieldDataMapper;
    }

    /**
     * Find all fields by associated payment system ID
     * 
     * @param int $paymentSystemId
     * @return array
     */
    public function findAllByPaymentSystemId(int $paymentSystemId) : array
    {
        return $this->paymentSystemFieldMapper->findAllByPaymentSystemId($paymentSystemId);
    }

    /**
     * Fetch payment field by its id
     * 
     * @param int $id Payment field id
     * @return array
     */
    public function fetchById(int $id) : array
    {
        return $this->paymentSystemFieldMapper->findByPk($id);
    }

    /**
     * Saves a payment field
     * 
     * @param array $field
     * @return boolean
     */
    public function save(array $field)
    {
        return $this->paymentSystemFieldMapper->persist($field);
    }

    /**
     * Deletes payment field
     * 
     * @param int $id Payment field id
     * @return boolean
     */
    public function deleteById(int $id)
    {
        return $this->paymentSystemFieldMapper->deleteByPk($id);
    }
}
