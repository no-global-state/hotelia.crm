<?php

namespace Site\Storage\MySQL;

use Krystal\Db\Sql\AbstractMapper;

final class ReservationMapper extends AbstractMapper
{
	/**
	 * {@inheritDoc}
	 */
	public static function getTableName()
	{
		return self::getWithPrefix('hotelia_reservation');
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getPk()
	{
		return 'id';
	}

	/**
	 * Fetch all records
	 * 
	 * @return array
	 */
	public function fetchAll()
	{
		$db = $this->db->select('*')
					   ->from(self::getTableName())
					   ->orderBy('id')
					   ->desc();

		return $db->queryAll();
	}
}
