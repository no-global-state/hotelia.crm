<?php

namespace Site\Controller\Facility;

use Site\Controller\AbstractSiteController;

final class Grid extends AbstractSiteController
{
    /**
     * Renders main grid
     * 
     * @return string
     */
    public function indexAction()
    {
        $service = $this->getModuleService('facilitiyService');

        return $this->view->render('facility/index', array(
            'categories' => $service->getCategories(),
            'categoryId' => null,
            'items' => $service->getItems()
        ));
    }
}
