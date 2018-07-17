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
     * Fetch averages
     * 
     * @param int $hotelId
     * @param int $langId
     * @return array
     */
    public function fetchAverages(int $hotelId, int $langId) : array
    {
        return $this->reviewMapper->fetchAverages($hotelId, $langId);
    }

    /**
     * Find all review types
     * 
     * @param int $langId Language ID filter
     * @return array
     */
    public function findTypes(int $langId) : array
    {
        return $this->reviewTypeMapper->fetchAll($langId);
    }

    /**
     * Fetch all reviews
     * 
     * @param int $hotelId
     * @return array
     */
    public function fetchAll(int $hotelId) : array
    {
        return $this->reviewMapper->fetchAll($hotelId);
    }

    /**
     * Adds a review
     * 
     * @param int $langId
     * @param int $hotelId
     * @param array $input
     * @return boolean
     */
    public function add(int $langId, int $hotelId, array $input) : bool
    {
        // Data to be inserted
        $data = [
            'lang_id' => $langId,
            'hotel_id' => $hotelId,
            'title' => '',
            'review' => $input['review']
        ];

        // Persist a review
        $this->reviewMapper->persist($data);
        $id = $this->reviewMapper->getMaxId();

        // Insert marks
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
