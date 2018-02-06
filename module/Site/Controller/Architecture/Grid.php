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

final class Grid extends AbstractCrmController
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

        return $this->view->render('architecture/index', array(
            'icon' => 'glyphicon glyphicon-home',
            'rooms' => $this->createRoomMapper()->fetchAll($this->getCurrentLangId(), $this->getHotelId()),
            'cleaningCollection' => new CleaningCollection(),
            'roomQualityCollection' => new RoomQualityCollection()
        ));
    }
}
