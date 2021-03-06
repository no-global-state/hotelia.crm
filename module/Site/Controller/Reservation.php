<?php

namespace Site\Controller;

use Krystal\Iso\ISO3166\Country;
use Krystal\Validate\Pattern;
use Krystal\Stdlib\VirtualEntity;
use Krystal\Stdlib\ArrayUtils;
use Krystal\Db\Filter\InputDecorator;
use Krystal\Db\Filter\FilterInvoker;
use Site\Collection\ReservationCollection;
use Site\Collection\PurposeCollection;
use Site\Collection\PaymentTypeCollection;
use Site\Collection\LegalStatusCollection;
use Site\Collection\StatusCollection;
use Site\Collection\DaysCollection;
use Site\Collection\SourceCollection;
use Site\Collection\GenderCollection;
use Site\Service\ReservationService;

final class Reservation extends AbstractCrmController
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
        // Edit caption for breadcrumb
        $editTitle = $this->translator->translate('Edit reservation by "%s"', $client['full_name']);

        // Add a breadcrumb
        $this->view->getBreadcrumbBag()
                   ->addOne('Reservations', $this->createUrl('Site:Reservation@indexAction'))
                   ->addOne(is_array($client) ? $editTitle : 'New reservation');

        // Load view plugins
        $this->view->getPluginBag()
                   ->load(array('chosen', 'datetimepicker'));

        // Price group list
        $priceGroups = $this->createMapper('\Site\Storage\MySQL\PriceGroupMapper')->fetchAll(true);

        return $this->view->render('reservation/form', array(
            'icon' => 'glyphicon glyphicon-pencil',
            'arrival' => $arrival,
            'departure' => $departure,
            'client' => $client,
            'services' => $this->getModuleService('serviceManager')->fetchList($this->getHotelId()),

            // Price groups
            'priceGroupList' => ArrayUtils::arrayList($priceGroups, 'id', 'name'),
            'priceGroups' => $priceGroups,

            'rooms' => $this->getModuleService('roomService')->createRooms($this->getCurrentLangId(), $this->getHotelId(), $this->translator),
            'prices' => $this->getModuleService('roomTypeService')->findAllPrices($this->getHotelId()),
            'discounts' => $this->getModuleService('discountService')->createDiscounts($this->getHotelId(), $this->translator),

            // Collections
            'countries' => (new Country())->getAll(),
            'states' => (new ReservationCollection)->getAll(),
            'purposes' => (new PurposeCollection)->getAll(),
            'paymentSystems' => $this->getModuleService('paymentSystemService')->fetchList(),
            'legalStatuses' => (new LegalStatusCollection)->getAll(),
            'statuses' => (new StatusCollection)->getAll(),
            'sources' => (new SourceCollection())->getAll(),
            'genders' => (new GenderCollection)->getAll(),
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
        // Appends one breadcrumb
        $this->view->getBreadcrumbBag()
                   ->addOne('Clients');

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
            'icon' => 'glyphicon glyphicon-user',
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
            'rooms' => $this->getModuleService('roomService')->createRooms($this->getCurrentLangId(), $this->getHotelId(), $this->translator),
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
        $service = $this->getModuleService('roomService');

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
                $rooms = $service->findFreeRooms(
                    $this->getCurrentLangId(),
                    $this->getHotelId(), 
                    $arrival, 
                    $departure, 
                    $this->request->getPost('types', []),
                    $this->request->getPost('inventories', [])
                );

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

            // Add a breadcrumb
            $this->view->getBreadcrumbBag()
                       ->addOne('Find a free room');

            return $this->view->render('reservation/find', [
                'icon' => 'glyphicon glyphicon-search',
                'client' => new InputDecorator(),
                'roomTypes' => $service->getRoomTypes($this->getCurrentLangId(), $this->getHotelId()),
                'inventories' => ArrayUtils::arrayList($this->createMapper('\Site\Storage\MySQL\InventoryMapper')->fetchAll($this->getHotelId()), 'id', 'name')
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
        // Append a breadcrumb
        $this->view->getBreadcrumbBag()
                   ->addOne('Desktop');

        $period = $this->request->getQuery('period', DaysCollection::PARAM_MONTH);
        $type = $this->request->getQuery('type', null);

        $rooms = $this->getModuleService('reservationService')->fetchReservations($this->getHotelId(), $this->getCurrentLangId(), $type);

        return $this->view->render('reservation/table', array(
            'types' => $this->getModuleService('roomService')->getRoomTypes($this->getCurrentLangId(), $this->getHotelId()),
            'type' => $type,
            'rooms' => $rooms,
            'periods' => (new DaysCollection())->getAll(),
            'period' => $period,
            'dates' => ReservationService::createPeriodRange($period),
            'table' => $this->getModuleService('roomService')->createTable($this->getCurrentLangId(), $this->getHotelId()),
            'icon' => 'glyphicon glyphicon-blackboard'
        ));
    }

    /**
     * View info by associated room ID
     * 
     * @param int $roomId
     * @return string
     */
    public function viewTakenAction(int $roomId) : string
    {
        $entity = $this->getModuleService('reservationService')->fetchByRoomId($roomId);

        return $this->view->disableLayout()->render('reservation/view', [
            'entity' => $entity
        ]);
    }

    /**
     * Views reservation info
     * 
     * @param string $id
     * @return string
     */
    public function viewAction($id)
    {
        $entity = $this->getModuleService('reservationService')->fetchById($id, $this->getCurrentLangId());

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
        // Append breadcrumbs
        $this->view->getBreadcrumbBag()
                   ->addOne('Reservations', $this->createUrl('Site:Reservation@indexAction'))
                   ->addOne($this->translator->translate('Invoice') . ' # ' . $id);
        
        $entity = $this->getModuleService('reservationService')->fetchById($id, $this->getCurrentLangId());

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
        return $this->createGrid($this->request->getQuery(), 'Clients', true);
    }

    /**
     * Deletes a reservation
     * 
     * @param string $id
     * @return string
     */
    public function deleteAction($id)
    {
        $this->getModuleService('reservationService')->deleteById($id);

        $this->flashBag->set('danger', 'Selected reservation has been removed successfully');
        return $this->response->redirect($this->createUrl('Site:Reservation@indexAction', [null]));
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
    public function editAction(int $id)
    {
        $entity = $this->getModuleService('reservationService')->fetchById($id);

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
        $data = $this->getWithHotelId($data);

        $service = $this->getModuleService('reservationService');

        $this->formAttribute->setNewAttributes($data);

        // Whether arrival checking needs to be done
        if ($data['id']) {
            $hasChanged = $this->formAttribute->hasChanged('arrival') ? $service->hasAvailability($data['arrival'], $data['room_id']) : false;
        } else {
            $hasChanged = true;
        }

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
                                'value' => $hasChanged ? !$service->hasAvailability($data['arrival'], $data['room_id']) : false,
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        if ($formValidator->isValid()) {
            $service->save($data);

            $this->flashBag->set('success', $data['id'] ? 'Reservation is updated' : 'Reservation is successful');
            return '1';
        } else {
            return $formValidator->getErrors();
        }
    }
}
