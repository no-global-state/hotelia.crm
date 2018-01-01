<?php

namespace Site\Controller;

use Krystal\Db\Filter\InputDecorator;
use Krystal\Validate\Pattern;

final class Discount extends AbstractCrmController
{
    /**
     * Renders a form
     * 
     * @param mixed $entity
     * @return string
     */
    private function createForm($entity) : string
    {
        return $this->view->render('helpers/discount', [
            'discounts' => $this->getModuleService('discountService')->fetchAll($this->getHotelId()),
            'entity' => $entity
        ]);
    }

    /**
     * Renders discount grid
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
     * @return string
     */
    public function editAction(int $id)
    {
        $discount = $this->getModuleService('discountService')->fetchById($id);

        if ($discount) {
            return $this->createForm($discount);
        } else {
            return false;
        }
    }

    /**
     * Removes a discount by its id
     * 
     * @param int $id
     * @return void
     */
    public function deleteAction(int $id) : void
    {
        $service = $this->getModuleService('discountService');
        $service->deleteById($id);

        $this->flashBag->set('danger', 'Discount has been removed successfully');
        $this->response->redirectToPreviousPage();
    }

    /**
     * Persists data
     * 
     * @return string
     */
    public function saveAction()
    {
        $data = $this->request->getPost();
        $data = $this->getWithHotelId($data);

        $formValidator = $this->createValidator(array(
            'input' => array(
                'source' => $data,
                'definition' => array(
                    'name' => new Pattern\Name,
                    'percentage' => new Pattern\Percentage
                )
            )
        ));

        if ($formValidator->isValid()) {
            $service = $this->getModuleService('discountService');
            $service->save($data);

            $this->flashBag->set('success', $data['id'] ? 'Discount has been updated successfully' : 'Discount has been added successfully');
            return 1;
        } else {
            return $formValidator->getErrors();
        }
    }
}
