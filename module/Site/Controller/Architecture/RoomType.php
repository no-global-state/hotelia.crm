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
     * Creates the grid
     * 
     * @param \Krystal\Db\Filter\InputDecorator|array $entity
     * @param array $priceGroups
     * @return string
     */
    private function createGrid($entity, array $priceGroups) : string
    {
        $service = $this->getModuleService('roomTypeService');

        return $this->view->render('architecture/room-type', array(
            'entity' => $entity,
            'id' => $entity['id'],
            'types' => $service->fetchAll(),
            'priceGroups' => $priceGroups
        ));
    }

    /**
     * Renders main grid
     * 
     * @return string
     */
    public function indexAction() : string
    {
        $priceGroups = $this->createMapper('\Site\Storage\MySQL\PriceGroupMapper')->fetchAll(false);
        return $this->createGrid(new InputDecorator(), $priceGroups);
    }

    /**
     * Persists room type
     * 
     * @return integer
     */
    public function saveAction() : int
    {
        $data = $this->request->getPost();
        $service = $this->getModuleService('roomTypeService');

        !$data['id'] ? $service->add($data) : $service->update($data);

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
        $service = $this->getModuleService('roomTypeService');
        $room = $service->findById($id);

        if (!empty($room)) {
            $priceGroups = $this->createMapper('\Site\Storage\MySQL\PriceGroupMapper')->fetchAll(false);
            $priceGroups = array_replace_recursive($priceGroups, $service->findPricesByRoomTypeId($id));

            return $this->createGrid($room, $priceGroups);
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
        $service = $this->getModuleService('roomTypeService');
        $service->deleteById($id);

        $this->flashBag->set('danger', 'The room type has been deleted successfully');
        $this->response->redirectToPreviousPage();
    }
}
