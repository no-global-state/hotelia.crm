<?php

namespace Site\Controller;

use Krystal\Db\Filter\InputDecorator;

final class PriceGroup extends AbstractCrmController
{
    /**
     * Creates price group mapper
     * 
     * @return \Site\Storage\MySQL\PriceGroupMapper
     */
    private function createPriceGroupMapper()
    {
        return $this->createMapper('\Site\Storage\MySQL\PriceGroupMapper');
    }

    /**
     * Creates form
     * 
     * @param mixed $entity
     * @return string
     */
    private function createForm($entity) : string
    {
        return $this->view->render('price-group/index', [
            'entity' => $entity,
            'priceGroups' => $this->createPriceGroupMapper()->fetchAll()
        ]);
    }

    /**
     * Renders main grid
     * 
     * @return string
     */
    public function indexAction() : string
    {
        return $this->createForm(new InputDecorator());
    }

    /**
     * Renders edit form
     * 
     * @param int $id
     * @return string|boolean
     */
    public function editAction(int $id)
    {
        $priceGroup = $this->createPriceGroupMapper()->findByPk($id);

        if ($priceGroup) {
            return $this->createForm($priceGroup);
        } else {
            return false;
        }
    }

    /**
     * Deletes price group by its ID
     * 
     * @param int $id
     * @return void
     */
    public function deleteAction(int $id)
    {
        $this->createPriceGroupMapper()->deleteByPk($id);
        $this->flashBag->set('danger', 'The price group has been deleted successfully');
        $this->response->redirectToPreviousPage();
    }

    /**
     * Persist price group
     * 
     * @return int
     */
    public function saveAction()
    {
        $data = $this->request->getPost();
        $this->createPriceGroupMapper()->persist($data);

        $this->flashBag->set('success', $data['id'] ? 'The price group has been updated successfully' : 'The price group has been added successfully');
        return 1;
    }
}
