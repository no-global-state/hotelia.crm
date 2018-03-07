<?php

namespace Site\Controller;

use Krystal\Db\Filter\InputDecorator;

final class Meals extends AbstractCrmController
{
    /**
     * Renders meals form
     * 
     * @return string
     */
    public function indexAction() : string
    {
        // Append one breadcrumb
        $this->view->getBreadcrumbBag()
                   ->addOne('Meals');

        return $this->view->render('meals/index', [
            'meals' => $this->getModuleService('mealsService')->fetchAll($this->getCurrentLangId()),
            'icon' => 'glyphicon glyphicon-glass'
        ]);
    }

    /**
     * Renders meals form
     * 
     * @param mixed $meal
     * @return string
     */
    private function createForm($meal) : string
    {
        // Append one breadcrumb
        $this->view->getBreadcrumbBag()
                   ->addOne('Meals', $this->createUrl('Site:Meals@indexAction'))
                   ->addOne(!is_array($meal) ? 'Add meal' : 'Edit meal');

        return $this->view->render('meals/form', [
            'meal' => $meal,
            'icon' => 'glyphicon glyphicon-glass'
        ]);
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
     * Renders edit form
     * 
     * @param int $id
     * @return string
     */
    public function editAction(int $id)
    {
        $meal = $this->getModuleService('mealsService')->fetchById($id);

        if ($meal) {
            return $this->createForm($meal);
        } else {
            return false;
        }
    }

    /**
     * Deletes a meal
     * 
     * @param int $id
     * @return void
     */
    public function deleteAction(int $id) : void
    {
        $this->getModuleService('mealsService')->deleteById($id);

        $this->flashBag->set('danger', 'Selected meal has been deleted successfully');
        $this->response->redirectToPreviousPage();
    }

    /**
     * Save meals
     * 
     * @return string
     */
    public function saveAction()
    {
        $input = $this->request->getPost();
        $this->getModuleService('mealsService')->save($input);

        $this->flashBag->set('success', $input['meal']['id'] ? 'The meal has been updated successfully' : 'The meal has been added successfully');
        return 1;
    }
}
