<?php

namespace Site\Controller;

use Krystal\Application\Controller\AbstractController;
use Krystal\Validate\Renderer;
use Site\Service\Dictionary;
use Site\Service\BehaviorService;
use Site\Service\ExternalService;

abstract class AbstractSiteController extends AbstractController
{
    const PARAM_SESSION_PRICE_GROUP = 'price_group_id';
    const PARAM_COOKIE_LANG_ID = 'language_id';
    const PARAM_COOKIE_LANG_CODE = 'language_code';

    /**
     * Returns behavior defaults
     * 
     * @return array
     */
    protected function getBehaviorDefaults() : array
    {
        static $call = null;

        if (is_null($call)) {
            if (isset($_ENV['behavior'])) {
                $ip = $this->request->getClientIp();
                $session = $this->sessionBag;

                $service = new BehaviorService($ip, $_ENV['behavior']);
                $call = $service->getDefaults($session, $this->getModuleService('languageService')->fetchAll());

            } else {
                $call = [];
            }
        }

        return $call;
    }

    /**
     * Returns shared parameter
     * 
     * @param string $key
     * @param boolean $default Default value to be returned
     * @return mixed
     */
    protected function getSharedParam(string $key, $default = false)
    {
        $defaults = $this->getBehaviorDefaults();

        // Priority #1 - Query string
        if ($this->request->hasQuery($key)) {
            $value = $this->request->getQuery($key);

            $this->sessionBag->set($key, $value);
            return $value;

        // Priority #2 - Session
        } else if ($this->sessionBag->has($key)) {
            return $this->sessionBag->get($key);

        // Priority #3 - Cookies
        } else if ($this->request->getCookieBag()->has($key)) {
            return $this->request->getCookieBag()->get($key);

        // Priority #4 - Defaults
        } else if (isset($defaults[$key])) {
            $value = $defaults[$key];

            $this->sessionBag->set($key, $value);
            return $value;
        // Nothing of the above
        } else {
            return $default;
        }
    }

    /**
     * Sets language
     * 
     * @param string|int $code Language code or ID
     * @return boolean
     */
    protected function setLanguage(string $target) : bool
    {
        if (is_numeric($target)) {
            $language = $this->getModuleService('languageService')->fetchById($target);
        } else {
            $language = $this->getModuleService('languageService')->fetchByCode($target);
        }

        if ($language) {
            $this->request->getCookieBag()->set(self::PARAM_COOKIE_LANG_ID, $language['id']);
            $this->request->getCookieBag()->set(self::PARAM_COOKIE_LANG_CODE, $language['code']);

            return true;
        } else {
            return false;
        }
    }

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
        return $this->getSharedParam(self::PARAM_SESSION_PRICE_GROUP, 1);
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
        return $this->getSharedParam(self::PARAM_COOKIE_LANG_ID, 1);
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
     * Load defaults
     * 
     * @return array
     */
    protected function loadDefaults()
    {
        // Get defaults
        $defaults = $this->getBehaviorDefaults();

        if (!empty($defaults)) {
            // Filtered languages
            $languages = $defaults['languages'];

            $this->view->addVariable('country', $defaults['country']);
        } else {
            // All languages
            $languages = $this->getModuleService('languageService')->fetchAll();
        }

        $this->view->addVariable('languages', $languages);
    }

    /**
     * This method automatically gets called when this controller executes
     * 
     * @return void
     */
    protected function bootstrap()
    {
        // Optionals
        $priceGroupId = $this->request->getQuery('price_group_id');
        $lang = $this->request->getQuery('lang');

        // If default price group set, use it
        if ($priceGroupId) {
            $this->setPriceGroupId($priceGroupId);
        }

        // If language code ID, use it as a default one
        if ($lang) {
            $this->setLanguage(ExternalService::externalLangId($lang));
        }

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

        $foreigner = $this->getPriceGroupId() == 1;
        $exchange = $this->getModuleService('exchangeService');

        // Add shared variables
        $this->view->addVariables(array(
            // Languages
            'language' => $code,
            'dictionary' => $this->createDictionary(),
            'currency' => $foreigner && $exchange->hasCurrency() ? $exchange->getCurrency() : $this->getCurrency(),

            'params' => $this->paramBag->getAll(),
            'locale' => $this->appConfig->getLanguage(),
            'appName' => $this->paramBag->get('appName'),
            'priceGroups' => $this->getModuleService('priceGroupService')->fetchAll(),
            'activePriceGroupId' => $this->getPriceGroupId(),
            'exchange' => $exchange,

            // Foreigner
            'foreigner' => $foreigner
        ));

        // Load defaults
        $this->loadDefaults();

        // Load language if explicitly provided
        if ($this->paramBag->has('siteLanguage')) {
            $siteLanguage = $this->paramBag->get('siteLanguage');
            $this->loadTranslations($siteLanguage);
        }

        // Define the main layout
        $this->view->setLayout('__layout__');
    }
}
