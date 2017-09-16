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
        return $this->view->render('home', array(
            'stat' => $this->getModuleService('architectureService')->createStat($this->getHotelId())
        ));
    }
}
