<?php

namespace Site\Service;

use Krystal\Db\Filter\InputDecorator;

final class LanguageService
{
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
