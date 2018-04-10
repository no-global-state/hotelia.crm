<?php

namespace Site\Storage\MySQL;

final class PaymentSystemFieldMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_payment_systems_fields');
    }

    /**
     * {@inheritDoc}
     */
    protected function getPk()
    {
        return 'id';
    }

    /**
     * Find all fields by associated payment system ID
     * 
     * @param int $paymentSystemId
     * @return array
     */
    public function findAllByPaymentSystemId(int $paymentSystemId) : array
    {
        // Columns to be selected
        $columns = [
            self::column('id'),
            self::column('payment_system_id'),
            self::column('order'),
            self::column('name'),
            PaymentSystemMapper::column('name') => 'system'
        ];

        return $this->db->select($columns)
                        ->from(self::getTableName())
                        ->leftJoin(PaymentSystemMapper::getTableName(), [
                            PaymentSystemMapper::column('id') => self::getRawColumn('payment_system_id')
                        ])
                        ->whereEquals('payment_system_id', $paymentSystemId)
                        ->queryAll();
    }
}
