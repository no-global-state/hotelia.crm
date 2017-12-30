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
     * @param array $priceGroups
     * @return string
     */
    private function createGrid($entity, array $priceGroups) : string
    {
        return $this->view->render('services/index', array(
            'services' => $this->getModuleService('serviceManager')->fetchAll($this->getHotelId()),
            'entity' => $entity,
            'id' => $entity['id'],
            'unitCollection' => new UnitCollection,
            'priceGroups' => $priceGroups
        ));
    }

    /**
     * Renders main grid
     * 
     * @return string
     */
    public function indexAction() : string
    {
        $priceGroups = $this->createMapper('\Site\Storage\MySQL\PriceGroupMapper')->fetchAll(false);
        return $this->createGrid(new InputDecorator(), $priceGroups);
    }

    /**
     * Renders edit form
     * 
     * @param int $id Service ID 
     * @return mixed
     */
    public function editAction(int $id)
    {
        $service = $this->getModuleService('serviceManager');
        $entity = $this->getModuleService('serviceManager')->fetchById($id);

        if ($entity) {
            $priceGroups = $this->createMapper('\Site\Storage\MySQL\PriceGroupMapper')->fetchAll(false);
            $priceGroups = array_replace_recursive($priceGroups, $service->findPricesServiceId($id));

            return $this->createGrid($entity, $priceGroups);
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
                    'name' => new Pattern\Name
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
