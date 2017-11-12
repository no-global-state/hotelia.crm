<?php

namespace Site\Controller\Facility;

use Site\Controller\AbstractCrmController;
use Krystal\Db\Filter\InputDecorator;

final class Category extends AbstractCrmController
{
    /**
     * Saves a category
     * 
     * @return int
     */
    public function saveAction() : int
    {
        $data = $this->request->getPost();

        $service = $this->getModuleService('facilitiyService');
        $service->saveCategory($data);

        $this->flashBag->set('success', $data['id'] ? 'The category has been updated successfully' : 'The category has been added successfully');
        return 1;
    }

    /**
     * Creates category form
     * 
     * @param mixed $category
     * @return string
     */
    private function createForm($category) : string
    {
        return $this->view->render('facility/form-category', array(
            'category' => $category
        ));
    }

    /**
     * Renders empty form
     * 
     * @return string
     */
    public function addAction() : string
    {
        return $this->createForm(new InputDecorator());
    }

    /**
     * Renders edit form by category ID
     * 
     * @param int $id Category ID
     * @return mixed
     */
    public function editAction(int $id)
    {
        $category = $this->getModuleService('facilitiyService')->getCategoryById($id);

        if ($category) {
            return $this->createForm($category);
        } else {
            return false;
        }
    }

    /**
     * Deletes a category by its ID
     * 
     * @param int $id Category ID
     * @return void
     */
    public function deleteAction(int $id) : void
    {
        $service = $this->getModuleService('facilitiyService');
        $service->deleteCategory($id);

        $this->flashBag->set('danger', 'The category has been deleted successfully');
        $this->response->redirectToPreviousPage();
    }
}
