<?php

namespace Site\Service;

use Site\Storage\MySQL\DiscountMapper;
use Krystal\Stdlib\ArrayUtils;
use Krystal\I18n\TranslatorInterface;

final class DiscountService
{
    /**
     * Any compliant discount mapper
     * 
     * @var \Site\Storage\MySQL\DiscountMapper
     */
    private $discountMapper;

    /**
     * State initialization
     * 
     * @param \Site\Storage\MySQL\DiscountMapper $discountMapper
     * @return void
     */
    public function __construct(DiscountMapper $discountMapper)
    {
        $this->discountMapper = $discountMapper;
    }

    /**
     * Create discounts
     * 
     * @param int $hotelId
     * @param \Krystal\I18n\TranslatorInterface $translator
     * @return array
     */
    public function createDiscounts(int $hotelId, TranslatorInterface $translator) : array
    {
        // Grab all available discounts by current hotel ID
        $discounts = $this->fetchList($hotelId);

        $defaults = $translator->translateArray([
            '0' => $translator->translate('No discount'),
            '' => $translator->translate('Type manually')
        ]);

        $output = [
            $translator->translate('Defaults') => $defaults,
        ];

        // Append discounts to output if provided
        if (!empty($discounts)) {
            $output[$translator->translate('Discounts')] = $discounts;
        }

        return $output;
    }

    /**
     * Persists a discount
     * 
     * @param array $data
     * @return boolean
     */
    public function save($data) : bool
    {
        return $this->discountMapper->persist($data);
    }

    /**
     * Delete discount by its ID
     * 
     * @param int $id
     * @return boolean
     */
    public function deleteById(int $id) : bool
    {
        return $this->discountMapper->deleteByPk($id);
    }

    /**
     * Fetch a discount by its associated id
     * 
     * @param int $id
     * @return array
     */
    public function fetchById(int $id) : array
    {
        return $this->discountMapper->findByPk($id);
    }

    /**
     * Fetch discounts as a list
     * 
     * @param int $hotelId
     * @return array
     */
    public function fetchList(int $hotelId) : array
    {
        return ArrayUtils::arrayList($this->fetchAll($hotelId), 'percentage', 'name');
    }

    /**
     * Fetch all discounts
     * 
     * @param int $hotelId
     * @return array
     */
    public function fetchAll(int $hotelId)
    {
        return $this->discountMapper->fetchAll($hotelId);
    }
}
