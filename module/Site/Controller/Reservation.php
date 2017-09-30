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

class Reservation extends AbstractCrmController
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

        return $this->view->render('reservation/form', array(
            'arrival' => $arrival,
            'client' => $client,
            'services' => ArrayUtils::arrayList($this->createMapper('\Site\Storage\MySQL\RoomServiceMapper')->fetchAll($this->getHotelId()), 'id', 'name'),
            'rooms' => $this->getModuleService('architectureService')->createRooms($this->getHotelId()),
            'prices' => $this->getModuleService('architectureService')->getRoomPrices($this->getHotelId()),

            // Collections
            'countries' => (new Country())->getAll(),
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
        // Load view plugins
        $this->view->getPluginBag()
                   ->load('datetimepicker');

        $route = $this->createUrl('Site:Reservation@indexAction', [null]);

        $mapper = $this->createMapper('\Site\Storage\MySQL\ReservationMapper');
        $invoker = new FilterInvoker($query, $route);

        $data = $invoker->invoke($mapper, $this->getPerPageCount(), array(
            'leaving' => $this->request->getQuery('leaving'),
            'coming' => $this->request->getQuery('coming'),
            'type' => $this->request->getQuery('type'),
            'from' => $this->request->getQuery('from'),
            'to' => $this->request->getQuery('to'),
            'hotel_id' => $this->getHotelId()
        ));

        return $this->view->render('reservation/index', array(
            // Whether to show range filter or not
            'hideRange' => $this->request->hasQuery('leaving') || $this->request->hasQuery('coming'),
            'from' => $this->request->getQuery('from'),
            'to' => $this->request->getQuery('to'),
            'type' => $this->request->getQuery('type'),
            'hasFilter' => $this->request->hasQuery('filter'),
            'title' => $title,
            'route' => $route,
            'query' => $query,
            'data' => $data,
            'paginator' => $mapper->getPaginator(),
            'countries' => (new Country)->getAll(),
            'rooms' => $this->getModuleService('architectureService')->createRooms($this->getHotelId()),
            'showRooms' => $showRooms,
            'reservationCollection' => new ReservationCollection
        ));
    }

    /**
     * Find free rooms
     * 
     * @return string
     */
    public function findAction()
    {
        $service = $this->getModuleService('architectureService');

        if ($this->request->isPost()) {
            $formValidator = $this->createValidator([
                'input' => [
                    'source' => $this->request->getPost(),
                    'definition' => [
                        'arrival' => new Pattern\DateFormat('Y-m-d'),
                        'departure' => new Pattern\DateFormat('Y-m-d')
                    ]
                ]
            ]);

            if ($formValidator->isValid()) {
                // Request variables
                $arrival = $this->request->getPost('arrival');
                $departure = $this->request->getPost('departure');

                // Free rooms
                $rooms = $service->findFreeRooms($this->getHotelId(), $arrival, $departure, $this->request->getPost('types', []));

                return $this->view->disableLayout()->render('reservation/find-results', [
                    'rooms' => $rooms,
                    'arrival' => $arrival,
                    'departure' => $departure
                ]);

            } else {
                return $formValidator->getErrors();
            }

        } else {
            // Load view plugins
            $this->view->getPluginBag()
                       ->load('datetimepicker');

            return $this->view->render('reservation/find', [
                'client' => new InputDecorator(),
                'roomTypes' => $service->getRoomTypes($this->getHotelId())
            ]);
        }
    }

    /**
     * Renders the table
     * 
     * @return string
     */
    public function tableAction()
    {
        return $this->view->render('reservation/table', array(
            'table' => $this->getModuleService('architectureService')->createTable($this->getHotelId())
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
        $hotel = $this->getHotelData();

        return $this->view->disableLayout()->render('reservation/view', array(
            'entity' => $entity,
            'count' => ReservationService::createCount($entity, $hotel['daily_tax'])
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
        $hotel = $this->getHotelData();

        return $this->view->disableLayout()->render('reservation/view', array(
            'entity' => $entity,
            'count' => ReservationService::createCount($entity, $hotel['daily_tax'])
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
        $hotel = $this->getHotelData();

        return $this->view->render('reservation/print', array(
            'entity' => $entity,
            'count' => ReservationService::createCount($entity, $hotel['daily_tax'])
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
            return $this->createForm($entity, ReservationService::addOneDay($this->request->getQuery('arrival')));
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

            $this->flashBag->set('success', 'Reservation is successful');
            return '1';
        } else {
            return $formValidator->getErrors();
        }
    }
}
