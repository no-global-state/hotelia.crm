<?php

namespace Site\Controller;

use Krystal\Application\Controller\AbstractAuthAwareController;
use Krystal\Validate\Renderer;
use Krystal\Form\Gadget\LastCategoryKeeper;

abstract class AbstractCrmController extends AbstractAuthAwareController
{
    /**
     * Returns keeper service
     * 
     * @return \Krystal\Form\Gadget\LastCategoryKeeper
     */
    protected function getFloorIdKeeper()
    {
        static $keeper = null;

        if ($keeper === null) {
            $keeper = new LastCategoryKeeper($this->sessionBag, 'ack');
        }

        return $keeper;
    }

    /**
     * Returns current language ID
     * 
     * @return int
     */
    protected function getCurrentLangId() : int
    {
        return 1;
    }

    /**
     * Returns data appending current hotel ID
     * 
     * @param array $data
     * @return array
     */
    final protected function getWithHotelId(array $data) : array
    {
        return array_merge($data, ['hotel_id' => $this->getHotelId()]);
    }

    /**
     * Returns current hotel ID
     * 
     * @return integer
     */
    protected function getHotelId()
    {
        return $this->getModuleService('userService')->getHotelId();
    }

    /**
     * Returns shared per page count
     * 
     * @return integer
     */
    protected function getPerPageCount()
    {
        return 20;
    }

    /**
     * Returns shared authentication service for the site
     * 
     * @return \Site\Service\UserService
     */
    protected function getAuthService()
    {
        return $this->getModuleService('userService');
    }

    /**
     * {@inheritDoc}
     */
    protected function onSuccess()
    {
    }

    /**
     * {@inheritDoc}
     */
    protected function onFailure()
    {
        $this->response->redirect($this->createUrl('Site:Auth@indexAction'));
    }

    /**
     * {@inheritDoc}
     */
    protected function onNoRights()
    {
        die($this->translator->translate('You do not have enough rights to perform this action!'));
    }

    /**
     * Validates the request
     * 
     * @return void
     */
    protected function validateRequest()
    {
        // Validate CSRF token from POST requests
        if ($this->request->isPost()) {
            $token = $this->request->isAjax() ? $this->request->getMetaCsrfToken() : $this->request->getPost('csrf-token');

            // Check the validity
            if (!$this->csrfProtector->isValid($token)) {
                $this->response->setStatusCode(400);
                die('Invalid CSRF token');
            }
        }
    }

    /**
     * Returns current configuration data of hotel
     * 
     * @return array
     */
    protected function getHotelData()
    {
        static $hotel = null;

        if ($hotel === null) {
            $hotel = $this->createMapper('\Site\Storage\MySQL\HotelMapper')->findByPk($this->getHotelId());
        }

        return $hotel;
    }

    /**
     * This method automatically gets called when this controller executes
     * 
     * @return void
     */
    protected function bootstrap()
    {
        // Validate the request on demand
        $this->validateRequest();

        // Define the default renderer for validation error messages
        $this->validatorFactory->setRenderer(new Renderer\StandardJson());

        // Define a directory where partial template fragments must be stored
        $this->view->getPartialBag()
                   ->addPartialDir($this->view->createThemePath('Site', $this->appConfig->getTheme()).'/partials/');

        // Append required assets
        $this->view->getPluginBag()->appendStylesheets(array(
            '@Site/bootstrap/css/bootstrap.min.css',
            '@Site/jasny-bootstrap/css/jasny-bootstrap.min.css',
            '@Site/styles.css'
        ));

        // Append required script paths
        $this->view->getPluginBag()
                   ->appendScripts(array(
                        '@Site/jquery.min.js',
                        '@Site/bootstrap/js/bootstrap.min.js',
                        '@Site/jasny-bootstrap/js/jasny-bootstrap.min.js',
                        '@Site/krystal.jquery.js',
                   ))
                   // This one will always be last
                   ->appendLastScript('@Site/application.js');

        $hotel = $this->getHotelData();

        // Language loading
        $bag = $this->request->getCookieBag();
        $code = $bag->has('language') ? $bag->get('language') : $this->appConfig->getLanguage();
        $this->loadTranslations($code);

        // Add shared variables
        $this->view->addVariables(array(
            'isLoggedIn' => $this->getAuthService()->isLoggedIn(),
            'role' => $this->getAuthService()->getRole(),
            'locale' => $this->appConfig->getLanguage(),
            'currency' => $hotel['currency'],
            'active' => (bool) $hotel['active'],
            'appName' => $this->paramBag->get('appName'),
            'languages' => $this->createMapper('\Site\Storage\MySQL\LanguageMapper')->fetchAll(),
            'code' => $code
        ));


        // Define the main layout
        $this->view->setLayout('__layout__');
    }
}
