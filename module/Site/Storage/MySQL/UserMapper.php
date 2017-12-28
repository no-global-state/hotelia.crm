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
     * Checks whether wizard is finished
     * 
     * @param int $userId
     * @return boolean
     */
    public function isWizardFinished(int $userId) : bool
    {
        return (bool) $this->db->select()
                               ->count('id')
                               ->from(self::getTableName())
                               ->whereEquals('id', $userId)
                               ->andWhereEquals('wizard_finished', 1)
                               ->queryScalar();
    }

    /**
     * Makes wizard as finished
     * 
     * @param int $userId
     * @return boolean
     */
    public function markWizardAsFinished(int $userId)
    {
        return $this->db->update(self::getTableName(), ['wizard_finished' => 1])
                        ->whereEquals('id', $userId)
                        ->execute();
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
