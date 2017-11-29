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
use Site\Service\DaysCollection;

class Reservation extends AbstractCrmController
{
    /**
     * Creates a form
     * 
     * @param \Krystal\Db\Filter\InputDecorator|array $client
     * @param string $arrival
     * @param string $departure
     * @return string
     */
    private function createForm($client, $arrival = null, $departure = null)
    {
        // Load view plugins
        $this->view->getPluginBag()
                   ->load(array('chosen', 'datetimepicker'));

        return $this->view->render('reservation/form', array(
            'arrival' => $arrival,
            'departure' => $departure,
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
            'dailyTax' => $this->getHotelData()['daily_tax'],
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
        $period = $this->request->getQuery('period', 7);
        $type = $this->request->getQuery('type', null);

        $rooms = $this->createMapper('\Site\Storage\MySQL\ReservationMapper')->findReservations($type);

        return $this->view->render('reservation/table', array(
            'types' => $this->getModuleService('architectureService')->getRoomTypes($this->getHotelId()),
            'type' => $type,
            'rooms' => $rooms,
            'periods' => (new DaysCollection())->getAll(),
            'period' => $period,
            'dates' => ReservationService::createPeriodRange($period),
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
            'entity' => $entity
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
            'entity' => $entity
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
        $arrival = $this->request->getQuery('arrival');
        $departure = $this->request->getQuery('departure', null);

        if ($arrival != null) {
            // If not provided explicitly, then assume for one day
            if ($departure === null) {
                $departure = ReservationService::addOneDay($arrival);
            }

            $entity['arrival'] = $arrival;
            $entity['departure'] = $departure;

            return $this->createForm($entity);
        } else {
            $dates = ReservationService::getReservationDefaultDates();

            $entity['arrival'] = $dates['today'];
            $entity['departure'] = $dates['tomorrow'];

            return $this->createForm($entity);
        }
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
            $this->formAttribute->setOldAttribute('arrival', $entity['arrival']);

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
        $mapper = $this->createMapper('\Site\Storage\MySQL\ReservationMapper');

        $this->formAttribute->setNewAttributes($data);

        // Whether arrival checking needs to be done
        $hasChanged = $this->formAttribute->hasChanged('arrival') ? !$mapper->hasAvailability($data['arrival'], $data['room_id']) : false;

        $formValidator = $this->createValidator([
            'input' => [
                'source' => $data,
                'definition' => [
                    'full_name' => new Pattern\Name(),
                    'arrival' => [
                        'required' => true,
                        'rules' => [
                            'Unique' => [
                                'message' => 'Selected room is already reserved on provided arrival date',
                                'value' => $hasChanged ? !$mapper->hasAvailability($data['arrival'], $data['room_id']) : false
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        if ($formValidator->isValid()) {

            if (!empty($data['id'])) {
                $mapper->update($data);
            } else {
                $mapper->insert($data);
            }

            $this->flashBag->set('success', $data['id'] ? 'Reservation is updated' : 'Reservation is successful');
            return '1';
        } else {
            return $formValidator->getErrors();
        }
    }
}
