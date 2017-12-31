<?php

namespace Site\Controller;

use Krystal\Db\Filter\InputDecorator;
use Krystal\Stdlib\ArrayUtils;
use Site\Collection\UnitCollection;

final class ReservationService extends AbstractCrmController
{
    /**
     * Creates and renders grid
     * 
     * @param mixed $entity
     * @return string
     */
    private function createGrid($id, $entity) : string
    {
        $mapper = $this->createMapper('\Site\Storage\MySQL\ReservationServiceMapper');
        $services = $mapper->findAllByReservationId($id);

        $sum = ArrayUtils::columnSum($services, ['price']);

        return $this->view->render('reservation/services', [
            'reservationId' => $id,
            'currency' => $mapper->findCurrencyByReservationId($id),
            'services' => $services,
            'sum' => $sum['price'],
            'types' => $mapper->findOptionsByReservationId($id),
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
        $mapper = $this->createMapper('\Site\Storage\MySQL\ReservationServiceMapper');
        $mapper->deleteByPk($id);

        $this->flashBag->set('danger', 'Reservation service has been deleted successfully');
        $this->response->redirect($this->createUrl('Site:ReservationService@indexAction', [$reservationId]));
    }

    /**
     * Finds reservation service by its ID
     * 
     * @param int $id Reservation ID
     * @return string
     */
    public function editAction(int $id)
    {
        $mapper = $this->createMapper('\Site\Storage\MySQL\ReservationServiceMapper');
        $entity = $mapper->findByPk($id);

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
        $data = $this->request->getPost();

        // Append counted price
        $data['price'] = floatval($data['qty']) * floatval($data['rate']);

        $service = $this->createMapper('\Site\Storage\MySQL\ReservationServiceMapper');
        $service->persist($data);

        $this->flashBag->set('success', $data['id'] ? 'Reservation service has been updated successfully' : 'Reservation service has been added successfully');
        return 1;
    }
}
