<?php

namespace Site\Controller;

use Krystal\Db\Filter\InputDecorator;

final class HotelType extends AbstractCrmController
{
    /**
     * Renders form
     * 
     * @param mixed $entity
     * @return string
     */
    private function createForm($hotelType) : string
    {
        // Appends on breadcrumb
        $this->view->getBreadcrumbBag()
                   ->addOne('Hotel types', $this->createUrl('Site:HotelType@indexAction'))
                   ->addOne(!is_array($hotelType) ? 'Add new hotel type' : 'Edit the hotel type');

        return $this->view->render('hotel-type/form', [
            'hotelType' => $hotelType,
            'icon' => 'glyphicon glyphicon-pencil'
        ]);
    }

    /**
     * Renders main grid
     * 
     * @return string
     */
    public function indexAction() : string
    {
        // Appends on breadcrumb
        $this->view->getBreadcrumbBag()
                   ->addOne('Hotel types');

        return $this->view->render('hotel-type/index', [
            'hotelTypes' => $this->getModuleService('hotelTypeService')->fetchAll($this->getCurrentLangId()),
            'icon' => 'glyphicon glyphicon-resize-full'
        ]);
    }

    /**
     * Renders adding form
     * 
     * @return mixed
     */
    public function addAction()
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
        $entity = $this->getModuleService('hotelTypeService')->fetchById($id, 0);

        if ($entity) {
            return $this->createForm($entity);
        } else {
            return false;
        }
    }

    /**
     * Delete hotel type by its ID
     * 
     * @param int $id
     * @return void
     */
    public function deleteAction(int $id)
    {
        $this->getModuleService('hotelTypeService')->deleteById($id);

        $this->flashBag->set('danger', 'Hotel type has been removed successfully');
        $this->response->redirectToPreviousPage();
    }

    /**
     * Saves hotel type
     * 
     * @return mixed
     */
    public function saveAction()
    {
        $data = $this->request->getPost();
        $this->getModuleService('hotelTypeService')->save($data);

        $this->flashBag->set('success', $data['type']['id'] ? 'Hotel type has been updated successfully' : 'Hotel type has been added successfully');
        return 1;
    }
}
