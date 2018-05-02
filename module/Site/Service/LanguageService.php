<?php

namespace Site\Service;

use Site\Storage\MySQL\LanguageMapper;
use Krystal\Db\Filter\InputDecorator;
use ArrayAccess;

final class LanguageService
{
    /**
     * Any compliant language mapper
     * 
     * @var \Site\Storage\MySQL\LanguageMapper
     */
    private $languageMapper;

    /**
     * State initialization
     * 
     * @param \Site\Storage\MySQL\LanguageMapper $languageMapper
     * @return void
     */
    public function __construct(LanguageMapper $languageMapper)
    {
        $this->languageMapper = $languageMapper;
    }

    /**
     * Find IDs
     * 
     * @return array
     */
    public function findIds() : array
    {
        return $this->languageMapper->findIds();
    }

    /**
     * Finds language ID by its attached code
     * 
     * @param string $code Language code
     * @return string
     */
    public function findIdByCode(string $code)
    {
        return $this->languageMapper->findIdByCode($code);
    }

    /**
     * Checks whether language code exists
     * 
     * @param string $code
     * @return boolean
     */
    public function exists(string $code)
    {
        return $this->languageMapper->exists($code);
    }

    /**
     * Deletes by id
     * 
     * @param int $id Language ID
     * @return boolean
     */
    public function deleteById($id)
    {
        return $this->languageMapper->deleteByPk($id);
    }

    /**
     * Saves a language
     * 
     * @param array $input
     * @return boolean
     */
    public function save(array $input) : bool
    {
        return $this->languageMapper->persist($input);
    }

    /**
     * Fetches language
     * 
     * @param int $id
     * @return array
     */
    public function fetchById(int $id)
    {
        return $this->languageMapper->findByPk($id);
    }

    /**
     * Fetch all languages
     * 
     * @return array
     */
    public function fetchAll() : array
    {
        return $this->languageMapper->fetchAll();
    }

    /**
     * Normalizes entity
     * 
     * @param mixed $entity
     * @return array
     */
    public static function normalizeEntity($entity)
    {
        return $entity instanceof ArrayAccess ? $entity : $entity[0];
    }

    /**
     * Finds entity in collection by associated language ID
     * 
     * @param string $languageId
     * @param mixed $entity
     * @return mixed
     */
    public static function findByLangId($languageId, $entity)
    {
        if (empty($entity)) {
            return new InputDecorator;
        }

        // Find attached entity
        foreach ($entity as $translation) {
            if ($translation['lang_id'] == $languageId) {
                return $translation;
            }
        }

        // Default case
        $input = new InputDecorator();
        $input['id'] = isset($entity[0]) ? $entity[0]['id'] : null;

        // Merge missing keys
        foreach ($entity as $key => $value) {
            $input[$key] = $value;
        }

        return $input;
    }
}
