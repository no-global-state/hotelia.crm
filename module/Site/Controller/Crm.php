<?php

namespace Site\Controller;

final class Crm extends AbstractCrmController
{
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
            'stat' => $this->getModuleService('architectureService')->createStat($this->getHotelId())
        ));
    }
}
