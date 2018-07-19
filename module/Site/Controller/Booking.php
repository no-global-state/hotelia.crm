<?php

namespace Site\Controller;

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
     * Render details
     * 
     * @param int $id Booking ID
     * @return mixed
     */
    public function detailsAction($id)
    {
        $details = $this->getModuleService('bookingService')->findDetails($id, $this->getCurrentLangId());

        if ($details !== false) {
            // Append breadcrumbs
            $this->view->getBreadcrumbBag()
                       ->addOne('Bookings from the site', $this->createUrl('Site:Booking@indexAction'))
                       ->addOne('Booking details');

            return $this->view->render('booking/details', [
                'details' => $details,
                'icon' => 'glyphicon glyphicon-search'
            ]);

        } else {
            return false;
        }
    }
}
