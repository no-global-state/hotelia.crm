<?php

namespace Site\Controller\Architecture;

use Site\Controller\AbstractCrmController;
use Krystal\Db\Filter\InputDecorator;
use Krystal\Validate\Pattern;
use Krystal\Stdlib\ArrayUtils;

final class RoomInventory extends AbstractCrmController
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
     * @param int $roomId
     * @param \Krystal\Db\Filter\InputDecorator|array $entity
     * @return string
     */
    private function createGrid(int $roomId, $entity) : string
    {
        // Room name
        $name = $this->createMapper('\Site\Storage\MySQL\RoomMapper')->fetchNameById($roomId);

        // Append breadcrumbs
        $this->view->getBreadcrumbBag()->addOne('Architecture', $this->createUrl('Site:Architecture:Room@indexAction'))
                                       ->addOne($this->translator->translate('Room inventory') . ' - ' . $name);

        $collection = ArrayUtils::arrayList($this->createInventoryMapper()->fetchAll($this->getHotelId()), 'id', 'name');
        $inventories = $this->createRoomInventoryMapper()->fetchAll($roomId);

        return $this->view->render('room/inventory', array(
            'inventories' => $inventories,
            'count' => count($inventories),
            'entity' => $entity,
            'id' => $entity['id'],
            'collection' => $collection,
            'roomId' => $roomId,
            'icon' => 'glyphicon glyphicon-eject'
        ));
    }

    /**
     * Renders main grid by room ID
     * 
     * @param int $roomId
     * @return string
     */
    public function indexAction(int $roomId) : string
    {
        return $this->createGrid($roomId, new InputDecorator());
    }

    /**
     * Renders edit form by inventory ID
     * 
     * @param int $roomId Room ID
     * @param int $id Inventory ID
     * @return mixed
     */
    public function editAction(int $roomId, int $id)
    {
        $entity = $this->createRoomInventoryMapper()->findByPk($id);

        if ($entity) {
            return $this->createGrid($roomId, $entity);
        } else {
            return false;
        }
    }

    /**
     * Deletes inventory by its ID
     * 
     * @param int $roomId Room ID
     * @param int $id Inventory ID
     * @return void
     */
    public function deleteAction(int $roomId, int $id) : void
    {
        $this->createRoomInventoryMapper()->deleteByPk($id);

        $this->flashBag->set('danger', 'The inventory has been deleted successfully');
        $this->response->redirectToPreviousPage();
    }

    /**
     * Saves the inventory
     * 
     * @return int
     */
    public function saveAction() : int
    {
        $data = $this->request->getPost();

        $this->createRoomInventoryMapper()->persist($data);

        $this->flashBag->set('success', $data['id'] ? 'The inventory has been updated successfully' : 'The inventory has been added successfully');
        return 1;
    }
}
