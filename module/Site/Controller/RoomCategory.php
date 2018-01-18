<?php

namespace Site\Controller;

use Krystal\Db\Filter\InputDecorator;

final class RoomCategory extends AbstractCrmController
{
    /**
     * Renders main grid
     * 
     * @return string
     */
    public function indexAction()
    {
        return $this->view->render('room-category/index', [
            'categories' => $this->getModuleService('roomCategoryService')->fetchAll($this->getCurrentLangId())
        ]);
    }

    /**
     * Renders form
     * 
     * @param mixed $category
     * @return string
     */
    private function createForm($category) : string
    {
        return $this->view->render('room-category/form', [
            'category' => $category
        ]);
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
     * Renders edit form
     * 
     * @param int $id
     * @return mixed
     */
    public function editAction(int $id)
    {
        $entity = $this->getModuleService('roomCategoryService')->fetchById($id);

        if ($entity) {
            return $this->createForm($entity);
        } else {
            return false;
        }
    }

    /**
     * Delete room category by its ID
     * 
     * @param int $id
     * @return void
     */
    public function deleteAction(int $id) : void
    {
        $this->getModuleService('roomCategoryService')->deleteById($id);

        $this->flashBag->set('danger', 'The room category has been deleted successfully');
        $this->response->redirectToPreviousPage();
    }

    /**
     * Save room category
     * 
     * @return string
     */
    public function saveAction()
    {
        $data = $this->request->getPost();
        $this->getModuleService('roomCategoryService')->save($data);

        $this->flashBag->set('success', $data['category']['id'] ? 'The room category has been updated successfully' : 'The room category has been added successfully');
        return 1;
    }
}
