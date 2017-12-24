<?php

namespace Site\Controller;

use Krystal\Db\Filter\InputDecorator;
use Krystal\Validate\Pattern;

final class Inventory extends AbstractCrmController
{
    /**
     * Creates inventory mapper
     * 
     * @return \Site\Storage\MySQL\InventoryMapper
     */
    private function createInventoryMapper()
    {
        return $this->createMapper('\Site\Storage\MySQL\InventoryMapper');
    }

    /**
     * Creates the grid
     * 
     * @param mixed $entity
     * @return string
     */
    private function createForm($entity) : string
    {
        return $this->view->render('inventory/index', array(
            'inventories' => $this->createInventoryMapper()->fetchAll($this->getHotelId()),
            'id' => $entity['id'],
            'entity' => $entity
        ));
    }

    /**
     * Renders inventory grid
     * 
     * @return string
     */
    public function indexAction() : string
    {
        return $this->createForm(new InputDecorator());
    }

    /**
     * Edits the inventory item by its ID
     * 
     * @param int $id Inventory ID
     * @return mixed
     */
    public function editAction(int $id)
    {
        $entity = $this->createInventoryMapper()->findByPk($id);

        if ($entity) {
            return $this->createForm($entity);
        } else {
            return false;
        }
    }

    /**
     * Saves the inventory
     * 
     * @return string
     */
    public function saveAction()
    {
        $data = $this->request->getPost();

        $formValidator = $this->createValidator(array(
            'input' => array(
                'source' => $data,
                'definition' => array(
                    'name' => new Pattern\Name
                )
            )
        ));

        if ($formValidator->isValid()) {
            $data = $this->getWithHotelId($data);
            $this->createInventoryMapper()->persist($data);

            $this->flashBag->set('success', $data['id'] ? 'The inventory has been updated successfully' : 'The inventory has been added successfully');
            return 1;

        } else {
            return $formValidator->getErrors();
        }
    }

    /**
     * Deletes an inventory
     * 
     * @param string $id
     * @return string
     */
    public function deleteAction(int $id) : void
    {
        $this->createInventoryMapper()->deleteByPk($id);

        $this->flashBag->set('danger', 'The inventory has been deleted successfully');
        $this->response->redirectToPreviousPage();
    }
}
