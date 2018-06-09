<?php

namespace Site\Controller;

use Krystal\Db\Filter\InputDecorator;

final class RoomBed extends AbstractCrmController
{
    /**
     * Creates form
     * 
     * @param mixed $entity
     * @return string
     */
    private function createForm($entity)
    {
        // Append breadcrumbs
        $this->view->getBreadcrumbBag()
                   ->addOne('Beds', $this->createUrl('Site:RoomBed@indexAction'))
                   ->addOne(!is_array($entity) ? 'Add new bed' : 'Edit the bed');

        return $this->view->render('room-bed/form', [
            'bed' => $entity,
            'icon' => 'glyphicon glyphicon-pencil'
        ]);
    }

    /**
     * Renders grid
     * 
     * @return string
     */
    public function indexAction()
    {
        $this->view->getBreadcrumbBag()
                   ->addOne('Beds');

        return $this->view->render('room-bed/index', [
            'beds' => $this->getModuleService('bedService')->fetchAll($this->getCurrentLangId())
        ]);
    }

    /**
     * Renders add form
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
     * @param int $id Bed ID
     * @return string
     */
    public function editAction(int $id)
    {
        $bed = $this->getModuleService('bedService')->fetchById($id);

        if ($bed !== false) {
            return $this->createForm($bed);
        } else {
            return false;
        }
    }

    /**
     * Deletes a bed
     * 
     * @param int $id Bed ID
     * @return void
     */
    public function deleteAction(int $id) : void
    {
        $this->getModuleService('bedService')->deleteById($id);

        $this->flashBag->set('danger', 'The bed has been deleted successfully');
        $this->response->redirectToPreviousPage();
    }

    /**
     * Saves a bed
     * 
     * @return string
     */
    public function saveAction()
    {
        $data = $this->request->getPost();
        $this->getModuleService('bedService')->save($data);

        $this->flashBag->set('success', $data['bed']['id'] ? 'The bed has been updated successfully' : 'The bed has been added successfully');
        return 1;
    }
}
