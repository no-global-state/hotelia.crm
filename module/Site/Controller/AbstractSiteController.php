<?php

namespace Site\Controller;

use Krystal\Application\Controller\AbstractAuthAwareController;
use Krystal\Validate\Renderer;
use Krystal\Form\Gadget\LastCategoryKeeper;

abstract class AbstractSiteController extends AbstractAuthAwareController
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
     * Returns current hotel ID
     * 
     * @return integer
     */
    protected function getHotelId()
    {
        return 1;
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
            // Check the validity
            if (!$this->csrfProtector->isValid($this->request->getMetaCsrfToken())) {
                $this->response->setStatusCode(400);
                die('Invalid CSRF token');
            }
        }
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
            '@Site/styles.css'
        ));

        // Append required script paths
        $this->view->getPluginBag()
                   ->appendScripts(array(
                        '@Site/jquery.min.js',
                        '@Site/bootstrap/js/bootstrap.min.js',
                        '@Site/krystal.jquery.js',
                   ))
                   // This one will always be last
                   ->appendLastScript('@Site/application.js');

        $hotel = $this->createMapper('\Site\Storage\MySQL\HotelMapper')->findByPk($this->getHotelId());

        // Add shared variables
        $this->view->addVariables(array(
            'isLoggedIn' => $this->getAuthService()->isLoggedIn(),
            'locale' => $this->appConfig->getLanguage(),
            'currency' => $hotel['currency'],
            'appName' => $this->paramBag->get('appName')
        ));

        // Define the main layout
        $this->view->setLayout('__layout__');
    }
}
