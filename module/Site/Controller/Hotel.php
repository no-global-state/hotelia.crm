<?php

namespace Site\Controller;

use Krystal\Db\Filter\InputDecorator;
use Krystal\Validate\Pattern;

final class Hotel extends AbstractSiteController
{
    /**
     * Renders checklist
     * 
     * @return string
     */
    public function checklistAction()
    {
        $service = $this->getModuleService('facilitiyService');

        if ($this->request->isPost()) {

            $ids = array_keys($this->request->getPost('checked'));
            $service->updateRelation($this->getHotelId(), $ids);
            return 1;

        } else {
            return $this->view->render('facility/checklist', array(
                'checklist' => $service->getCollection()
            ));
        }
    }

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
            'checklist' => $this->getModuleService('facilitiyService')->getCollection(),
            'photos' => $this->getModuleService('photoService')->fetchAll($this->getHotelId())
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

        // Facilities
        $ids = array_keys($this->request->getPost('checked'));
        $this->getModuleService('facilitiyService')->updateRelation($this->getHotelId(), $ids);

        unset($data['checked']);

        $mapper = $this->createMapper('/Site/Storage/MySQL/HotelMapper');
        $mapper->persist($data);

        return 1;
    }
}
