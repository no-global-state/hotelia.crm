<?php

namespace Site\Controller;

use Site\Collection\TimeRangeCollection;

final class Crm extends AbstractCrmController
{
    /**
     * Switches to another hotel
     * 
     * @param int $hotelId
     * @return void
     */
    public function hotelSwitchAction(int $hotelId)
    {
        $this->becomeAdmin($hotelId);

        return $this->response->redirect($this->createUrl('Site:Crm@indexAction'));
    }

    /**
     * Renders schedule grid
     * 
     * @return string
     */
    public function chessAction() : string
    {
        // Load daypilot
        $this->view->getPluginBag()
                   ->load('daypilot');

        return $this->view->render('chessboard/main', [
            'pageTitle' => 'Chessboard',
            'icon' => 'glyphicon glyphicon-king',
            'types' => $this->getModuleService('roomService')->getRoomTypes($this->getCurrentLangId(), $this->getHotelId()),
            'timeRanges' => (new TimeRangeCollection())->getAll()
        ]);
    }

    /**
     * Shows a home page
     * 
     * @return string
     */
    public function indexAction()
    {
        $mapper = $this->createMapper('\Site\Storage\MySQL\TransactionMapper');

        // If wizard is not finished, then redirect to it
        if (!$this->getModuleService('userService')->isWizardFinished($this->getUserId())) {
            // Redirect to Wizard URL
            $this->response->redirect($this->createUrl('Site:Wizard@indexAction'));
        }

        return $this->view->render('home', array(
            'transactions' => $mapper->fetchLast($this->getHotelId()),
            'stat' => $this->getModuleService('roomService')->createStat($this->getHotelId()),
            'pageTitle' => 'My property',
            'icon' => 'glyphicon glyphicon-blackboard'
        ));
    }
}
