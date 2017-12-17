<?php

namespace Site\Controller;

final class Hotel extends AbstractCrmController
{
    /**
     * Renders the form
     * 
     * @return string
     */
    public function indexAction() : string
    {
        return $this->view->render('hotel/form', [
            'hotel' => $this->getModuleService('hotelService')->fetchById($this->getHotelId(), $this->getCurrentLangId()),
            'checklist' => $this->getModuleService('facilitiyService')->getCollection($this->getCurrentLangId()),
            'photos' => $this->getModuleService('photoService')->fetchAll($this->getHotelId())
        ]);
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

        $service = $this->getModuleService('hotelService');
        $service->save($data);

        $this->flashBag->set('success', 'Settings have been updated successfully');
        return 1;
    }
}
