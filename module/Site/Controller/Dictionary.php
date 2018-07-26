<?php

namespace Site\Controller;

use Krystal\Db\Filter\InputDecorator;

final class Dictionary extends AbstractCrmController
{
    /**
     * Renders the grid
     * 
     * @return string
     */
    public function indexAction()
    {
        // Append one breadcrumb
        $this->view->getBreadcrumbBag()
                   ->addOne('Dictionary');

        $entries = $this->getModuleService('dictionaryService')->fetchAll($this->getCurrentLangId());

        return $this->view->render('dictionary/index', [
            'icon' => 'glyphicon glyphicon-book',
            'entries' => $entries,
            'count' => count($entries)
        ]);
    }

    /**
     * Renders dictionary form
     * 
     * @param mixed $entry
     * @return string
     */
    private function createForm($entry) : string
    {
        // Append one breadcrumb
        $this->view->getBreadcrumbBag()
                   ->addOne('Dictionary', $this->createUrl('Site:Dictionary@indexAction'))
                   ->addOne(!is_array($entry) ? 'Add entry' : 'Edit entry');

        return $this->view->render('dictionary/form', [
            'icon' => 'glyphicon glyphicon-pencil',
            'entry' => $entry
        ]);
    }

    /**
     * Renders empty adding form
     * 
     * @return string
     */
    public function addAction()
    {
        return $this->createForm(new InputDecorator());
    }

    /**
     * Renders edit form
     * 
     * @param int $id
     * @return mixed
     */
    public function editAction(int $id)
    {
        $entry = $this->getModuleService('dictionaryService')->fetchById($id);

        if ($entry) {
            return $this->createForm($entry);
        } else {
            return false;
        }
    }

    /**
     * Delete dictionary entry
     * 
     * @return void
     */
    public function deleteAction($id) : void
    {
        $this->getModuleService('dictionaryService')->deleteById($id);

        $this->flashBag->set('danger', 'The entry has been removed successfully');
        $this->response->redirectToPreviousPage();
    }

    /**
     * Saves dictionary entry
     * 
     * @return mixed
     */
    public function saveAction()
    {
        $input = $this->request->getPost();

        $service = $this->getModuleService('dictionaryService');
        $service->save($input);

        $this->flashBag->set('success', $input['dictionary']['id'] ? 'The entry has been updated successfully' : 'The entry has been added successfully');
        return 1;
    }
}
