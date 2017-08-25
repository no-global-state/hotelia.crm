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

use Site\Controller\AbstractSiteController;

final class Floor extends AbstractSiteController
{
    /**
     * @return \Site\Storage\FloorMapper
     */
    private function createFloorMapper()
    {
        return $this->createMapper('\Site\Storage\MySQL\FloorMapper');
    }

    /**
     * Saves a room
     * 
     * @param array $entity
     * @return string
     */
    public function saveAction(array $entity = array())
    {
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $this->createFloorMapper()->persist($data);

            return 1;
        } else {
            return $this->view->render('architecture/form-floor', array(
                'entity' => $entity
            ));
        }
    }

    /**
     * Edits the room
     * 
     * @param string $id
     * @return string
     */
    public function editAction($id)
    {
        $floor = $this->createFloorMapper()->findByPk($id);

        if (!empty($floor)) {
            return $this->saveAction($floor);
        } else {
            return false;
        }
    }

    /**
     * Deletes a room
     * 
     * @param string $id
     * @return string
     */
    public function deleteAction($id)
    {
        $this->createFloorMapper()->deleteByPk($id);
        $this->sessionBag->set('success', 'The floor has been deleted successfully');

        return $this->redirectToRoute('Site:Architecture:Grid@indexAction');
    }
}
