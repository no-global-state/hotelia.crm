<?php

namespace Site\Controller\Facility;

use Site\Controller\AbstractCrmController;

final class Grid extends AbstractCrmController
{
    /**
     * Creates the grid
     * 
     * @param string $categoryId
     * @return string
     */
    private function createGrid($categoryId)
    {
        $service = $this->getModuleService('facilitiyService');

        return $this->view->render('facility/index', array(
            'categories' => $service->getCategories($this->getCurrentLangId()),
            'categoryId' => $categoryId,
            'items' => $service->getItems($categoryId)
        ));
    }

    /**
     * Renders grid filtering by category id
     * 
     * @param string $categoryId
     * @return string
     */
    public function categoryAction($categoryId)
    {
        return $this->createGrid($categoryId);
    }

    /**
     * Renders main grid
     * 
     * @return string
     */
    public function indexAction()
    {
        return $this->createGrid(null);
    }
}
