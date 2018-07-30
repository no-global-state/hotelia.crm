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
     * Inserts hotel relation
     * 
     * @param int $userId
     * @param int $hotelId
     * @return boolean
     */
    public function insertRelation(int $userId, int $hotelId) : bool
    {
        return $this->insertIntoJunction(HotelUserRelationMapper::getTableName(), $userId, [$hotelId]);
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
        // To be selected
        $columns = [
            self::column('id'),
            self::column('name'),
            self::column('email'),
            self::column('login'),
            self::column('role'),
            HotelUserRelationMapper::column('slave_id') => 'hotel_id',
        ];

        $db = $this->db->select($columns)
                        ->from(self::getTableName())
                        // Junction relation
                        ->leftJoin(HotelUserRelationMapper::getTableName(), [
                            HotelUserRelationMapper::column('master_id') => self::column('id')
                        ])
                        ->whereEquals('login', $login)
                        ->andWhereEquals('password', new RawBinding($password));

        return $db->query();
    }
}
