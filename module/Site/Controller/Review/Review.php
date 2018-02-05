<?php

namespace Site\Controller\Review;

use Site\Controller\AbstractCrmController;
use Krystal\Db\Filter\InputDecorator;

final class Review extends AbstractCrmController
{
    /**
     * Returns review mapper
     * 
     * @return \Site\Storage\MySQL\ReviewMapper
     */
    private function getReviewMapper()
    {
        return $this->createMapper('\Site\Storage\MySQL\ReviewMapper');
    }

    /**
     * Renders main grid
     * 
     * @return string
     */
    public function indexAction() : string
    {
        // Add a breadcrumb
        $this->view->getBreadcrumbBag()
                   ->addOne('Reviews');

        return $this->view->render('review/index', [
            'icon' => 'glyphicon glyphicon-signal',
            'reviews' => $this->getReviewMapper()->fetchAll($this->getHotelId())
        ]);
    }

    /**
     * Renders form
     * 
     * @param mixed $entity
     * @return string
     */
    private function createForm($entity) : string
    {
        return $this->view->render('review/review-form', [
            'entity' => $entity
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
        $entity = $this->getReviewMapper()->findByPk($id);

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
        $this->getReviewMapper()->persist($data);

        $this->flashBag->set('success', $data['id'] ? 'The review has been updated successfully' : 'The review has been added successfully');
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
        $this->getReviewMapper()->deleteByPk($id);

        $this->flashBag->set('danger', 'The review has been deleted successfully');
        $this->response->redirectToPreviousPage();
    }
}
