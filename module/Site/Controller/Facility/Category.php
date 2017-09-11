<?php

namespace Site\Controller\Facility;

use Site\Controller\AbstractSiteController;
use Krystal\Db\Filter\InputDecorator;

final class Category extends AbstractSiteController
{
    /**
     * Saves a category
     * 
     * @return string
     */
    public function saveAction()
    {
        $input = $this->request->getPost();

        $service = $this->getModuleService('facilitiyService');
        $service->saveCategory($input);

        return 1;
    }

    /**
     * Creates category form
     * 
     * @return string
     */
    private function createForm($category)
    {
        return $this->view->render('facility/form-category', array(
            'category' => $category
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
     * Renders edit form
     * 
     * @param string $id
     * @return string
     */
    public function editAction($id)
    {
        $category = $this->getModuleService('facilitiyService')->getCategoryById($id);

        if ($category) {
            return $this->createForm($category);
        } else {
            return false;
        }
    }

    /**
     * Deletes a category
     * 
     * @param string $id
     * @return string
     */
    public function deleteAction($id)
    {
        $service = $this->getModuleService('facilitiyService');
        $service->deleteCategory($id);

        return 1;
    }
}
