<?php

namespace Site\Controller;

use Krystal\Db\Filter\InputDecorator;
use Krystal\Validate\Pattern;
use Site\Collection\UnitCollection;

final class Service extends AbstractCrmController
{
    /**
     * Creates the grid
     * 
     * @param \Krystal\Db\Filter\InputDecorator|array $entity
     * @return string
     */
    private function createGrid($entity) : string
    {
        return $this->view->render('services/index', array(
            'services' => $this->getModuleService('serviceManager')->fetchAll($this->getHotelId()),
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
        $entity = $this->getModuleService('serviceManager')->fetchById($id);

        if ($entity) {
            return $this->createGrid($entity);
        } else {
            return false;
        }
    }

    /**
     * Deletes a service by its id
     * 
     * @param int $id Service ID 
     * @return string
     */
    public function deleteAction(int $id) : void
    {
        $this->getModuleService('serviceManager')->deleteById($id);

        $this->flashBag->set('danger', 'The service has been deleted successfully');
        $this->response->redirectToPreviousPage();
    }

    /**
     * Saves a service
     * 
     * @return string
     */
    public function saveAction()
    {
        $data = $this->request->getPost();
        $data = $this->getWithHotelId($data);

        $formValidator = $this->createValidator([
            'input' => [
                'source' => $data,
                'definition' => [
                    'name' => new Pattern\Name,
                    'price' => new Pattern\Price
                ]
            ]
        ]);

        if ($formValidator->isValid()) {
            $this->getModuleService('serviceManager')->save($data);

            $this->flashBag->set('success', $data['id'] ? 'The service has been updated successfully' : 'The service has been added successfully');
            return 1;
        } else {
            return $formValidator->getErrors();
        }
    }
}
