<?php

namespace Site\Storage\MySQL;

final class PaymentSystemFieldDataMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_payment_systems_fields_data');
    }

    /**
     * {@inheritDoc}
     */
    protected function getPk()
    {
        return 'id';
    }
}
