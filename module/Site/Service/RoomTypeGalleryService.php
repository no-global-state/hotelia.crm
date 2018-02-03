<?php

namespace Site\Service;

use Site\Module;
use Site\Storage\MySQL\RoomTypeGalleryMapper;
use Krystal\Image\Tool\ImageManagerInterface;
use Krystal\Application\Model\AbstractService;

final class RoomTypeGalleryService extends AbstractService
{
    const PARAM_IMAGE_SIZE_LARGE = '850x450';
    const PARAM_IMAGE_SIZE_SMALL = '80x50';

    /**
     * Any compliant room gallery mapper
     * 
     * @var \Site\Storage\MySQL\RoomTypeGalleryMapper
     */
    private $roomTypeGalleryMapper;

    /**
     * Image handler service
     * 
     * @var \Krystal\Image\Tool\ImageManagerInterface
     */
    private $imageManager;

    /**
     * State initialization
     * 
     * @param \Site\Storage\MySQL\RoomTypeGalleryMapper $roomGalleryMapper
     * @param \Krystal\Image\Tool\ImageManagerInterface $imageManager
     * @return void
     */
    public function __construct(RoomTypeGalleryMapper $roomTypeGalleryMapper, ImageManagerInterface $imageManager)
    {
        $this->roomTypeGalleryMapper = $roomTypeGalleryMapper;
        $this->imageManager = $imageManager;
    }

    /**
     * Updates a photo
     * 
     * @param array $input
     * @return boolean
     */
    public function update(array $input)
    {
        // Files
        $data =& $input['data'];
        $file =& $input['files']['file'];

        // Only possible when file is selected
        if (!empty($file)) {
            // Make names unique
            $this->filterFileInput($file);
            $data['file'] = $file[0]->getName();

            // Upload the image
            $this->imageManager->upload($data['id'], $file);
        }

        // Persists
        return $this->roomTypeGalleryMapper->persist($data);
    }

    /**
     * Adds a photo
     * 
     * @param int $roomId
     * @param array $input
     * @return boolean
     */
    public function add(int $hotelId, array $input)
    {
        // Files
        $data =& $input['data'];
        $file =& $input['files']['file'];

        // Only possible when file is selected
        if (!empty($file)) {
            // Make names unique
            $this->filterFileInput($file);
            $data['file'] = $file[0]->getName();

            // Persists
            $this->roomTypeGalleryMapper->persist($data);

            // Last id
            $id = $this->roomTypeGalleryMapper->getMaxId();

            // Upload the image
            $this->imageManager->upload($id, $file);

            return true;

        } else {
            return false;
        }
    }

    /**
     * Deletes a photo by its associated id
     * 
     * @param string $id
     * @return boolean
     */
    public function deleteById($id)
    {
        return $this->roomTypeGalleryMapper->deleteByPk($id) && $this->imageManager->delete($id);
    }

    /**
     * Creates image path URL
     * 
     * @param array $row
     * @param string $size
     * @return string
     */
    private function createImagePath(array $row, $size)
    {
        return sprintf('%s/%s/%s', Module::PARAM_ROOM_GALLERY_PATH . $row['id'], $size, $row['file']);
    }

    /**
     * Fetch photos by their associated ID
     * 
     * @param string $id
     * @param string $size
     * @return array
     */
    public function fetchById($id, $size = self::PARAM_IMAGE_SIZE_LARGE)
    {
        $row = $this->roomTypeGalleryMapper->findByPk($id);
        $row['file'] = $this->createImagePath($row, $size);

        return $row;
    }

    /**
     * Fetch all photos
     * 
     * @param string $hotelId
     * @param string $size
     * @return array
     */
    public function fetchAll($roomTypeId, $size = self::PARAM_IMAGE_SIZE_LARGE)
    {
        $rows = $this->roomTypeGalleryMapper->fetchAll($roomTypeId);

        foreach ($rows as &$row) {
            $row['file'] = $this->createImagePath($row, $size);
        }

        return $rows;
    }
}
