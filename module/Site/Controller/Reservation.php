<?php

namespace Site\Controller;

use Krystal\Iso\ISO3166\Country;
use Krystal\Validate\Pattern;
use Krystal\Stdlib\VirtualEntity;
use Krystal\Stdlib\ArrayUtils;
use Krystal\Db\Filter\InputDecorator;
use Krystal\Db\Filter\FilterInvoker;
use Site\Service\ReservationCollection;
use Site\Service\PurposeCollection;
use Site\Service\PaymentTypeCollection;
use Site\Service\LegalStatusCollection;
use Site\Service\StatusCollection;
use Site\Service\ReservationService;

class Reservation extends AbstractSiteController
{
    /**
     * Creates a form
     * 
     * @param \Krystal\Db\Filter\InputDecorator|array $client
     * @param string $arrival
     * @return string
     */
    private function createForm($client, $arrival = null)
    {
        // Load view plugins
        $this->view->getPluginBag()
                   ->load(array('chosen', 'datetimepicker'));

        $this->loadApp();

        return $this->view->render('reservation/form', array(
            'arrival' => $arrival,
            'client' => $client,
            'countries' => (new Country())->getAll(),
            'services' => ArrayUtils::arrayList($this->createMapper('\Site\Storage\MySQL\RoomServiceMapper')->fetchAll(), 'id', 'name'),
            'rooms' => $this->createRooms(),
            'prices' => ArrayUtils::arrayList($this->createMapper('\Site\Storage\MySQL\RoomMapper')->fetchPrices(), 'id', 'unit_price'),
            // Collections
            'states' => (new ReservationCollection)->getAll(),
            'purposes' => (new PurposeCollection)->getAll(),
            'paymentTypes' => (new PaymentTypeCollection)->getAll(),
            'legalStatuses' => (new LegalStatusCollection)->getAll(),
            'statuses' => (new StatusCollection)->getAll(),
            'genders' => array(
                'M' => 'Male',
                'F' => 'Female'
            )
        ));
    }

    /**
     * Renders the grid
     * 
     * @param array $query
     * @param string $title
     * @param boolean $showRooms Whether to render room column
     * @return string
     */
    private function createGrid(array $query, $title, $showRooms)
    {
        $this->loadApp();

        $route = '/reservation/index/';

        $mapper = $this->createMapper('\Site\Storage\MySQL\ReservationMapper');
        $countries = new Country();

        $invoker = new FilterInvoker($query, $route);
        $data = $invoker->invoke($mapper, 20);

        return $this->view->render('reservation/index', array(
            'title' => $title,
            'route' => $route,
            'query' => $this->request->getQuery(),
            'data' => $data,
            'paginator' => $mapper->getPaginator(),
            'countries' => $countries->getAll(),
            'rooms' => $this->createRooms(),
            'showRooms' => $showRooms,
            'reservationCollection' => new ReservationCollection
        ));
    }

    /**
     * @return array
     */
    private function createTable()
    {
        $output = array();

        $roomMapper = $this->createMapper('\Site\Storage\MySQL\RoomMapper');
        $floorMapper = $this->createMapper('\Site\Storage\MySQL\FloorMapper');

        foreach ($floorMapper->fetchAll() as $floor) {
            $floor['rooms'] = $roomMapper->fetchAll($floor['id']);
            $output[] = $floor;
        }

        return $output;
    }

    /**
     * Create rooms
     * 
     * @return array
     */
    private function createRooms()
    {
        $output = array();
        $rows = $this->createTable();

        foreach ($rows as $row) {
            $output[$row['name']] = ArrayUtils::arrayList($row['rooms'], 'id', 'name');
        }

        return $output;
    }

    /**
     * Renders the table
     * 
     * @return string
     */
    public function tableAction()
    {
        $this->loadApp();

        return $this->view->render('reservation/table', array(
            'table' => $this->createTable()
        ));
    }

