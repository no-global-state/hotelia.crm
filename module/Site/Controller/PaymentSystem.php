<?php

namespace Site\Controller;

use Krystal\Db\Filter\InputDecorator;

final class PaymentSystem extends AbstractCrmController
{
    /**
     * Renders payment system
     * 
     * @param mixed $paymentSystem
     * @return string
     */
    private function createForm($entity)
    {
        return $this->view->render('payment-system/index', [
            'entity' => $entity,
            'paymentSystems' => $this->getModuleService('paymentSystemService')->fetchAll()
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
     * @return string
     */
    public function editAction(int $id)
    {
        $paymentSystem = $this->getModuleService('paymentSystemService')->fetchById($id);

        if ($paymentSystem) {
            return $this->createForm($paymentSystem);
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
        $this->getModuleService('paymentSystemService')->deleteById($id);

        $this->flashBag->set('danger', 'Payment system has been removed successfully');
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

        $service = $this->getModuleService('paymentSystemService');
        $service->save($data);

        $this->flashBag->set('success', $data['id'] ? 'Payment system has been updated successfully' : 'Payment system has been added successfully');
        return 1;
    }
}
