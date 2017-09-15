<?php

namespace Site\Controller\Architecture;

use Site\Controller\AbstractCrmController;
use Krystal\Db\Filter\InputDecorator;
use Krystal\Validate\Pattern;
use Krystal\Stdlib\ArrayUtils;

class RoomInventory extends AbstractCrmController
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
     * Creates room inventory mapper
     * 
     * @return \Site\Storage\MySQL\RoomInventoryMapper
     */
    private function createRoomInventoryMapper()
    {
        return $this->createMapper('\Site\Storage\MySQL\RoomInventoryMapper');
    }

    /**
     * Creates a grid
     * 
     * @param string $roomId
     * @param \Krystal\Db\Filter\InputDecorator|array $entity
     * @return string
     */
    private function createGrid($roomId, $entity)
    {
        $collection = ArrayUtils::arrayList($this->createInventoryMapper()->fetchAll($this->getHotelId()), 'id', 'name');

        return $this->view->render('architecture/room-inventory', array(
            'inventories' => $this->createRoomInventoryMapper()->fetchAll($roomId),
            'entity' => $entity,
            'id' => $entity['id'],
            'collection' => $collection,
            'roomId' => $roomId
        ));
    }

    /**
     * Renders main grid
     * 
     * @param string $roomId
     * @return string
     */
    public function indexAction($roomId)
    {
        return $this->createGrid($roomId, new InputDecorator());
    }

    /**
     * Renders edit form
     * 
     * @param string $roomId
     * @param string $id
     * @return string
     */
    public function editAction($roomId, $id)
    {
        $entity = $this->createRoomInventoryMapper()->findByPk($id);

        if ($entity) {
            return $this->createGrid($roomId, $entity);
        } else {
            return false;
        }
    }

    /**
     * Deletes inventory
     * 
     * @param string $roomId
     * @param string $id
     * @return string
     */
    public function deleteAction($roomId, $id)
    {
        $this->createRoomInventoryMapper()->deleteByPk($id);
        return 1;
    }

    /**
     * Saves the inventory
     * 
     * @return string
     */
    public function saveAction()
    {
        $data = $this->request->getPost();
        $this->createRoomInventoryMapper()->persist($data);

        return 1;
    }
}
