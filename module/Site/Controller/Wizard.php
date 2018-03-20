<?php

namespace Site\Controller;

use Site\Collection\FacilityTypeCollection;
use Site\Collection\BreakfastCollection;

final class Wizard extends AbstractCrmController
{
    /**
     * Saves data
     * 
     * @return void
     */
    private function saveAction()
    {
        // Optional photo upload
        if ($this->request->hasFiles('files')) {
            $files = $this->request->getFiles('files');

            // Start batch uploading
            $this->getModuleService('photoService')->batchUpload($this->getHotelId(), $files);
        }

        $data = $this->request->getPost();

        // Update checklist if provided
        $ids = array_keys($this->request->getPost('checked', []));
        $this->getModuleService('facilitiyService')->updateRelation($this->getHotelId(), $ids);

        // No need any more
        if (isset($data['checked'])) {
            unset($data['checked']);
        }

        // Update meals relations
        $this->getModuleService('mealsService')->updateRelation($this->getHotelId(), $this->request->getPost('meal', []));

        // Update global prices
        $this->getModuleService('mealsService')->updateGlobalPrice($this->getHotelId(), $this->request->getPost('meal', []));

        // Save hotel data
        $this->getModuleService('hotelService')->save($data);

        // Update payment gateway attributes
        $this->getModuleService('paymentFieldService')->updateGateways($this->getHotelId(), $data['payment']);

        // Mark wizard as finished
        $this->getModuleService('userService')->markWizardAsFinished($this->getUserId());

        return json_encode([
            'successUrl' => $this->createUrl('Site:Architecture:RoomType@indexAction')
        ]);
    }

    /**
     * Renders a form
     * 
     * @return string
     */
    private function formAction()
    {
        // Append UI handler
        $this->view->getPluginBag()
                   ->load('map')
                   ->appendLastScript('@Site/wizard.js');

        return $this->view->render('wizard/index', [
            'extended' => false,
            // Collections
            'breakfasts' => (new BreakfastCollection())->getAll(),
            'types' => (new FacilityTypeCollection)->getAll(),
            'categories' => $this->getModuleService('roomCategoryService')->fetchList($this->getCurrentLangId()),
            'languageId' => $this->getCurrentLangId(),
            'hotelId' => $this->getHotelId(),
            'checklist' => $this->getModuleService('facilitiyService')->getCollection($this->getCurrentLangId(), true, $this->getHotelId()),
            'hotelTypes' => $this->getModuleService('hotelTypeService')->fetchList($this->getCurrentLangId()),
            'regions' => $this->getModuleService('regionService')->fetchList($this->getCurrentLangId()),
            'districts' => $this->getModuleService('districtService')->fetchAll(null, $this->getCurrentLangId()),
            'payments' => $this->getModuleService('paymentFieldService')->findAllByHotelId($this->getHotelId()),
            'meals' => $this->getModuleService('mealsService')->fetchAll($this->getCurrentLangId(), $this->getHotelId()),
            'globalMealPrices' => $this->getModuleService('mealsService')->findGlobalPrices($this->getHotelId()),
            'priceGroups' => $this->createMapper('\Site\Storage\MySQL\PriceGroupMapper')->fetchAll(false)
        ]);
    }

    /**
     * Renders main page
     * 
     * @return string
     */
    public function indexAction()
    {
        if ($this->request->isPost()) {
            return $this->saveAction();
        } else {
            return $this->formAction();
        }
    }
}
