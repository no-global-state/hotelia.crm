<?php

namespace Site\Controller;

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
     * Shows a home page
     * 
     * @return string
     */
    public function indexAction()
    {
        // If hotel ID provided
        if ($this->getHotelId() !== null) {
            $mapper = $this->createMapper('\Site\Storage\MySQL\TransactionMapper');

            // If wizard is not finished, then redirect to it
            if (!$this->getModuleService('hotelService')->isWizardFinished($this->getHotelId())) {
                // Redirect to Wizard URL
                $this->response->redirect($this->createUrl('Site:Wizard@indexAction'));
            }

            return $this->view->render('home', array(
                'states' => $this->getModuleService('reservationService')->countStates($this->getHotelId()),
                'reservations' => $this->getModuleService('reservationService')->fetchLatest($this->getHotelId()),
                'transactions' => $mapper->fetchLast($this->getHotelId()),
                'stat' => $this->getModuleService('roomService')->createStat($this->getHotelId()),
                'pageTitle' => 'My property',
                'icon' => 'glyphicon glyphicon-blackboard'
            ));

        } else {

            return $this->response->redirect($this->createUrl('Site:Property@indexAction'));
        }
    }
}
