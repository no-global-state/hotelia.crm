<?php

namespace Site\Controller;

use Krystal\Db\Filter\InputDecorator;
use Site\Collection\UnitCollection;

final class ReservationService extends AbstractCrmController
{
    /**
     * Creates and renders grid
     * 
     * @param int $id
     * @param mixed $entity
     * @return string
     */
    private function createGrid(int $id, $entity) : string
    {
        $reservationServiceManager = $this->getModuleService('reservationServiceManager');
        $services = $reservationServiceManager->findAllByReservationId($id);

        return $this->view->render('reservation/services', [
            'fullName' => $this->createMapper('\Site\Storage\MySQL\ReservationMapper')->findFullNameById($id),
            'reservationId' => $id,
            'currency' => $reservationServiceManager->findCurrencyByReservationId($id),
            'services' => $services['services'],
            'sum' => $services['sum'],
            'types' => $reservationServiceManager->findOptionsByReservationId($id),
            'entity' => $entity,
            'unitCollection' => new UnitCollection()
        ]);
    }

    /**
     * Render reservation services
     * 
     * @param int $id Reservation ID
     * @return string
     */
    public function indexAction(int $id) : string
    {
        return $this->createGrid($id, new InputDecorator());
    }

    /**
     * Removes reservation service by its ID
     * 
     * @param int $reservationId Reservation ID
     * @param int $id
     * @return string
     */
    public function deleteAction(int $reservationId, int $id) : void
    {
        $reservationServiceManager = $this->getModuleService('reservationServiceManager');
        $reservationServiceManager->deleteById($id);

        $this->flashBag->set('danger', 'Reservation service has been deleted successfully');
        $this->response->redirect($this->createUrl('Site:ReservationService@indexAction', [$reservationId]));
    }

    /**
     * Finds reservation service by its ID
     * 
     * @param int $id Reservation ID
     * @return mixed
     */
    public function editAction(int $id)
    {
        $entity = $this->getModuleService('reservationServiceManager')->fetchById($id);

        if ($entity) {
            return $this->createGrid($entity['master_id'], $entity);
        } else {
            return false;
        }
    }

    /**
     * Saves reservation service
     * 
     * @return mixed
     */
    public function saveAction()
    {
        // Request data
        $data = $this->request->getPost();

        $reservationServiceManager = $this->getModuleService('reservationServiceManager');
        $reservationServiceManager->save($data);

        $this->flashBag->set('success', $data['id'] ? 'Reservation service has been updated successfully' : 'Reservation service has been added successfully');
        return 1;
    }
}
