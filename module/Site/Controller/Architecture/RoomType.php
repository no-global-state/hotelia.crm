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
use Krystal\Validate\Pattern;

final class RoomType extends AbstractCrmController
{
    /**
     * Creates room type form
     * 
     * @param \Krystal\Db\Filter\InputDecorator|array $type
     * @param array $priceGroups
     * @return string
     */
    private function createForm($type, array $priceGroups) : string
    {
        return $this->view->render('room-type/form', [
            'type' => $type,
            'types' => $this->getModuleService('roomTypeService')->fetchAll($this->getCurrentLangId(), $this->getHotelId()),
            'categories' => $this->getModuleService('roomCategoryService')->fetchList($this->getCurrentLangId()),
            'priceGroups' => $priceGroups
        ]);
    }

    /**
     * Renders main grid
     * 
     * @return string
     */
    public function indexAction() : string
    {
        return $this->view->render('room-type/index', array(
            'types' => $this->getModuleService('roomTypeService')->fetchAll($this->getCurrentLangId(), $this->getHotelId()),
            'categories' => $this->getModuleService('roomCategoryService')->fetchList($this->getCurrentLangId()),
        ));
    }

    /**
     * Persists room type
     * 
     * @return mixed
     */
    public function saveAction()
    {
        $data = $this->request->getPost();
        $data['type'] = $this->getWithHotelId($data['type']);

        $service = $this->getModuleService('roomTypeService');
        !$data['type']['id'] ? $service->add($data) : $service->update($data);

        $this->flashBag->set('success', $data['type']['id'] ? 'Room type has been updated successfully' : 'Room type has added updated successfully');
        return 1;
    }

    /**
     * Renders adding form
     * 
     * @return string
     */
    public function addAction()
    {
        $priceGroups = $this->createMapper('\Site\Storage\MySQL\PriceGroupMapper')->fetchAll(false);
        return $this->createForm(new InputDecorator(), $priceGroups);
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
        $type = $service->findById($id);

        if (!empty($type)) {
            $priceGroups = $this->createMapper('\Site\Storage\MySQL\PriceGroupMapper')->fetchAll(false);
            $priceGroups = array_replace_recursive($priceGroups, $service->findPricesByRoomTypeId($id));

            return $this->createForm($type, $priceGroups);
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
