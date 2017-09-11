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

    /**
     * Renders checklist
     * 
     * @return string
     */
    public function checklistAction()
    {
        $service = $this->getModuleService('facilitiyService');

        if ($this->request->isPost()) {

            $ids = array_keys($this->request->getPost('checked'));
            $service->updateRelation($this->getHotelId(), $ids);
            return 1;

        } else {
            return $this->view->render('facility/checklist', array(
                'checklist' => $service->getCollection()
            ));
        }
    }
}
