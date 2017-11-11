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
use Krystal\Db\Filter\InputDecorator;
use Krystal\Stdlib\ArrayUtils;

final class RoomType extends AbstractCrmController
{
    /**
     * @return \Site\Storage\RoomMapper
     */
    private function createRoomTypeMapper()
    {
        return $this->createMapper('\Site\Storage\MySQL\RoomTypeMapper');
    }

    /**
     * Creates the grid
     * 
     * @param \Krystal\Db\Filter\InputDecorator|array $entity
     * @return string
     */
    private function createGrid($entity) : string
    {
        return $this->view->render('architecture/room-type', array(
            'entity' => $entity,
            'id' => $entity['id'],
            'types' => $this->createRoomTypeMapper()->fetchAll()
        ));
    }

    /**
     * Renders main grid
     * 
     * @return string
     */
    public function indexAction() : string
    {
        return $this->createGrid(new InputDecorator());
    }

    /**
     * Persists room type
     * 
     * @return integer
     */
    public function saveAction() : int
    {
        $data = $this->request->getPost();
        $this->createRoomTypeMapper()->persist($data);

        $this->flashBag->set('success', $data['id'] ? 'Room type has been updated successfully' : 'Room type has added updated successfully');
        return 1;
    }

    /**
     * Edits the room type by its ID
     * 
     * @param int $id Room type ID
     * @return string
     */
    public function editAction(int $id)
    {
        $room = $this->createRoomTypeMapper()->findByPk($id);

        if (!empty($room)) {
            return $this->createGrid($room);
        } else {
            return false;
        }
    }

    /**
     * Deletes a room type by its ID
     * 
     * @param int $id Room type ID
     * @return void
     */
    public function deleteAction(int $id) : void
    {
        $this->createRoomTypeMapper()->deleteByPk($id);
        $this->flashBag->set('danger', 'The room type has been deleted successfully');

        $this->response->redirectToPreviousPage();
    }
}
