<?php

namespace Site\Controller\Facility;

use Site\Controller\AbstractCrmController;
use Krystal\Db\Filter\InputDecorator;

final class ItemData extends AbstractCrmController
{
    /**
     * Create item data form
     * 
     * @param mixed $entity
     * @param int $itemId
     * @return string
     */
    private function createForm($data, int $itemId) : string
    {
        // Append one breadcrumb
        $this->view->getBreadcrumbBag()
                   ->addOne('Facilities', $this->createUrl('Site:Facility:Grid@indexAction'))
                   ->addOne('Facility item data', $this->createUrl('Site:Facility:ItemData@indexAction', [$itemId]))
                   ->addOne(!is_array($data) ? 'Add item data' : 'Edit item data');

        return $this->view->render('facility-data/form', [
            'data' => $data,
            'icon' => 'glyphicon glyphicon-check'
        ]);
    }

    /**
     * Renders data by associated item id
     * 
     * @param int $itemId
     * @return string
     */
    public function indexAction(int $itemId) : string
    {
        // Append one breadcrumb
        $this->view->getBreadcrumbBag()
                   ->addOne('Facilities', $this->createUrl('Site:Facility:Grid@indexAction'))
                   ->addOne('Facility item data');

        $data = $this->getModuleService('facilityItemDataService')->fetchAll($itemId, $this->getCurrentLangId());

        return $this->view->render('facility-data/index', [
            'icon' => 'glyphicon glyphicon-check',
            'data' => $data,
            'itemId' => $itemId
        ]);
    }

    /**
     * Renders empty adding form
     * 
     * @param int $itemId
     * @return string
     */
    public function addAction(int $itemId) : string
    {
        $entity = new InputDecorator();
        $entity['item_id'] = $itemId;

        return $this->createForm($entity, $itemId);
    }

    /**
     * Renders edit form
     * 
     * @param int $itemId
     * @param int $Id Data id
     * @return mixed
     */
    public function editAction(int $itemId, int $id)
    {
        $data = $this->getModuleService('facilityItemDataService')->fetchById($id);

        if ($data !== false) {
            return $this->createForm($data, $itemId);
        } else {
            return false;
        }
    }

    /**
     * Save item data
     * 
     * @return void
     */
    public function saveAction()
    {
        $input = $this->request->getPost();
        $this->getModuleService('facilityItemDataService')->save($input);

        $this->flashBag->set('success', $input['item']['id'] ? 'The item data has been updated successfully' : 'The item data has been added successfully');
        return 1;
    }

    /**
     * Delete item data by its id
     * 
     * @param int $id
     * @return void
     */
    public function deleteAction(int $id)
    {
        // Delete item data by its id
        $this->getModuleService('facilityItemDataService')->deleteById($id);

        $this->flashBag->set('danger', 'The item data has been deleted successfully');
        $this->response->redirectToPreviousPage();
    }
}
