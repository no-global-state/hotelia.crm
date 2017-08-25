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
use Krystal\Db\Filter\InputDecorator;
use Krystal\Stdlib\ArrayUtils;

final class RoomType extends AbstractSiteController
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
    private function createGrid($entity)
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
    public function indexAction()
    {
        return $this->createGrid(new InputDecorator());
    }

    /**
     * Saves a room
     * 
     * @return string
     */
    public function saveAction()
    {
        $data = $this->request->getPost();
        $this->createRoomTypeMapper()->persist($data);

        return 1;
    }

    /**
     * Edits the room
     * 
     * @param string $id
     * @return string
     */
    public function editAction($id)
    {
        $room = $this->createRoomTypeMapper()->findByPk($id);

        if (!empty($room)) {
            return $this->createGrid($room);
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
        $this->createRoomTypeMapper()->deleteByPk($id);
        $this->sessionBag->set('success', 'The room has been deleted successfully');

        return $this->redirectToRoute('Site:Architecture:Grid@indexAction');
    }
}
