<?php

namespace Site\Service;

final class WizardService
{
    /**
     * Create share localizations (one for all languages)
     * 
     * @param array $ids Language IDs
     * @param string $description
     * 
     * @return array
     */
    public static function createSharedLocalization(array $ids, string $description) : array
    {
        $output = [];

        foreach ($ids as $langId) {
            // Append description to given language ID
            $output[$langId] = [
                'lang_id' => $langId,
                'description' => $description
            ];
        }

        return $output;
    }

    /**
     * Parse input
     * 
     * @param array $rooms
     * @return array
     */
    public static function parseRawRooms(array $rooms) : array
    {
        $output = [];

        // Save price group IDs
        $priceGroupIds = $rooms[RoomTypeService::PARAM_PRICE_GROUP_IDS];

        // And free the main input from them
        unset($rooms[RoomTypeService::PARAM_PRICE_GROUP_IDS]);

        // Prepare output
        foreach ($rooms as $key => $params) {
            foreach ($params as $index => $value) {
                $output[$index][$key] = $value;
            }
        }

        $priceGroups = [];

        // Append prices field
        foreach ($output as $index => $data) {
            foreach ($priceGroupIds as $id => $prices) {
                foreach ($prices as $internalIndex => $price) {
                    if ($internalIndex == $index) {
                        $output[$index]['prices'][$id] = $price;
                    }
                }
            }
        }

        return $output;
    }
}
