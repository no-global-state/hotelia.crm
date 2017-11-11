<?php

/**
 * This file is part of the Hotelia CRM Solution
 * 
 * Copyright (c) No Global State Lab
 * 
 * For the full copyright and license information, please view
 * the license file that was distributed with this source code.
 */

namespace Site\Controller\Architecture;

use Site\Controller\AbstractCrmController;
use Krystal\Db\Filter\InputDecorator;

final class Floor extends AbstractCrmController
{
    /**
     * @return \Site\Storage\FloorMapper
     */
    private function createFloorMapper()
    {
        return $this->createMapper('\Site\Storage\MySQL\FloorMapper');
    }

    /**
     * Creates floor form
     * 
     * @param mixed $entity
     * @return string
     */
    private function createForm($entity) : string
    {
        return $this->view->render('architecture/form-floor', array(
            'entity' => $entity
        ));
    }

    /**
     * Renders empty form
     * 
     * @return string
     */
    public function addAction()
    {
        return $this->createForm(new InputDecorator());
    }

    /**
     * Edits the floor by its ID
     * 
     * @param int $id Floor ID
     * @return string
     */
    public function editAction(int $id)
    {
        $floor = $this->createFloorMapper()->findByPk($id);

        if (!empty($floor)) {
            return $this->createForm($floor);
        } else {
            return false;
        }
    }

    /**
     * Saves a floor
     * 
     * @return int
     */
    public function saveAction() : int
    {
        $data = $this->request->getPost();
        $this->createFloorMapper()->persist($data);

        $this->flashBag->set('success', $data['id'] ? 'The floor has been updated successfully' : 'The floor has been added successfully');
        return 1;
    }

    /**
     * Deletes a floor by its ID
     * 
     * @param int $id Floor ID
     * @return void
     */
    public function deleteAction(int $id) : void
    {
        $this->createFloorMapper()->deleteByPk($id);
        $this->flashBag->set('danger', 'The floor has been deleted successfully');

        return $this->redirectToRoute('Site:Architecture:Grid@indexAction');
    }
}
