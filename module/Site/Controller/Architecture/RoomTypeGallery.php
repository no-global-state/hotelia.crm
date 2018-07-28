<?php

namespace Site\Controller\Architecture;

use Site\Controller\AbstractCrmController;
use Krystal\Db\Filter\InputDecorator;

final class RoomTypeGallery extends AbstractCrmController
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
        // Append a breadcrumb
        $this->view->getBreadcrumbBag()
                   ->addOne('Room types', $this->createUrl('Site:Architecture:RoomType@indexAction'))
                   ->addOne('Gallery', $this->createUrl('Site:Architecture:RoomType@editAction', [$roomId]))
                   ->addOne(is_array($entity) ? 'Edit the photo' : 'Add a photo');

        return $this->view->render('room-type-gallery/form', [
            'entity' => $entity,
            'roomId' => $roomId,
            'icon' => 'glyphicon glyphicon-pencil'
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
        $photo = $this->getModuleService('roomTypeGalleryService')->fetchById($id);

        if ($photo !== false) {
            return $this->createForm($photo, $photo['room_type_id']);
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
        $service = $this->getModuleService('roomTypeGalleryService');

        if ($this->request->getPost('id')) {
            $service->update($this->request->getAll());
        } else {
            $service->add($this->request->getPost('room_type_id'), $this->request->getAll());
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
        $this->getModuleService('roomTypeGalleryService')->deleteById($id);

        $this->flashBag->set('danger', 'The photo has been deleted successfully');
        $this->response->redirectToPreviousPage();
    }
}
