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
        // If wizard is not finished, then redirect to it
        if (!$this->getModuleService('userService')->isWizardFinished($this->getUserId())) {

            // Redirect to Wizard URL
            $this->response->redirect($this->createUrl('Site:Wizard@indexAction'));
        }

        return $this->view->render('home', array(
            'stat' => $this->getModuleService('roomService')->createStat($this->getHotelId()),
            'pageTitle' => 'My property',
            'icon' => 'glyphicon glyphicon-blackboard'
        ));
    }
}
