<?php

namespace Site\Controller\Review;

use Site\Controller\AbstractCrmController;
use Krystal\Db\Filter\InputDecorator;

final class ReviewType extends AbstractCrmController
{
    /**
     * Returns review type mapper
     * 
     * @return \Site\Storage\MySQL\ReviewTypeMapper
     */
    private function getReviewTypeMapper()
    {
        return $this->createMapper('\Site\Storage\MySQL\ReviewTypeMapper');
    }

    /**
     * Renders main grid
     * 
     * @return string
     */
    public function indexAction()
    {
        return $this->createForm(new InputDecorator());
    }

    /**
     * Renders form
     * 
     * @param mixed $entity
     * @return string
     */
    private function createForm($entity) : string
    {
        return $this->view->render('review/review-type', [
            'entity' => $entity,
            'reviewTypes' => $this->getReviewTypeMapper()->fetchAll()
        ]);
    }

    /**
     * Render edit form
     * 
     * @param string $id Review type ID
     * @return string
     */
    public function editAction(int $id)
    {
        $entity = $this->getReviewTypeMapper()->findByPk($id);

        if ($entity !== false) {
            return $this->createForm($entity);
        } else {
            return false;
        }
    }

    /**
     * Persist review type
     * 
     * @return string
     */
    public function saveAction()
    {
        $data = $this->request->getPost();
        $this->getReviewTypeMapper()->persist($data);

        $this->flashBag->set('success', $data['id'] ? 'The review type has been updated successfully' : 'The review type has been added successfully');
        return 1;
    }

    /**
     * Deletes a review type by its associated ID
     * 
     * @param string $id Review type ID
     * @return void
     */
    public function deleteAction(int $id)
    {
        $this->getReviewTypeMapper()->deleteByPk($id);

        $this->flashBag->set('danger', 'The review type has been deleted successfully');
        $this->response->redirectToPreviousPage();
    }
}
