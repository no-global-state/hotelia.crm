<?php

namespace Site\Storage\MySQL;

final class PaymentSystemMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_payment_systems');
    }

    /**
     * {@inheritDoc}
     */
    protected function getPk()
    {
        return 'id';
    }

    /**
     * Fetch all payment systems
     * 
     * @return array
     */
    public function fetchAll() : array
    {
        return $this->db->select('*')
                        ->from(self::getTableName())
                        ->orderBy($this->getPk())
                        ->desc()
                        ->queryAll();
    }
}
