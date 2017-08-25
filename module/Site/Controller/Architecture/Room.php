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

use Site\Controller\AbstractSiteController;
use Krystal\Stdlib\ArrayUtils;

final class Room extends AbstractSiteController
{
    /**
     * @return \Site\Storage\RoomMapper
     */
    private function createRoomMapper()
    {
        return $this->createMapper('\Site\Storage\MySQL\RoomMapper');
    }

    /**
     * Returns a collection of floors
     * 
     * @return array
     */
    private function getFloors()
    {
        $rows = $this->createMapper('\Site\Storage\MySQL\FloorMapper')->fetchAll();
        return ArrayUtils::arrayList($rows, 'id', 'name');
    }

    /**
     * Returns room types
     * 
     * @return array
     */
    private function getRoomTypes()
    {
        $rows = $this->createMapper('\Site\Storage\MySQL\RoomTypeMapper')->fetchAll();
        return ArrayUtils::arrayList($rows, 'id', 'type');
    }

    /**
     * Saves a room
     * 
     * @param array $room
     * @return string
     */
    public function saveAction(array $entity = array())
    {
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $this->createRoomMapper()->persist($data);

            return 1;
        } else {
            return $this->view->render('architecture/form-room', array(
                'entity' => $entity,
                'floors' => $this->getFloors(),
                'roomTypes' => $this->getRoomTypes()
            ));
        }
    }

    /**
     * Edits the room
     * 
     * @param string $id
     * @return string
     */
    public function editAction($id)
    {
        $room = $this->createRoomMapper()->findByPk($id);

        if (!empty($room)) {
            return $this->saveAction($room);
        } else {
            return false;
        }
    }

    /**
     * Deletes a room
     * 
     * @param string $id
     * @return string
     */
    public function deleteAction($id)
    {
        $this->createRoomMapper()->deleteByPk($id);
        $this->sessionBag->set('success', 'The room has been deleted successfully');

        return $this->redirectToRoute('Site:Architecture:Grid@indexAction');
    }
}
