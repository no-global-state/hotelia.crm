<?php

namespace Site\Service;

use Krystal\Db\Filter\InputDecorator;
use ArrayAccess;

final class LanguageService
{
    /**
     * Normalizes entity
     * 
     * @param mixed $entity
     * @return array
     */
    public static function normalizeEntity($entity)
    {
        if ($entity['id']) {
            $entity = $entity instanceof ArrayAccess ? $item[0] : $entity;
        }

        return $entity;
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

        $input = new InputDecorator();
        $input['id'] = isset($entity[0]) ? $entity[0]['id'] : null;

        return $input;
    }
}
