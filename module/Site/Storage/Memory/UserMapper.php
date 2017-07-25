<?php

namespace Site\Storage\Memory;

use Site\Storage\UserMapperInterface;

final class UserMapper implements UserMapperInterface
{
    /**
     * Virtual storage
     * 
     * @var array
     */
    private $users = array(
        array(
            'id' => '1',
            'role' => 'admin',
            'login' => 'admin',
            'password' => 'd033e22ae348aeb5660fc2140aec35850c4da997' // sha1 of 'admin'
        ),
        array(
            'id' => '2',
            'role' => 'user',
            'login' => 'user',
            'password' => '12dea96fec20593566ab75692c9949596833adc9' // sha1 of 'user'
        )
    );

    /**
     * Fetches by credentials
     * 
     * @param string $login
     * @param string $password
     * @return array
     */
    public function fetchByCredentials($login, $password)
    {
        $result = array();

        foreach ($this->users as $user) {
            if ($user['login'] === $login && $user['password'] === $password) {
                $result = array(
                    'id' => $user['id'],
                    'role' => $user['role']
                );
            }
        }

        return $result;
    }

    /**
     * Persist a user
     * 
     * @param array $data
     * @return boolean
     */
    public function persist(array $data)
    {
        // Pretend that we saved a user
        return true;
    }
}
