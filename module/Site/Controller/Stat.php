<?php

namespace Site\Controller;

use Krystal\Date\TimeHelper;

final class Stat extends AbstractCrmController
{
    /**
     * Renders statistic page
     * 
     * @return string
     */
    public function indexAction()
    {
        if ($this->request->isPost()) {
            $year = $this->request->getPost('year');
            $months = $this->request->getPost('months');
            $rooms = $this->request->getPost('rooms');
            $priceGroupId = $this->request->getPost('price_group_id');

            $mapper = $this->createMapper('\Site\Storage\MySQL\ReservationMapper');
            $sum = $mapper->getSumCount($year, $months, $rooms, $priceGroupId);

            return $this->view->disableLayout()->render('stat/response', [
                'sum' => $sum
            ]);

        } else {

            // Configure view
            $this->view->getPluginBag()->load('chart');
            $this->view->getBreadcrumbBag()->addOne('Statistic');

            return $this->view->render('stat/index', [
                'icon' => 'glyphicon glyphicon-stats',
                'stat' => true,
                'months' => TimeHelper::getMonths(),
                'year' => date('Y'),
                'priceGroups' => $this->getModuleService('priceGroupService')->fetchList(),
                'rooms' => $this->getModuleService('roomService')->createRooms($this->getCurrentLangId(), $this->getHotelId())
            ]);
        }
    }
}
