<?php

namespace Site\Controller;

use Krystal\Db\Filter\InputDecorator;
use Krystal\Validate\Pattern;

class Inventory extends AbstractCrmController
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
     * @param array $entity
     * @return string
     */
    private function createGrid($entity)
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
    public function indexAction()
    {
        return $this->createGrid(new InputDecorator());
    }

    /**
     * Edits the inventory item
     * 
     * @param string $id
     * @return string
     */
    public function editAction($id)
    {
        $entity = $this->createInventoryMapper()->findByPk($id);

        if ($entity) {
            return $this->createGrid($entity);
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
            $this->createInventoryMapper()->persist($data);
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
    public function deleteAction($id)
    {
        $this->createInventoryMapper()->deleteByPk($id);
        return 1;
    }
}
