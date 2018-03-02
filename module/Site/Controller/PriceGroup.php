<?php

namespace Site\Controller;

use Krystal\Db\Filter\InputDecorator;

final class PriceGroup extends AbstractCrmController
{
    /**
     * Creates form
     * 
     * @param mixed $entity
     * @return string
     */
    private function createForm($entity) : string
    {
        return $this->view->render('helpers/price-group', [
            'entity' => $entity,
            'priceGroups' => $this->getModuleService('priceGroupService')->fetchAll()
        ]);
    }

    /**
     * Renders main grid
     * 
     * @return string
     */
    public function indexAction() : string
    {
        return $this->createForm(new InputDecorator());
    }

    /**
     * Renders edit form
     * 
     * @param int $id
     * @return string|boolean
     */
    public function editAction(int $id)
    {
        $priceGroup = $this->getModuleService('priceGroupService')->fetchById($id);

        if ($priceGroup) {
            return $this->createForm($priceGroup);
        } else {
            return false;
        }
    }

    /**
     * Deletes price group by its ID
     * 
     * @param int $id
     * @return void
     */
    public function deleteAction(int $id)
    {
        $this->getModuleService('priceGroupService')->deleteById($id);

        $this->flashBag->set('danger', 'The price group has been deleted successfully');
        $this->response->redirectToPreviousPage();
    }

    /**
     * Persist price group
     * 
     * @return int
     */
    public function saveAction()
    {
        $data = $this->request->getPost();
        $this->getModuleService('priceGroupService')->save($data);

        $this->flashBag->set('success', $data['id'] ? 'The price group has been updated successfully' : 'The price group has been added successfully');
        return 1;
    }
}
