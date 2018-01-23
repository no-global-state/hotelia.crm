<?php

namespace Site\Controller;

use Krystal\Db\Filter\InputDecorator;

final class PaymentField extends AbstractCrmController
{
    /**
     * Renders payment field
     * 
     * @param mixed $entity
     * @return string
     */
    private function createForm($entity) : string
    {
        return $this->view->render('helpers/payment-field', [
            'name' => $this->getModuleService('paymentSystemService')->findNameById($entity['payment_system_id']),
            'entity' => $entity,
            'fields' => $this->getModuleService('paymentFieldService')->findAllByPaymentSystemId($entity['payment_system_id'])
        ]);
    }

    /**
     * Renders main grid
     * 
     * @param int $id
     * @return string
     */
    public function indexAction(int $id) : string
    {
        $field = new InputDecorator();
        $field['payment_system_id'] = $id;

        return $this->createForm($field);
    }

    /**
     * Renders edit form
     * 
     * @param int $id
     * @return string
     */
    public function editAction(int $id)
    {
        $field = $this->getModuleService('paymentFieldService')->fetchById($id);

        if ($field) {
            return $this->createForm($field);
        } else {
            return false;
        }
    }

    /**
     * Renders edit form
     * 
     * @param int $id
     * @return void
     */
    public function deleteAction(int $id) : void
    {
        $this->getModuleService('paymentFieldService')->deleteById($id);

        $this->flashBag->set('danger', 'Payment field has been removed successfully');
        $this->response->redirectToPreviousPage();
    }

    /**
     * Saves payment system
     * 
     * @return int
     */
    public function saveAction() : int
    {
        $data = $this->request->getPost();

        $service = $this->getModuleService('paymentFieldService');
        $service->save($data);

        $this->flashBag->set('success', $data['id'] ? 'Payment field has been updated successfully' : 'Payment field has been added successfully');
        return 1;
    }
}
