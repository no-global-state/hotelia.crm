<?php

namespace Site\Controller;

use Site\Collection\FacilityTypeCollection;

final class Hotel extends AbstractCrmController
{
    /**
     * Renders the form
     * 
     * @return string
     */
    public function indexAction() : string
    {
        // Add a breadcrumb
        $this->view->getBreadcrumbBag()
                   ->addOne('Hotel information');
        
        $this->view->getPluginBag()
                   ->load(['map', 'datetimepicker']);

        return $this->view->render('hotel/form', [
            'types' => (new FacilityTypeCollection)->getAll(),
            'icon' => 'glyphicon glyphicon-list-alt',
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

        // Update facility relations
        $this->getModuleService('facilitiyService')->updateRelation($this->getHotelId(), $this->request->getPost('facility', []));

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
