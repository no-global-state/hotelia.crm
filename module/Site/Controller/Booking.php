<?php

namespace Site\Controller;

use Site\Collection\GenderCollection;
use Site\Service\BookingService;
use Krystal\Iso\ISO3166\Country;

final class Booking extends AbstractCrmController
{
    /**
     * Renders main grid
     * 
     * @return string
     */
    public function indexAction()
    {
        // Append one breadcrumb
        $this->view->getBreadcrumbBag()
                   ->addOne('Bookings from the site');

        return $this->view->render('booking/index', [
            'icon' => 'glyphicon glyphicon-envelope',
            'bookings' => $this->getModuleService('bookingService')->findAll($this->getHotelId())
        ]);
    }

    /**
     * Makes actual reservation
     * 
     * @return string
     */
    public function reserveAction()
    {
        // Grab POST data
        $data = $this->request->getPost();

        $bookingService = $this->getModuleService('bookingService');

        // Parse into required format
        $reservations = $bookingService->createReservationDetails($data['id'], $data['guest']);

        if ($reservations !== false) {
            // Update status as well
            $bookingService->updateStatusById($data['id'], BookingService::STATUS_CONFIRMED);

            // And finally, do save
            $this->getModuleService('reservationService')->saveMany($reservations);

            $this->flashBag->set('success', 'Reservation has been made');
            return 1;
        }
    }

    /**
     * Render details
     * 
     * @param int $id Booking ID
     * @return mixed
     */
    public function detailsAction(int $id)
    {
        $details = $this->getModuleService('bookingService')->findDetails($id, $this->getCurrentLangId());

        if ($details !== false) {
            // Append breadcrumbs
            $this->view->getBreadcrumbBag()
                       ->addOne('Bookings from the site', $this->createUrl('Site:Booking@indexAction'))
                       ->addOne('Booking details');

            return $this->view->render('booking/details', [
                'details' => $details,
                'icon' => 'glyphicon glyphicon-search',
                'rooms' => $this->getModuleService('roomService')->createRooms($this->getCurrentLangId(), $this->getHotelId()),

                // From collection
                'genders' => (new GenderCollection)->getAll(),
                'countries' => (new Country)->getAll()
            ]);

        } else {
            return false;
        }
    }
}
