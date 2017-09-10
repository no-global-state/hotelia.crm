<?php

namespace Site\Controller;

use Krystal\Db\Filter\InputDecorator;
use Krystal\Validate\Pattern;
use Site\Service\UnitCollection;

final class Service extends AbstractSiteController
{
    /**
     * Create service mapper
     * 
     * @return \Site\Storage\MySQL\RoomServiceMapper
     */
    private function createServiceMapper()
    {
        return $this->createMapper('\Site\Storage\MySQL\RoomServiceMapper');
    }

    /**
     * Creates the grid
     * 
     * @param \Krystal\Db\Filter\InputDecorator|array $entity
     * @return string
     */
    private function createGrid($entity)
    {
        return $this->view->render('services/index', array(
            'services' => $this->createServiceMapper()->fetchAll($this->getHotelId()),
            'entity' => $entity,
            'id' => $entity['id'],
            'unitCollection' => new UnitCollection
        ));
    }

    /**
     * Renders main grid
     * 
     * @return string
     */
    public function indexAction()
    {
        return $this->createGrid(new InputDecorator());
    }

    /**
     * Renders edit form
     * 
     * @param strng $id
     * @return string
     */
    public function editAction($id)
    {
        $entity = $this->createServiceMapper()->findByPk($id);

        if ($entity) {
            return $this->createGrid($entity);
        } else {
            return false;
        }
    }

    /**
     * Deletes a service
     * 
     * @param string $id
     * @return string
     */
    public function deleteAction($id)
    {
        $this->createServiceMapper()->deleteByPk($id);
        return 1;
    }

    /**
     * Saves a service
     * 
     * @return string
     */
    public function saveAction()
    {
        $data = $this->request->getPost();
        $this->createServiceMapper()->persist($data);

        return 1;
    }
}
