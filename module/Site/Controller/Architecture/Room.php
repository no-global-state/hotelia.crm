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
     * Render floors and rooms
     * 
     * @return string
     */
    public function indexAction()
    {
        // Append a breadcrumb
        $this->view->getBreadcrumbBag()
                   ->addOne('Architecture');

        return $this->view->render('room/index', array(
            'icon' => 'glyphicon glyphicon-home',
            'rooms' => $this->createRoomMapper()->fetchAll($this->getCurrentLangId(), $this->getHotelId()),
            'cleaningCollection' => new CleaningCollection(),
            'roomQualityCollection' => new RoomQualityCollection()
        ));
    }

    /**
     * Renders room form
     * 
     * @param mixed $entity
     * @return string
     */
    private function createForm($entity) : string
    {
        // Append a breadcrumb
        $this->view->getBreadcrumbBag()
                   ->addOne('Architecture', $this->createUrl('Site:Architecture:Room@indexAction'))
                   ->addOne(is_array($entity) ? 'Edit the room' : 'Add a room');

        return $this->view->render('room/form', array(
            'icon' => 'glyphicon glyphicon-pencil',
            'entity' => $entity,
            'roomTypes' => $this->getModuleService('roomService')->getRoomTypes($this->getCurrentLangId(), $this->getHotelId()),
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
        $entity = $this->getModuleService('roomService')->fetchById($id, $this->getCurrentLangId());

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
        $roomService = $this->getModuleService('roomService');

        $data = $this->request->getPost();
        $data = $this->getWithHotelId($data);

        $this->formAttribute->setNewAttributes($data);

        // Whether name checking needs to be done
        $nameExists = $roomService->roomNameExists($data['name'], $this->getHotelId());
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
            $roomService->save($data);

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
     * @return mixed
     */
    public function editAction(int $id)
    {
        $room = $this->getModuleService('roomService')->getById($id, $this->getCurrentLangId());

        // If can find by id
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
        $this->getModuleService('roomService')->deleteById($id);

        $this->flashBag->set('success', 'The room has been deleted successfully');
        $this->redirectToRoute('Site:Architecture:Room@indexAction');
    }
}
