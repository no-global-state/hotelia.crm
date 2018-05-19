<?php

namespace Site\Controller;

use Krystal\Application\Controller\AbstractController;
use Krystal\Validate\Renderer;
use Site\Service\Dictionary;

abstract class AbstractSiteController extends AbstractController
{
    const PARAM_SESSION_PRICE_GROUP = 'price_group';
    const PARAM_COOKIE_LANG_ID = 'language_id';
    const PARAM_COOKIE_LANG_CODE = 'language_code';

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
     * Returns current currency
     * 
     * @return string
     */
    protected function getCurrency() : string
    {
        $group = $this->getModuleService('priceGroupService')->fetchById($this->getPriceGroupId());

        return $group['currency'];
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
     * Creates dictionary instance
     * 
     * @return \Site\Service\Dictionary
     */
    protected function createDictionary()
    {
        static $dictionary;

        if (is_null($dictionary)) {
            $dictionary = new Dictionary($this->getModuleService('dictionaryService'), $this->getCurrentLangId());
        }

        return $dictionary;
    }

    /**
     * Returns current language ID
     * 
     * @return int
     */
    protected function getCurrentLangId() : int
    {
        $bag = $this->request->getCookieBag();

        if ($bag->has(self::PARAM_COOKIE_LANG_ID)) {
            return $bag->get(self::PARAM_COOKIE_LANG_ID);
        }

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

        // Load site plugin
        $this->view->getPluginBag()
                   ->load('site');

        $hotel = $this->getHotelData();

        // Language loading
        $bag = $this->request->getCookieBag();
        $code = $bag->has(self::PARAM_COOKIE_LANG_CODE) ? $bag->get(self::PARAM_COOKIE_LANG_CODE) : $this->appConfig->getLanguage();
        $this->loadTranslations($code);

        // Add shared variables
        $this->view->addVariables(array(
            // Languages
            'languages' => $this->getModuleService('languageService')->fetchAll(),
            'language' => $code,
            'dictionary' => $this->createDictionary(),
            'currency' => $this->getCurrency(),

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
