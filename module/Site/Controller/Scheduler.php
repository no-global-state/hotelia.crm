<?php

namespace Site\Controller;

use Krystal\Iso\ISO3166\Country;
use Krystal\Stdlib\ArrayUtils;
use Site\Collection\GenderCollection;
use Site\Collection\SourceCollection;
use Site\Collection\StatusCollection;
use Site\Collection\LegalStatusCollection;
use Site\Collection\PurposeCollection;
use Site\Collection\ReservationCollection;

final class Scheduler extends AbstractCrmController
{
    /**
     * Returns shared vars
     * 
     * @param \Krystal\Db\Filter\InputDecorator|array $client
     * @return array
     */
    private function getReservationParams($client) : array
    {
        // Price group list
        $priceGroups = $this->createMapper('\Site\Storage\MySQL\PriceGroupMapper')->fetchAll(true);

        return [
            'client' => $client,
            'services' => $this->getModuleService('serviceManager')->fetchList($this->getHotelId()),

            // Price groups
            'priceGroupList' => ArrayUtils::arrayList($priceGroups, 'id', 'name'),
            'priceGroups' => $priceGroups,

            'rooms' => $this->getModuleService('roomService')->createRooms($this->getCurrentLangId(), $this->getHotelId()),
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
        ];
    }

    /**
     * Create modal
     * 
     * @param array $vars
     * @return string
     */
    private function createModal(array $vars = []) : string
    {
        $v =& $this->view;

        // Configure view
        $v->setLayout('layouts/dialog')
          ->getPluginBag()
          ->appendStylesheet('@Site/daypilot/media/layout.css')
          ->load(['chosen', 'datetimepicker']);

        return $v->render('chessboard/modal', $vars);
    }

    /**
     * Deletes a reservation
     * 
     * @return boolean
     */
    public function delete() : string
    {
        // Request param
        $id = $this->request->getPost('id');

        $this->getModuleService('reservationService')->deleteById($id);

        return $this->json([
            'result' => 'OK',
            'message' => 'Delete successful'
        ]);
    }

    /**
     * Edits the event
     * 
     * @return string
     */
    public function edit() : string
    {
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $data = $this->getWithHotelId($data);

            $this->getModuleService('reservationService')->save($data);

            return $this->json([
                'result' => 'OK',
                'message' => 'Updated',
            ]);

        } else {
            $id = $this->request->getQuery('id');
            $entity = $this->getModuleService('reservationService')->fetchById($id);

            return $this->createModal($this->getReservationParams($entity));
        }
    }

    /**
     * Renders adding dialog
     * 
     * @return string
     */
    public function add() : string
    {
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $data = $this->getWithHotelId($data);

            // Save and grab last ID
            $id = $this->getModuleService('reservationService')->save($data);

            return $this->json([
                'result' => 'OK',
                'message' => 'created',
                'id' => $id
            ]);

        } else {
            // Empty entity
            $entity = $this->getModuleService('scheduleService')->createEntity(
                $this->request->getQuery('room_id'), 
                $this->request->getQuery('arrival'), 
                $this->request->getQuery('departure')
            );

            return $this->createModal($this->getReservationParams($entity));
        }
    }

    /**
     * Returns rooms
     * 
     * @return string
     */
    public function rooms() : string
    {
        // Optional type ID filter
        $typeId = $this->request->getPost('type_id', null);

        $rooms = $this->getModuleService('roomService')->findAll($this->getCurrentLangId(), $this->getHotelId(), $typeId);
        return $this->json($rooms);
    }

    /**
     * Moves an event
     * 
     * @return string
     */
    public function move() : string
    {
        $service = $this->getModuleService('scheduleService');

        if ($service->move(
                // Request variables
                $this->request->getPost('id'), 
                $this->request->getPost('room_id'), 
                $this->request->getPost('arrival'),
                $this->request->getPost('departure')
            )) {
            $output = [
                'result' => 'OK',
                'message' => 'Update successful'
            ];
        } else {
            $output = [
                'result' => 'Error',
                'message' => 'This reservation overlaps with an existing reservation.'
            ];
        }

        return $this->json($output);
    }

    /**
     * Resizes an event
     * 
     * @return string
     */
    public function resize() : string
    {
        $service = $this->getModuleService('scheduleService')->resize(
            // Request variables
            $this->request->getPost('id'), 
            $this->request->getPost('arrival'), 
            $this->request->getPost('departure')
        );

        return $this->json([
            'result' => 'OK',
            'message' => 'Resized'
        ]);
    }

    /**
     * Returns events
     * 
     * @return string
     */
    public function events() : string
    {
        $events = $this->getModuleService('scheduleService')->findEvents(
            // Request variables
            $this->getHotelId(), 
            $this->request->getPost('arrival'), 
            $this->request->getPost('departure')
        );

        return $this->json($events);
    }
}
