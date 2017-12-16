<?php

namespace Site\Storage\MySQL;

use Site\Storage\UserMapperInterface;

final class UserMapper extends AbstractMapper implements UserMapperInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_users');
    }

    /**
     * Fetches by credentials
     * 
     * @param string $login
     * @param string $password
     * @return array
     */
    public function fetchByCredentials($login, $password)
    {
        return $this->db->select('*')
                        ->from(self::getTableName())
                        ->whereEquals('email', $login)
                        ->andWhereEquals('password', $password)
                        ->query();
    }
}
