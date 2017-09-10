<?php

namespace Site\Controller;

use Krystal\Db\Filter\InputDecorator;
use Krystal\Validate\Pattern;

final class Hotel extends AbstractSiteController
{
    /**
     * Renders the form
     * 
     * @return string
     */
    public function indexAction()
    {
        $mapper = $this->createMapper('/Site/Storage/MySQL/HotelMapper');
        $hotel = $mapper->findByPk($this->getHotelId());

        return $this->view->render('hotel/form', array(
            'hotel' => new InputDecorator($hotel ? $hotel : array())
        ));
    }

    /**
     * Save form data
     * 
     * @return string
     */
    public function saveAction()
    {
        $data = $this->request->getPost();

        $mapper = $this->createMapper('/Site/Storage/MySQL/HotelMapper');
        $mapper->persist($data);

        return 1;
    }
}
