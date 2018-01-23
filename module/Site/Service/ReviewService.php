<?php

namespace Site\Service;

use Site\Storage\MySQL\ReviewMapper;
use Site\Storage\MySQL\ReviewTypeMapper;
use Site\Storage\MySQL\ReviewMarkMapper;

final class ReviewService
{
    /**
     * Any compliant review mapper
     * 
     * @var \Site\Storage\MySQL\ReviewMapper
     */
    private $reviewMapper;

    /**
     * Review type mapper
     * 
     * @var \Site\Storage\MySQL\ReviewTypeMapper
     */
    private $reviewTypeMapper;

    /**
     * Any compliant review mark mapper
     * 
     * @var \Site\Storage\MySQL\ReviewMarkMapper
     */
    private $reviewMarkMapper;

    /**
     * State initialization
     * 
     * @param \Site\Storage\MySQL\ReviewMapper $reviewMapper
     * @param \Site\Storage\MySQL\ReviewTypeMapper $reviewTypeMapper
     * @param \Site\Storage\MySQL\ReviewMarkMapper $reviewMapper
     * @return void
     */
    public function __construct(ReviewMapper $reviewMapper, ReviewTypeMapper $reviewTypeMapper, ReviewMarkMapper $reviewMarkMapper)
    {
        $this->reviewMapper = $reviewMapper;
        $this->reviewTypeMapper = $reviewTypeMapper;
        $this->reviewMarkMapper = $reviewMarkMapper;
    }

    /**
     * Find all review types
     * 
     * @return array
     */
    public function findTypes() : array
    {
        return $this->reviewTypeMapper->fetchAll();
    }

    /**
     * Adds a review
     * 
     * @param int $langId
     * @param int $hotelId
     * @param array $input
     * @return bool
     */
    public function add(int $langId, int $hotelId, array $input) : bool
    {
        // Data to be inserted
        $data = [
            'lang_id' => $langId,
            'hotel_id' => $hotelId,
            'title' => $input['review-title'],
            'review' => $input['review-text']
        ];

        // Persist a review
        $this->reviewMapper->persist($data);
        $id = $this->reviewMapper->getMaxId();

        foreach ($input['mark'] as $typeId => $mark) {
            $this->reviewMarkMapper->persist([
                'review_id' => $id,
                'review_type_id' => $typeId,
                'mark' => $mark
            ]);
        }

        return true;
    }
}
