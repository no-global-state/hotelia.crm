<?php

namespace Site\Controller\Architecture;

use Site\Controller\AbstractCrmController;
use Krystal\Db\Filter\InputDecorator;

final class RoomGallery extends AbstractCrmController
{
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
            'images' => $this->getModuleService('roomGalleryService')->fetchAll($roomId),
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
        $photo = $this->getModuleService('roomGalleryService')->fetchById($id);

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
        $service = $this->getModuleService('roomGalleryService');

        if ($this->request->getPost('id')) {
            $service->update($this->request->getAll());
        } else {
            $service->add($this->request->getPost('room_id'), $this->request->getAll());
        }

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
        $this->getModuleService('roomGalleryService')->deleteByPk($id);

        $this->flashBag->set('danger', 'The photo has been deleted successfully');
        $this->response->redirectToPreviousPage();
    }
}
