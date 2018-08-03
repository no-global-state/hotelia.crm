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
use Krystal\Stdlib\ArrayUtils;

final class RoomCleaning extends AbstractCrmController
{
    /**
     * Renders room cleaning grid
     * 
     * @return string
     */
    public function indexAction()
    {
        // Add a breadcrumb
        $this->view->getBreadcrumbBag()
                   ->addOne('Room cleaning');
        
        $types = ArrayUtils::arrayList($this->createMapper('\Site\Storage\MySQL\RoomTypeMapper')->fetchAll($this->getCurrentLangId(), $this->getHotelId()), 'id', 'name');

        return $this->view->render('room/cleaning', array(
            'icon' => 'glyphicon glyphicon-refresh',
            'types' => $types,
            'data' => $this->createMapper('\Site\Storage\MySQL\RoomMapper')->fetchCleaning($this->getCurrentLangId(), $this->getHotelId())
        ));
    }

    /**
     * Update "cleaned" attribute
     * 
     * @param string $id Room id
     * @param string $type
     * @return void
     */
    public function markAction(int $id, int $type)
    {
        $this->getModuleService('roomService')->updateCleaned($id, $type);
        $this->flashBag->set('success', 'Successfully updated');

        return $this->response->redirectToPreviousPage();
    }

    /**
     * Update "cleaned" attribute
     * 
     * @param string $type
     * @return void
     */
    public function markBatchAction(int $type)
    {
        $ids = array_keys($this->request->getPost('batch'));
        $this->getModuleService('roomService')->updateCleaned($ids, $type);

        $this->flashBag->set('success', 'Successfully updated');
        return $this->response->redirectToPreviousPage();
    }
}
