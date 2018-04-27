<?php

namespace Site\Controller;

use Krystal\Validate\Pattern;
use Krystal\Db\Filter\InputDecorator;

final class Region extends AbstractCrmController
{
    /**
     * Creates main grid
     * 
     * @param int|null $regionId
     * @return string
     */
    private function createGrid($regionId) : string
    {
        return $this->view->render('region/index', [
            'regionId' => $regionId,
            'regions' => $this->getModuleService('regionService')->fetchAll($this->getCurrentLangId()),
            'districts' => $this->getModuleService('districtService')->fetchAll($regionId, $this->getCurrentLangId())
        ]);
    }

    /**
     * Renders list of regions
     * 
     * @return string
     */
    public function indexAction() : string
    {
        return $this->createGrid(null);
    }

    /**
     * List all districts by region id
     * 
     * @param int $id Region id
     * @return string
     */
    public function districtAction(int $id) : string
    {
        return $this->createGrid($id);
    }

    /**
     * Renders region form
     * 
     * @param mixed $entity
     * @return string
     */
    private function createForm($region) : string
    {
        // Appends on breadcrumb
        $this->view->getBreadcrumbBag()
                   ->addOne('Regions and districts', $this->createUrl('Site:Region@indexAction'))
                   ->addOne(!is_array($region) ? 'Add new region' : 'Edit the region');

        return $this->view->render('region/region', [
            'region' => $region,
            'icon' => 'glyphicon glyphicon-pencil'
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
        $region = $this->getModuleService('regionService')->fetchById($id);

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
        $this->getModuleService('regionService')->save($data);

        $this->flashBag->set('success', $data['region']['id'] ? 'The region has been updated successfully' : 'The region has been added successfully');
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
        $regionService = $this->getModuleService('regionService');
        $regionService->deleteById($id);

        $this->flashBag->set('danger', 'The region has been deleted successfully');
        $this->response->redirectToPreviousPage();
    }
}
