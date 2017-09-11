<?php

namespace Site\Controller\Facility;

use Site\Controller\AbstractSiteController;
use Krystal\Db\Filter\InputDecorator;

final class Item extends AbstractSiteController
{
    /**
     * Persist an item
     * 
     * @return string
     */
    public function saveAction()
    {
        $input = $this->request->getPost();

        $service = $this->getModuleService('facilitiyService');
        $service->saveItem($input);

        return 1;
    }

    /**
     * Creates a form
     * 
     * @param array $item
     * @return string
     */
    private function createForm($item)
    {
        return $this->view->render('facility/form-item', array(
            'item' => $item,
            'categories' => $this->getModuleService('facilitiyService')->getCategoryList()
        ));
    }

    /**
     * Renders adding form
     * 
     * @return string
     */
    public function addAction()
    {
        return $this->createForm(new InputDecorator());
    }

    /**
     * Renders item edit form
     * 
     * @param string $id
     * @return string
     */
    public function editAction($id)
    {
        $item = $this->getModuleService('facilitiyService')->getItemById($id);

        if ($item) {
            return $this->createForm($item);
        } else {
            return false;
        }
    }

    /**
     * Persist a category
     * 
     * @param string $id Item ID
     * @return string
     */
    public function deleteAction($id)
    {
        $service = $this->getModuleService('facilitiyService');
        $service->deleteItem($id);

        return 1;
    }
}
