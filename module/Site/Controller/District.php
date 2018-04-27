<?php

namespace Site\Controller;

use Krystal\Validate\Pattern;
use Krystal\Db\Filter\InputDecorator;

final class District extends AbstractCrmController
{
    /**
     * Renders region form
     * 
     * @param mixed $district
     * @return string
     */
    private function createForm($district) : string
    {
        // Appends on breadcrumb
        $this->view->getBreadcrumbBag()
                   ->addOne('Regions and districts', $this->createUrl('Site:Region@indexAction'))
                   ->addOne(!is_array($district) ? 'Add a district' : 'Edit the district');

        return $this->view->render('region/district', [
            'district' => $district,
            'regions' => $this->getModuleService('regionService')->fetchList($this->getCurrentLangId()),
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
     * Renders edit form for district
     * 
     * @param int $id District ID
     * @return string
     */
    public function editAction(int $id)
    {
        $district = $this->getModuleService('districtService')->fetchById($id);

        if ($district) {
            return $this->createForm($district);
        } else {
            return false;
        }
    }

    /**
     * Persist a district
     * 
     * @return int
     */
    public function saveAction()
    {
        $data = $this->request->getPost();
        $this->getModuleService('districtService')->save($data);

        $this->flashBag->set('success', $data['district']['id'] ? 'The district has been updated successfully' : 'The district has been added successfully');
        return 1;
    }

    /**
     * Deletes a district by its ID
     * 
     * @param int $id District ID
     * @return void
     */
    public function deleteAction(int $id) : void
    {
        $this->getModuleService('districtService')->deleteById($id);

        $this->flashBag->set('danger', 'The district has been deleted successfully');
        $this->response->redirectToPreviousPage();
    }
}
