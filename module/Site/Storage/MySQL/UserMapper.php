<?php

namespace Site\Storage\MySQL;

use Site\Storage\UserMapperInterface;
use Krystal\Db\Sql\RawBinding;

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
     * {@inheritDoc}
     */
    protected function getPk()
    {
        return 'id';
    }

    /**
     * Checks whether login exists
     * 
     * @param string $login
     * @return boolean
     */
    public function loginExists(string $login) : bool
    {
        return $this->valueExists('login', $login);
    }

    /**
     * Updates user password by it associated it
     * 
     * @param int $id User id
     * @param string $password
     * @return boolean
     */
    public function updatePasswordById(int $id, string $password)
    {
        return $this->persist([
            'id' => $id,
            'password' => $password
        ]);
    }

    /**
     * Fetches by credentials
     * 
     * @param string $login
     * @param string $password
     * @return array
     */
    public function fetchByCredentials(string $login, string $password)
    {
        return $this->db->select('*')
                        ->from(self::getTableName())
                        ->whereEquals('login', $login)
                        ->andWhereEquals('password', new RawBinding($password))
                        ->query();
    }
}
