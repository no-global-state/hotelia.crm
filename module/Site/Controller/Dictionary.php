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
        return $this->view->render('dictionary/index', [
            'entries' => $this->getModuleService('dictionaryService')->fetchAll($this->getCurrentLangId())
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
        return $this->view->render('dictionary/form', [
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

        return 1;
    }
}
