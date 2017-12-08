<?php

namespace Site\Controller\Architecture;

use Site\Controller\AbstractCrmController;
use Krystal\Db\Filter\InputDecorator;

final class RoomGallery extends AbstractCrmController
{
    /**
     * Creates room gallery mapper
     * 
     * @return \Site\Storage\MySQL\RoomGalleryMapper
     */
    private function createRoomGalleryMapper()
    {
        return $this->createMapper('\Site\Storage\MySQL\RoomGalleryMapper');
    }

    /**
     * Creates a form
     * 
     * @param mixed $entity
     * @param int $roomId
     * @return string
     */
    private function createForm($entity, $roomId = null) : string
    {
        return $this->view->render('architecture/room-gallery-form', [
            'entity' => $entity,
            'roomId' => $roomId
        ]);
    }

    /**
     * Renders main
     * 
     * @param int $roomId Room ID
     * @return string
     */
    public function indexAction(int $roomId)
    {
        return $this->view->render('architecture/room-gallery-index', [
            'images' => $this->createRoomGalleryMapper()->fetchAll($roomId),
            'roomId' => $roomId
        ]);
    }

    /**
     * Renders empty adding form
     * 
     * @param int $roomId
     * @return string
     */
    public function addAction(int $roomId)
    {
        return $this->createForm(new InputDecorator(), $roomId);
    }

    /**
     * Renders edit form
     * 
     * @param int $id Photo ID
     * @return string
     */
    public function editAction(int $id)
    {
        $photo = $this->createRoomGalleryMapper()->findByPk($id);

        if ($photo !== false) {
            return $this->createForm($photo);
        } else {
            return false;
        }
    }

    /**
     * Saves a room gallery
     * 
     * @return int
     */
    public function saveAction() : int
    {
        $data = $this->request->getPost();
        $this->createRoomGalleryMapper()->persist($data);

        $this->flashBag->set('success', $data['id'] ? 'The photo has been updated successfully' : 'The photo has been added successfully');
        return 1;
    }

    /**
     * Deletes a photo by its associated id
     * 
     * @param int $id Room ID
     * @return void
     */
    public function deleteAction(int $id)
    {
        $this->createRoomGalleryMapper()->deleteByPk($id);

        $this->flashBag->set('danger', 'The photo has been deleted successfully');
        $this->response->redirectToPreviousPage();
    }
}
