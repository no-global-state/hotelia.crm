<?php

namespace Site\Controller;

use Krystal\Db\Filter\InputDecorator;

final class Photo extends AbstractCrmController
{
    /**
     * Creates a form
     * 
     * @param mixed $photo
     * @return string
     */
    private function createForm($photo) : string
    {
        // Add a breadcrumb
        $this->view->getBreadcrumbBag()
                   ->addOne('Hotel information', $this->createUrl('Site:Hotel@indexAction'))
                   ->addOne($photo['id'] ? 'Edit the photo' : 'Add a photo');
        
        return $this->view->render('photo/form', array(
            'photo' => $photo,
            'icon' => 'glyphicon glyphicon-pencil'
        ));
    }

    /**
     * Persists a photo
     * 
     * @return string
     */
    public function saveAction() : int
    {
        $service = $this->getModuleService('photoService');

        if ($this->request->getPost('id')) {
            $service->update($this->request->getAll());
        } else {
            $service->add($this->getHotelId(), $this->request->getAll());
        }

        $this->flashBag->set('success', $this->request->getPost('id') ? 'The photo has been updated successfully' : 'The photo has been added successfully');
        return 1;
    }

    /**
     * Renders adding form
     * 
     * @return string
     */
    public function addAction() : string
    {
        return $this->createForm(new InputDecorator());
    }

    /**
     * Renders edit form by photo ID
     * 
     * @param string $id Photo ID
     * @return string
     */
    public function editAction(int $id)
    {
        $photo = $this->getModuleService('photoService')->fetchById($id);

        if ($photo) {
            return $this->createForm($photo);
        } else {
            return false;
        }
    }

    /**
     * Deletes a photo by its ID
     * 
     * @param string $id Photo ID
     * @return string
     */
    public function deleteAction(int $id)
    {
        $this->getModuleService('photoService')->deleteById($id);
        
        $this->flashBag->set('danger', 'Selected photo has been deleted successfully');
        return $this->response->redirectToPreviousPage();
    }
}
