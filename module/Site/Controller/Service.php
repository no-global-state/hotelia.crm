<?php

namespace Site\Controller;

use Krystal\Db\Filter\InputDecorator;
use Krystal\Validate\Pattern;
use Site\Collection\UnitCollection;

final class Service extends AbstractCrmController
{
    /**
     * Create service mapper
     * 
     * @return \Site\Storage\MySQL\RoomServiceMapper
     */
    private function createServiceMapper()
    {
        return $this->createMapper('\Site\Storage\MySQL\RoomServiceMapper');
    }

    /**
     * Creates the grid
     * 
     * @param \Krystal\Db\Filter\InputDecorator|array $entity
     * @return string
     */
    private function createGrid($entity) : string
    {
        return $this->view->render('services/index', array(
            'services' => $this->createServiceMapper()->fetchAll($this->getHotelId()),
            'entity' => $entity,
            'id' => $entity['id'],
            'unitCollection' => new UnitCollection
        ));
    }

    /**
     * Renders main grid
     * 
     * @return string
     */
    public function indexAction() : string
    {
        return $this->createGrid(new InputDecorator());
    }

    /**
     * Renders edit form
     * 
     * @param int $id Service ID 
     * @return mixed
     */
    public function editAction(int $id)
    {
        $entity = $this->createServiceMapper()->findByPk($id);

        if ($entity) {
            return $this->createGrid($entity);
        } else {
            return false;
        }
    }

    /**
     * Deletes a service
     * 
     * @param int $id Service ID 
     * @return string
     */
    public function deleteAction($id) : void
    {
        $this->createServiceMapper()->deleteByPk($id);

        $this->flashBag->set('danger', 'The service has been deleted successfully');
        $this->response->redirectToPreviousPage();
    }

    /**
     * Saves a service
     * 
     * @return string
     */
    public function saveAction() : int
    {
        $data = $this->request->getPost();
        $data = $this->getWithHotelId($data);

        $this->createServiceMapper()->persist($data);

        $this->flashBag->set('success', $data['id'] ? 'The service has been updated successfully' : 'The service has been added successfully');
        return 1;
    }
}
