<?php

namespace Site\Controller;

use Krystal\Validate\Pattern;
use Krystal\Stdlib\VirtualEntity;
use Krystal\Db\Filter\InputDecorator;

final class Language extends AbstractCrmController
{
    /**
     * Switches to another language
     * 
     * @param string $code Language code
     * @return void
     */
    public function switchAction(string $code) : void
    {
        $languageMapper = $this->createMapper('\Site\Storage\MySQL\LanguageMapper');

        if ($languageMapper->exists($code)) {
            $this->sessionBag->set('language', $code);
        }

        $this->response->redirectToPreviousPage();
    }

    /**
     * Creates language form
     * 
     * @param mixed $entity
     * @return string
     */
    private function createForm($entity) : string
    {
        $languageMapper = $this->createMapper('\Site\Storage\MySQL\LanguageMapper');

        return $this->view->render('languages/index', [
            'languages' => $languageMapper->fetchAll(),
            'entity' => $entity
        ]);
    }

    /**
     * Renders language grid
     * 
     * @return string
     */
    public function indexAction() : string
    {
        return $this->createForm(new InputDecorator());
    }

    /**
     * Renders edit form for language
     * 
     * @param integer $id
     * @return string
     */
    public function editAction(int $id)
    {
        $languageMapper = $this->createMapper('\Site\Storage\MySQL\LanguageMapper');
        $entity = $languageMapper->findByPk($id);

        if ($entity) {
            return $this->createForm($entity);
        } else {
            return false;
        }
    }

    /**
     * Deletes a language
     * 
     * @param integer $id
     * @return string
     */
    public function deleteAction(int $id) : void
    {
        $languageMapper = $this->createMapper('\Site\Storage\MySQL\LanguageMapper');
        $languageMapper->deleteByPk($id);

        $this->flashBag->set('danger', 'Language has been removed successfully');
        $this->response->redirectToPreviousPage();
    }

    /**
     * Saves entity
     * 
     * @return string
     */
    public function saveAction() : int
    {
        $data = $this->request->getPost();

        $languageMapper = $this->createMapper('\Site\Storage\MySQL\LanguageMapper');
        $languageMapper->persist($data);

        $this->flashBag->set('success', $data['id'] ? 'Language has been updated successfully' : 'Language has been added successfully');
        return 1;
    }
}
