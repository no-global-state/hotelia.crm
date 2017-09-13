<?php

namespace Site\Service;

use Site\Module;
use Site\Storage\MySQL\PhotoMapper;
use Krystal\Image\Tool\ImageManagerInterface;
use Krystal\Application\Model\AbstractService;

final class PhotoService extends AbstractService
{
    /**
     * Any compliant photo mapper
     * 
     * @var \Site\Storage\MySQL\PhotoMapper
     */
    private $photoMapper;

    /**
     * Image handler service
     * 
     * @var \Krystal\Image\Tool\ImageManagerInterface
     */
    private $imageManager;

    /**
     * State initialization
     * 
     * @param \Site\Storage\MySQL\PhotoMapper $photoMapper
     * @param \Krystal\Image\Tool\ImageManagerInterface $imageManager
     * @return void
     */
    public function __construct(PhotoMapper $photoMapper, ImageManagerInterface $imageManager)
    {
        $this->photoMapper = $photoMapper;
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
        return $this->photoMapper->persist($data);
    }

    /**
     * Adds a photo
     * 
     * @param string $hotelId
     * @param array $input
     * @return boolean
     */
    public function add($hotelId, array $input)
    {
        // Files
        $data =& $input['data'];
        $file =& $input['files']['file'];

        // Only possible when file is selected
        if (!empty($file)) {
            // Make names unique
            $this->filterFileInput($file);

            // Attach hotel ID
            $data['hotel_id'] = $hotelId;
            $data['file'] = $file[0]->getName();

            // Persists
            $this->photoMapper->persist($data);

            // Last id
            $id = $this->photoMapper->getMaxId();

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
        return $this->photoMapper->deleteByPk($id) && $this->imageManager->delete($id);
    }

    /**
     * Fetch photos by their associated ID
     * 
     * @param string $id
     * @return array
     */
    public function fetchById($id)
    {
        $row = $this->photoMapper->findByPk($id);
        $row['file'] = Module::PARAM_GALLERY_PATH . $row['id'] . '/200x200/' . $row['file'];

        return $row;
    }

    /**
     * Fetch all photos
     * 
     * @param string $hotelId
     * @return array
     */
    public function fetchAll($hotelId)
    {
        $rows = $this->photoMapper->fetchAll($hotelId);

        foreach ($rows as &$row) {
            $row['file'] = Module::PARAM_GALLERY_PATH . $row['id'] . '/200x200/' . $row['file'];
        }

        return $rows;
    }
}
