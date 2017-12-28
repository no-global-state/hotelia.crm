<?php

namespace Site\Service;

use Site\Module;
use Site\Storage\MySQL\PhotoMapper;
use Krystal\Image\Tool\ImageManagerInterface;
use Krystal\Application\Model\AbstractService;
use Krystal\Stdlib\ArrayUtils;

final class PhotoService extends AbstractService
{
    const PARAM_IMAGE_SIZE_LARGE = '850x450';
    const PARAM_IMAGE_SIZE_SMALL = '80x50';

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
     * Batch upload
     * 
     * @param int $hotelId
     * @param array $files
     * @return boolean
     */
    public function batchUpload(int $hotelId, array $files) : bool
    {
        $this->filterFileInput($files);

        foreach ($files as $file) {
            // Persists
            $this->photoMapper->persist([
                'hotel_id' => $hotelId,
                'file' => $file->getName()
            ]);

            // Last id
            $id = $this->photoMapper->getMaxId();

            // Upload the image
            $this->imageManager->upload($id, [$file]);
        }

        return true;
    }

    /**
     * Updates a cover
     * 
     * @param int $hotelId
     * @param int $photoId
     * @return boolean
     */
    public function updateCover(int $hotelId, int $photoId) : bool
    {
        return $this->photoMapper->updateCover($hotelId, $photoId);
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
     * Creates image path URL
     * 
     * @param int $id
     * @param string $file
     * @param string $size
     * @return string
     */
    public function createImagePath(int $id, string $file, string $size) : string
    {
        return sprintf('%s/%s/%s', Module::PARAM_GALLERY_PATH . $id, $size, $file);
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
        $row = $this->photoMapper->findByPk($id);
        $row['file'] = $this->createImagePath($row['id'], $row['file'], $size);

        return $row;
    }

    /**
     * Fetch all photos
     * 
     * @param string $hotelId
     * @param string $size
     * @return array
     */
    public function fetchList($hotelId, $size = self::PARAM_IMAGE_SIZE_LARGE)
    {
        return ArrayUtils::arrayList($this->fetchAll($hotelId, $size), 'id', 'file');
    }

    /**
     * Fetch all photos
     * 
     * @param string $hotelId
     * @param string $size
     * @return array
     */
    public function fetchAll($hotelId, $size = self::PARAM_IMAGE_SIZE_LARGE)
    {
        $rows = $this->photoMapper->fetchAll($hotelId);

        foreach ($rows as &$row) {
            $row['file'] = $this->createImagePath($row['id'], $row['file'], $size);
        }

        return $rows;
    }
}
