<?php

namespace Site\Controller;

use Krystal\Application\Controller\AbstractAuthAwareController;
use Krystal\Validate\Renderer;
use Site\Collection\BookingStatusCollection;
use Site\Service\UserService;
use Site\Service\LanguageService;

abstract class AbstractCrmController extends AbstractAuthAwareController
{
    /**
     * Returns current language ID
     * 
     * @return int
     */
    protected function getCurrentLangId() : int
    {
        $cookie = $this->request->getCookieBag();

        return $cookie->has('language_id') ? $cookie->get('language_id') : 1;
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
     * Returns current user ID
     * 
     * @return int
     */
    protected function getUserId() : int
    {
        return $this->getService('Site', 'userService')->getId();
    }

    /**
     * Returns current hotel ID
     * 
     * @return integer
     */
    protected function getHotelId()
    {
        if ($this->sessionBag->has('admin_hotel_id')) {
            return $this->sessionBag->get('admin_hotel_id');
        }

        return $this->getService('Site', 'userService')->getHotelId();
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
        return $this->getService('Site', 'userService');
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
                //$this->response->setStatusCode(400);
                //die('Invalid CSRF token');
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
     * Becomes hotel administrator
     * 
     * @param int $hotelId
     * @return void
     */
    protected function becomeAdmin(int $hotelId)
    {
        $this->sessionBag->set('admin_hotel_id', $hotelId);
        $this->sessionBag->set('admin', true);
    }

    /**
     * Stops being administrator
     * 
     * @return void
     */
    protected function stopBeingAdmin()
    {
        $this->sessionBag->remove('admin_hotel_id');
        $this->sessionBag->remove('admin');
    }

    /**
     * This method automatically gets called when this controller executes
     * 
     * @param string $action Action to be invoked
     * @return void
     */
    protected function bootstrap($action)
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
            '@Site/sb-admin.css',
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

        // Load FA
        $this->view->getPluginBag()
                   ->load('font-awesome');

        $hotel = $this->getHotelData();

        // Language loading
        $bag = $this->request->getCookieBag();
        $code = $bag->has('language') ? $bag->get('language') : $this->appConfig->getLanguage();
        $this->loadTranslations($code);

        // Language collection
        $languages = $this->getModuleService('languageService')->fetchAll();

        // Add shared variables
        $this->view->addVariables(array(
            'isTranslator' => $this->getAuthService()->getRole() == UserService::USER_ROLE_TRANSLATOR,
            'extended' => true,
            'isLoggedIn' => $this->getAuthService()->isLoggedIn(),
            'hasCurrentHotel' => $this->getHotelId() !== null,
            'role' => $this->getAuthService()->getRole(),
            'name' => $this->getAuthService()->getName(),
            'locale' => $this->paramBag->has('locale') ? $this->paramBag->get('locale') : $this->appConfig->getLanguage(),
            'active' => (bool) $hotel['active'],
            'appName' => $this->paramBag->get('appName'),
            'languages' => $languages,
            'hasSystemLanguage' => LanguageService::hasSystem($languages),
            'code' => $code,
            'admin' => $this->sessionBag->get('admin'),
        ));

        // If logged as a hotel owner
        if ($this->getHotelId() !== null) {
            // Grab booking service
            $bookingService = $this->getService('Site', 'bookingService');

            $this->view->addVariables([
                // New bookings
                'newBookingsCount' => $bookingService->countByStatus($this->getHotelId(), BookingStatusCollection::STATUS_NEW),
                'newBookings' => $bookingService->findByStatus($this->getHotelId(), BookingStatusCollection::STATUS_NEW)
            ]);
        }

        // Define the main layout
        $this->view->setLayout('layouts/main');
    }
}
