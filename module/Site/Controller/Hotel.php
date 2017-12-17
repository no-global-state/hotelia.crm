<?php

namespace Site\Controller;

use Krystal\Db\Filter\InputDecorator;
use Krystal\Validate\Pattern;

final class Hotel extends AbstractCrmController
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
            'hotel' => new InputDecorator($hotel ? $hotel : array()),
            'checklist' => $this->getModuleService('facilitiyService')->getCollection($this->getCurrentLangId()),
            'photos' => $this->getModuleService('photoService')->fetchAll($this->getHotelId())
        ));
    }

    /**
     * Save form data
     * 
     * @return string
     */
    public function saveAction() : int
    {
        $data = $this->request->getPost();

        // Facilities
        $ids = array_keys($this->request->getPost('checked'));
        $this->getModuleService('facilitiyService')->updateRelation($this->getHotelId(), $ids);

        unset($data['checked']);

        $mapper = $this->createMapper('/Site/Storage/MySQL/HotelMapper');
        $mapper->persist($data);

        $this->flashBag->set('success', 'Settings have been updated successfully');
        return 1;
    }
}
