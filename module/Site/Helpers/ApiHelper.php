<?php

namespace Site\Helpers;

use Site\Service\DictionaryService;

final class ApiHelper
{
    /**
     * Create star rates
     * 
     * @param \Site\Service\DictionaryService $dictionaryService
     * @param int $languageId
     * @return array
     */
    public static function createStarRates(DictionaryService $dictionaryService, int $languageId) : array
    {
        return [
            [
                'name' => $dictionaryService->findByAlias('RATE_RAW_NO_STARS', $languageId),
                'value' => ''
            ],

            [
                'name' => $dictionaryService->findByAlias('RATE_RAW_ONE_STAR', $languageId),
                'value' => 1
            ],

            [
                'name' => $dictionaryService->findByAlias('RATE_RAW_TWO_STARS', $languageId),
                'value' => 2
            ],

            [
                'name' => $dictionaryService->findByAlias('RATE_RAW_THREE_STARS', $languageId),
                'value' => 3
            ],

            [
                'name' => $dictionaryService->findByAlias('RATE_RAW_FOUR_STARS', $languageId),
                'value' => 4
            ],

            [
                'name' => $dictionaryService->findByAlias('RATE_RAW_FIVE_STARS', $languageId),
                'value' => 5
            ]
        ];
    }
}