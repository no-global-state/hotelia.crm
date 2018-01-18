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
use Site\Collection\CleaningCollection;
use Site\Collection\RoomQualityCollection;
use Krystal\Stdlib\ArrayUtils;
use Krystal\Db\Filter\InputDecorator;
use Krystal\Validate\Pattern;

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
        return $this->view->render('architecture/form-room', array(
            'entity' => $entity,
            'roomTypes' => $this->getModuleService('architectureService')->getRoomTypes($this->getCurrentLangId(), $this->getHotelId()),
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
        $entity = $this->createRoomMapper()->fetchById($id, $this->getCurrentLangId());

        return $this->view->disableLayout()->render('architecture/room-view', array(
            'entity' => $entity,
            'inventory' => $this->createMapper('\Site\Storage\MySQL\RoomInventoryMapper')->fetchAll($id)
        ));
    }

    /**
     * Saves a room
     * 
     * @return mixed
     */
    public function saveAction()
    {
        $data = $this->request->getPost();
        $data = $this->getWithHotelId($data);

        $this->formAttribute->setNewAttributes($data);

        // Whether name checking needs to be done
        $nameExists = $this->getModuleService('architectureService')->roomNameExists($data['name'], $this->getHotelId());
        $hasChanged = $this->formAttribute->hasChanged('name') ? $nameExists : false;

        $formValidator = $this->createValidator([
            'input' => [
                'source' => $data,
                'definition' => [
                    'name' => [
                        'required' => true,
                        'rules' => [
                            'NotEmpty' => [
                                'message' => 'Name can not be blank'
                            ],
                            'Unique' => [
                                'message' => 'Provided room name already exists. Please use another name',
                                'value' => $data['id'] ? $hasChanged : $nameExists,
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        if ($formValidator->isValid()) {
            $this->createRoomMapper()->persist($data);

            $this->flashBag->set('success', $data['id'] ? 'The room has been updated successfully' : 'The room has been added successfully');
            return 1;

        } else {
            return $formValidator->getErrors();
        }
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
            // Save the old name
            $this->formAttribute->setOldAttribute('name', $room['name']);

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
