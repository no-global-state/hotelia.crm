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
     * Finds payment system name by its ID
     * 
     * @param int $id Payment system ID
     * @return string
     */
    public function findNameById(int $id) : string
    {
        return $this->findColumnByPk($id, 'name');
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
