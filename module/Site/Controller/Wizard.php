<?php

namespace Site\Controller;

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

        // Save hotel data
        $this->getModuleService('hotelService')->save($data);

        // Mark wizard as finished
        $this->getModuleService('userService')->markWizardAsFinished($this->getUserId());

        return json_encode([
            'successUrl' => $this->createUrl('Site:Architecture:RoomType@indexAction')
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
            // Append UI handler
            $this->view->getPluginBag()
                       ->load('map')
                       ->appendLastScript('@Site/wizard.js');

            return $this->view->render('wizard/index', [
                'languageId' => $this->getCurrentLangId(),
                'hotelId' => $this->getHotelId(),
                'checklist' => $this->getModuleService('facilitiyService')->getCollection($this->getCurrentLangId(), true, $this->getHotelId()),
                'hotelTypes' => $this->getModuleService('hotelTypeService')->fetchList($this->getCurrentLangId()),
                'regions' => $this->getModuleService('regionService')->fetchList($this->getCurrentLangId()),
                'districts' => $this->getModuleService('districtService')->fetchAll(null, $this->getCurrentLangId()),
            ]);
        }
    }
}
