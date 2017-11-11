<?php

/**
 * This file is part of the Hotelia CRM Solution
 * 
 * Copyright (c) No Global State Lab
 * 
 * For the full copyright and license information, please view
 * the license file that was distributed with this source code.
 */

namespace Site\Controller\Architecture;

use Site\Controller\AbstractCrmController;
use Site\Service\CleaningCollection;
use Site\Service\RoomQualityCollection;
use Krystal\Stdlib\ArrayUtils;
use Krystal\Db\Filter\InputDecorator;

final class Room extends AbstractCrmController
{
    /**
     * @return \Site\Storage\RoomMapper
     */
    private function createRoomMapper()
    {
        return $this->createMapper('\Site\Storage\MySQL\RoomMapper');
    }

    /**
     * Renders room form
     * 
     * @param mixed $entity
     * @return string
     */
    private function createForm($entity) : string
    {
        if ($this->getFloorIdKeeper()->hasLastCategoryId()) {
            $entity['floor_id'] = $this->getFloorIdKeeper()->getLastCategoryId();
        }

        return $this->view->render('architecture/form-room', array(
            'entity' => $entity,
            'floors' => $this->getModuleService('architectureService')->getFloors($this->getHotelId()),
            'roomTypes' => $this->getModuleService('architectureService')->getRoomTypes($this->getHotelId()),
            'cleaningCollection' => new CleaningCollection(),
            'roomQualities' => (new RoomQualityCollection())->getAll()
        ));
    }

    /**
     * Renders room info
     * 
     * @param int $id Room ID
     * @return string
     */
    public function viewAction(int $id)
    {
        $entity = $this->createRoomMapper()->fetchById($id);

        return $this->view->disableLayout()->render('architecture/room-view', array(
            'entity' => $entity,
            'inventory' => $this->createMapper('\Site\Storage\MySQL\RoomInventoryMapper')->fetchAll($id)
        ));
    }

    /**
     * Saves a room
     * 
     * @return int
     */
    public function saveAction() : int
    {
        $data = $this->request->getPost();
        $this->createRoomMapper()->persist($data);

        $this->flashBag->set('success', $data['id'] ? 'The room has been updated successfully' : 'The room has been added successfully');
        return 1;
    }

    /**
     * Renders empty form
     * 
     * @return string
     */
    public function addAction() : string
    {
        return $this->createForm(new InputDecorator);
    }

    /**
     * Edits the room by its ID
     * 
     * @param int $id Room ID
     * @return string
     */
    public function editAction(int $id)
    {
        $room = $this->createRoomMapper()->findByPk($id);

        if (!empty($room)) {
            return $this->createForm($room);
        } else {
            return false;
        }
    }

    /**
     * Deletes a room by its ID
     * 
     * @param int $id Room ID
     * @return void
     */
    public function deleteAction($id) : void
    {
        $this->createRoomMapper()->deleteByPk($id);
        $this->flashBag->set('success', 'The room has been deleted successfully');

        return $this->redirectToRoute('Site:Architecture:Grid@indexAction');
    }
}
