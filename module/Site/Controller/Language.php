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
    public function switchAction(string $code)
    {
        $cookie = $this->request->getCookieBag();
        $language = $this->getModuleService('languageService')->fetchByCode($code);

        if ($language !== false) {
            $cookie->set('language', $language['code']);
            $cookie->set('language_id', $language['id']);
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
        // Append a breadcrumb
        $this->view->getBreadcrumbBag()
                   ->addOne('Languages');

        $languages = $this->getModuleService('languageService')->fetchAll();

        return $this->view->render('languages/index', [
            'languages' => $languages,
            'count' => count($languages),
            'icon' => 'glyphicon glyphicon-flag',
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
     * @return mixed
     */
    public function editAction(int $id)
    {
        $entity = $this->getModuleService('languageService')->fetchById($id);

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
        $languageService = $this->getModuleService('languageService');
        $languageService->deleteById($id);

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

        $languageService = $this->getModuleService('languageService');
        $languageService->save($data);

        $this->flashBag->set('success', $data['id'] ? 'Language has been updated successfully' : 'Language has been added successfully');
        return 1;
    }
}
