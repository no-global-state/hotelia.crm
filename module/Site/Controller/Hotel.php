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
        $this->view->getPluginBag()
                   ->load('map');

        return $this->view->render('hotel/form', [
            'hotel' => $this->getModuleService('hotelService')->fetchById($this->getHotelId()),
            'checklist' => $this->getModuleService('facilitiyService')->getCollection($this->getCurrentLangId(), true, $this->getHotelId()),
            'photos' => $this->getModuleService('photoService')->fetchAll($this->getHotelId()),
            'hotelTypes' => $this->getModuleService('hotelTypeService')->fetchList($this->getCurrentLangId()),
            'regions' => $this->getModuleService('regionService')->fetchList($this->getCurrentLangId()),
            'districts' => $this->getModuleService('districtService')->fetchAll(null, $this->getCurrentLangId()),
            'payments' => $this->getModuleService('paymentFieldService')->findAllByHotelId($this->getHotelId())
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
        $ids = array_keys($this->request->getPost('checked', []));
        $this->getModuleService('facilitiyService')->updateRelation($this->getHotelId(), $ids);

        if (isset($data['cover'])) {
            $photoService = $this->getModuleService('photoService');
            $photoService->updateCover($this->getHotelId(), $data['cover']);

            unset($data['cover']);
        }

        $service = $this->getModuleService('hotelService');
        $service->save($data);

        // Update payment gateway attributes
        $this->getModuleService('paymentFieldService')->updateGateways($this->getHotelId(), $data['payment']);

        $this->flashBag->set('success', 'Settings have been updated successfully');
        return 1;
    }
}
