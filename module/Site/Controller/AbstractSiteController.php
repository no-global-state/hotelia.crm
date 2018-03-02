<?php

namespace Site\Controller;

use Krystal\Application\Controller\AbstractController;
use Krystal\Validate\Renderer;

abstract class AbstractSiteController extends AbstractController
{
    const PARAM_SESSION_PRICE_GROUP = 'price_group';

    /**
     * Sets price group id
     * 
     * @param int $priceGroupId
     * @return void
     */
    protected function setPriceGroupId(int $priceGroupId)
    {
        $this->sessionBag->set(self::PARAM_SESSION_PRICE_GROUP, $priceGroupId);
    }

    /**
     * Returns current price group ID
     * 
     * @return int
     */
    protected function getPriceGroupId() : int
    {
        if ($this->sessionBag->has(self::PARAM_SESSION_PRICE_GROUP)) {
            return $this->sessionBag->get(self::PARAM_SESSION_PRICE_GROUP);
        }

        return 1;
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
     * Returns current language ID
     * 
     * @return int
     */
    protected function getCurrentLangId() : int
    {
        return 1;
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
        $this->view->setTheme('site');
        $this->appConfig->setTheme('site');

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

        $hotel = $this->getHotelData();

        // Add shared variables
        $this->view->addVariables(array(
            'params' => $this->paramBag->getAll(),
            'locale' => $this->appConfig->getLanguage(),
            'appName' => $this->paramBag->get('appName'),
            'priceGroups' => $this->getModuleService('priceGroupService')->fetchAll(),
            'activePriceGroupId' => $this->getPriceGroupId(),
        ));

        // Load language if explicitly provided
        if ($this->paramBag->has('siteLanguage')) {
            $siteLanguage = $this->paramBag->get('siteLanguage');
            $this->loadTranslations($siteLanguage);
        }

        // Define the main layout
        $this->view->setLayout('__layout__');
    }
}
