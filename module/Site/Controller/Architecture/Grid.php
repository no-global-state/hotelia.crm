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
use Site\Service\CleaningCollection;
use Site\Service\RoomQualityCollection;

final class Grid extends AbstractSiteController
{
    /**
     * @return \Site\Storage\RoomMapper
     */
    private function createRoomMapper()
    {
        return $this->createMapper('\Site\Storage\MySQL\RoomMapper');
    }

    /**
     * @return \Site\Storage\FloorMapper
     */
    private function createFloorMapper()
    {
        return $this->createMapper('\Site\Storage\MySQL\FloorMapper');
    }

    /**
     * Renders the grid
     * 
     * @param string $id Floor ID
     * @return string
     */
    private function createGrid($id)
    {
        return $this->view->render('architecture/index', array(
            'floors' => $this->createFloorMapper()->fetchAll(),
            'floorId' => $id,
            'rooms' => $this->createRoomMapper()->fetchAll($id),
            'cleaningCollection' => new CleaningCollection(),
            'roomQualityCollection' => new RoomQualityCollection()
        ));
    }

    /**
     * Render floors and rooms
     * 
     * @return string
     */
    public function indexAction()
    {
        $id = $this->createFloorMapper()->getMaxId();
        return $this->createGrid($id);
    }

    /**
     * View rooms by associated floor ID
     * 
     * @param string $id Floor ID
     * @return string
     */
    public function floorAction($id)
    {
        return $this->createGrid($id);
    }
}
