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

        unset($data['floor_count']);

        $service = $this->getModuleService('hotelService');
        $service->save($data);
    }

    /**
     * Renders main page
     * 
     * @return string
     */
    public function indexAction()
    {
        if ($this->request->isPost()) {
            $this->saveAction();
            return 1;
        } else {
            // Append UI handler
            $this->view->getPluginBag()
                       ->appendLastScript('@Site/wizard.js');

            return $this->view->render('wizard/index', [
                'languageId' => $this->getCurrentLangId(),
                'hotelId' => $this->getHotelId(),
                'checklist' => $this->getModuleService('facilitiyService')->getCollection($this->getCurrentLangId(), true, $this->getHotelId())
            ]);
        }
    }
}
