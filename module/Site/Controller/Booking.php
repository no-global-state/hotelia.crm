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
            'bookings' => $this->getModuleService('bookingService')->findAll()
        ]);
    }
}
