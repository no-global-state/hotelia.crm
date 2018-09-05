<?php

namespace Site\Controller;

use Site\Collection\GenderCollection;
use Site\Collection\BookingStatusCollection;
use Site\Service\BookingService;
use Krystal\Iso\ISO3166\Country;

final class Booking extends AbstractCrmController
{
    use MailerTrait;

    /**
     * Creates the grid
     * 
     * @param boolean $all
     * @param string $action
     * @param int $page Current page number
     * @return string
     */
    private function createGrid(bool $all, string $action, $page) : string
    {
        if (!$page) {
            $page = 1;
        }

        $perPageCount = 15; // Default per page count

        $route = $this->createUrl(sprintf('Site:Booking@%s', $action), []);

        // Grab the service
        $service = $this->getModuleService('bookingService');

        if ($all == true) {
            $bookings = $service->findShared($this->getCurrentLangId(), $page, $perPageCount);
        } else {
            $bookings = $service->findAll($this->getHotelId(), $page, $perPageCount);
        }

        // Configure paginator
        $paginator = $service->getPaginator();
        $paginator->setUrl($route);

        // Append one breadcrumb
        $this->view->getBreadcrumbBag()
                   ->addOne('Bookings from the site');

        return $this->view->render('booking/index', [
            'icon' => 'glyphicon glyphicon-envelope',
            'bookings' => $bookings,
            'all' => $all,
            'count' => $paginator->getTotalAmount(),
            'paginator' => $paginator
        ]);
    }

    /**
     * Renders all bookings
     * 
     * @param int $page Current page number
     * @return string
     */
    public function allAction($page = 1) : string
    {
        return $this->createGrid(true, __FUNCTION__, $page);
    }

    /**
     * Renders main grid
     * 
     * @param int $page Current page number
     * @return string
     */
    public function indexAction($page = 1) : string
    {
        return $this->createGrid(false, __FUNCTION__, $page);
    }

    /**
     * Update booking status
     * 
     * @return int
     */
    public function updateStatusAction() : int
    {
        $data = $this->request->getPost();

        $bookingService = $this->getModuleService('bookingService');
        $bookingService->updateStatusById($data['id'], $data['status']);

        $this->flashBag->set('success', 'Booking status has been updated successfully');
        return 1;
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
        $data = $bookingService->createReservationDetails($data['id'], $data['guest']);

        if ($data !== false) {
            // Update status as well
            $bookingService->updateStatusById($data['booking']['id'], BookingStatusCollection::STATUS_ACCEPTED);

            // And finally, do save
            $ids = $this->getModuleService('reservationService')->saveMany($data['reservations']);

            $bookingService->insertRelation($bookingService->getLastId(), $ids);

            $this->flashBag->set('success', 'Reservation has been made');
            return 1;
        }
    }

    /**
     * Deletes booking info by its ID
     * 
     * @param int $id Booking ID
     * @return void
     */
    public function deleteAction(int $id)
    {
        $this->getModuleService('bookingService')->deleteById($id);

        $this->flashBag->set('success', 'Selected reservation has been removed successfully');
        $this->response->redirectToPreviousPage();
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
                       ->addOne('Bookings from the site', $this->createUrl('Site:Booking@indexAction', [null]))
                       ->addOne('Booking details');

            return $this->view->render('booking/details', [
                'details' => $details,
                'icon' => 'glyphicon glyphicon-search',
                'rooms' => $this->getModuleService('roomService')->createRooms($this->getCurrentLangId(), $this->getHotelId(), $this->translator),

                // From collection
                'genders' => (new GenderCollection)->getAll(),
                'countries' => (new Country)->getAll()
            ]);

        } else {
            return false;
        }
    }
}
