<?php

namespace Site\Controller;

use Krystal\Db\Filter\InputDecorator;

final class Photo extends AbstractSiteController
{
    /**
     * Creates a form
     * 
     * @param array $photo
     * @return string
     */
    private function createForm($photo)
    {
        return $this->view->render('photo/form', array(
            'photo' => $photo
        ));
    }

    /**
     * Persists a photo
     * 
     * @return string
     */
    public function saveAction()
    {
        $service = $this->getModuleService('photoService');

        if ($this->request->getPost('id')) {
            $service->update($this->request->getAll());
        } else {
            $service->add($this->getHotelId(), $this->request->getAll());
        }

        return 1;
    }

    /**
     * Renders adding form
     * 
     * @return string
     */
    public function addAction()
    {
        return $this->createForm(new InputDecorator());
    }

    /**
     * Renders edit form
     * 
     * @param string $id Photo ID
     * @return string
     */
    public function editAction($id)
    {
        $photo = $this->getModuleService('photoService')->fetchById($id);

        if ($photo) {
            return $this->createForm($photo);
        } else {
            return false;
        }
    }

    /**
     * Deletes a photo
     * 
     * @param string $id Photo ID
     * @return string
     */
    public function deleteAction($id)
    {
        $this->getModuleService('photoService')->deleteById($id);
        $this->flashBag->set('success', 'Selected photo has been deleted successfully');

        return $this->response->redirectToPreviousPage();
    }
}
