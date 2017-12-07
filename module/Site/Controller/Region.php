<?php

namespace Site\Controller;

use Krystal\Validate\Pattern;
use Krystal\Db\Filter\InputDecorator;

final class Region extends AbstractCrmController
{
    /**
     * Creates region mapper
     * 
     * @return \Site\Storage\MySQL\RegionMapper
     */
    private function createRegionMapper()
    {
        return $this->createMapper('\Site\Storage\MySQL\RegionMapper');
    }

    /**
     * Renders list of regions
     * 
     * @return string
     */
    public function indexAction()
    {
        return $this->createForm(new InputDecorator());
    }

    /**
     * Renders region form
     * 
     * @param mixed $entity
     * @return string
     */
    private function createForm($entity)
    {
        return $this->view->render('region/index', [
            'regions' => $this->createRegionMapper()->fetchAll(),
            'entity' => $entity
        ]);
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
     * Renders edit form for region
     * 
     * @param int $id Region ID
     * @return string
     */
    public function editAction(int $id)
    {
        $region = $this->createRegionMapper()->findByPk($id);

        if ($region) {
            return $this->createForm($region);
        } else {
            return false;
        }
    }

    /**
     * Persist a region
     * 
     * @return int
     */
    public function saveAction()
    {
        $data = $this->request->getPost();
        $this->createRegionMapper()->persist($data);

        $this->flashBag->set('success', $data['id'] ? 'The region has been updated successfully' : 'The region has been added successfully');
        return 1;
    }

    /**
     * Deletes a region by its ID
     * 
     * @param int $id Region ID
     * @return void
     */
    public function deleteAction(int $id)
    {
        $this->createRegionMapper()->deleteByPk($id);

        $this->flashBag->set('danger', 'The region has been deleted successfully');
        $this->response->redirectToPreviousPage();
    }
}