    /**
     * View info by associated room ID
     * 
     * @param string $roomId
     * @return string
     */
    public function viewTakenAction($roomId)
    {
        $entity = $this->createMapper('\Site\Storage\MySQL\ReservationMapper')->fetchByRoomId($roomId);

        return $this->view->disableLayout()->render('reservation/view', array(
            'entity' => $entity
        ));
    }

    /**
     * Views reservation info
     * 
     * @param string $id
     * @return string
     */
    public function viewAction($id)
    {
        $entity = $this->createMapper('\Site\Storage\MySQL\ReservationMapper')->fetchById($id);

        return $this->view->disableLayout()->render('reservation/view', array(
            'entity' => $entity,
            'count' => ReservationService::createCount($entity['arrival'], $entity['departure'], $entity['room_price'], $entity['discount'])
        ));
    }

    /**
     * Renders print-able reservation data
     * 
     * @param string $id Reservation id
     * @return string
     */
    public function printAction($id)
    {
        $entity = $this->createMapper('\Site\Storage\MySQL\ReservationMapper')->fetchById($id);

        return $this->view->render('reservation/print', array(
            'entity' => $entity,
            'count' => ReservationService::createCount($entity['arrival'], $entity['departure'], $entity['room_price'], $entity['discount'])
        ));
    }

    /**
     * Renders history of the room by its ID
     * 
     * @param string $id Room ID
     * @return string
     */
    public function historyAction($id)
    {
        $query = $this->request->getQuery();
        $query['filter']['room_id'] = $id;

        $title = $this->translator->translate('Browse by room - %s', $this->createMapper('\Site\Storage\MySQL\RoomMapper')->fetchNameById($id));

        return $this->createGrid($query, $title, false);
    }

    /**
     * Renders main grid
     * 
     * @return string
     */
    public function indexAction()
    {
        return $this->createGrid($this->request->getQuery(), 'A list of guest', true);
    }

    /**
     * Deletes a reservation
     * 
     * @param string $id
     * @return string
     */
    public function deleteAction($id)
    {
        $mapper = $this->createMapper('\Site\Storage\MySQL\ReservationMapper');
        $mapper->deleteById($id);

        $this->flashBag->set('danger', 'Selected reservation has been removed successfully');

        return $this->redirectToRoute('Site:Reservation@indexAction');
    }

    /**
     * Renders adding form
     * 
     * @return string
     */
    public function addAction()
    {
        $entity = new InputDecorator();

        // Defaults
        $entity['legal_status'] = 1;
        $entity['room_id'] = $this->request->getQuery('room_id');

        if ($this->request->hasQuery('arrival')) {
            $date = new \DateTime($this->request->getQuery('arrival'));
            $date->modify('+1 day');

            return $this->createForm($entity, $date->format('Y-m-d'));
        }

        return $this->createForm($entity);
    }

    /**
     * Edits a reservation
     * 
     * @param string $id
     * @return string
     */
    public function editAction($id)
    {
        $mapper = $this->createMapper('\Site\Storage\MySQL\ReservationMapper');
        $entity = $mapper->fetchById($id);

        if ($entity) {
            // Append IDs
            $entity['service_ids'] = array_column($entity['services'], 'id');

            return $this->createForm($entity);
        } else {
            return false;
        }
    }

    /**
     * Saves a reservation
     * 
     * @return string
     */
    public function saveAction()
    {
        $data = $this->request->getPost();

        $formValidator = $this->createValidator(array(
            'input' => array(
                'source' => $data,
                'definition' => array(
                    'full_name' => new Pattern\Name(),
                )
            )
        ));

        if ($formValidator->isValid()) {
            $mapper = $this->createMapper('\Site\Storage\MySQL\ReservationMapper');

            if (!empty($data['id'])) {
                $mapper->update($data);
            } else {
                $mapper->insert($data);
            }

            $this->flashBag->set('success', 'Your request has been sent!');
            return '1';
        } else {
            return $formValidator->getErrors();
        }
    }
}
