<?php

namespace Site\Controller\Facility;

use Site\Controller\AbstractCrmController;
use Krystal\Db\Filter\InputDecorator;

final class Item extends AbstractCrmController
{
    /**
     * Persist an item
     * 
     * @return string
     */
    public function saveAction() : int
    {
        $data = $this->request->getPost();

        $service = $this->getModuleService('facilitiyService');
        $service->saveItem($data);

        $this->flashBag->set('success', $data['item']['id'] ? 'The item has been updated successfully' : 'The item has been added successfully');
        return 1;
    }

    /**
     * Creates a form
     * 
     * @param mixed $item
     * @return string
     */
    private function createForm($item) : string
    {
        return $this->view->render('facility/form-item', array(
            'item' => $item,
            'categories' => $this->getModuleService('facilitiyService')->getCategoryList($this->getCurrentLangId())
        ));
    }

    /**
     * Renders adding form
     * 
     * @return string
     */
    public function addAction() : string
    {
        return $this->createForm(new InputDecorator());
    }

    /**
     * Renders item edit form by its ID
     * 
     * @param string $id
     * @return string
     */
    public function editAction(int $id)
    {
        $item = $this->getModuleService('facilitiyService')->getItemById($id);

        if ($item) {
            return $this->createForm($item);
        } else {
            return false;
        }
    }

    /**
     * Persist an item
     * 
     * @param string $id Item ID
     * @return string
     */
    public function deleteAction(int $id) : void
    {
        $service = $this->getModuleService('facilitiyService');
        $service->deleteItem($id);

        $this->flashBag->set('danger', 'The item has been deleted successfully');
        $this->response->redirectToPreviousPage();
    }
}
