<?php

namespace Site\Helpers;

use Site\Service\DictionaryService;

final class ApiHelper
{
    /**
     * Returns price range
     * 
     * @param int $priceGroupId
     * @return array
     */
    public static function getPriceRanges(int $priceGroupId) : array
    {
        $collection = $_ENV['prices'];

        // If price group ID is defined
        if (isset($collection[$priceGroupId])) {
            // Current group
            $group = $collection[$priceGroupId];

            // Items count
            $count = count($group['items']);

            // Elements
            $first = reset($group['items']);
            $last = end($group['items']);

            // Values
            $min = $first['start'];
            $max = isset($last['end']) ? $last['end'] : $group['items'][($count - 1) - 1]['end'];

            return [
                'currency' => $group['currency'],
                'min' => [
                    'value' => $min,
                    'title' => number_format($min)
                ],
                'max' => [
                    'value' => $max,
                    'title' => number_format($max)
                ]
            ];
        }
    }

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