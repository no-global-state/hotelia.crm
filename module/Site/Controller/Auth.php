<?php

namespace Site\Controller;

use Krystal\Validate\Pattern;
use Site\Service\UserService;

final class Auth extends AbstractCrmController
{
    /**
     * {@inheritDoc}
     */
    protected $authActive = false;

    /**
     * Displays login form
     * 
     * @return string
     */
    public function indexAction()
    {
        if ($this->request->isPost()) {
            return $this->loginAction();
        } else {
            return $this->formAction();
        }
    }

    /**
     * Performs a logout and redirects to a home page
     * 
     * @return string
     */
    public function logoutAction()
    {
        $this->getAuthService()->logout();
        $this->response->redirect('/');
    }

    /**
     * Displays a login form
     * 
     * @return string
     */
    private function formAction()
    {
        // If trying to render login form when already logged in
        if ($this->getAuthService()->isLoggedIn()) {
            // Then simply go home
            return $this->response->redirect('/crm');
        } else {
            return $this->view->render('login');
        }
    }

    /**
     * Performs a login
     * 
     * @return string
     */
    private function loginAction()
    {
        // Build a validator
        $formValidator = $this->createValidator(array(
            'input' => array(
                'source' => $this->request->getPost(),
                'definition' => array(
                    'login' => new Pattern\Login(),
                    'password' => new Pattern\Password()
                )
            )
        ));

        if ($formValidator->isValid()) {
            // Grab request data
            $login = $this->request->getPost('login');
            $password = $this->request->getPost('password');
            $remember = (bool) $this->request->getPost('remember');

            if ($this->getAuthService()->authenticate($login, $password, $remember)) {
                // Special case for admin
                if ($this->getAuthService()->getRole() == UserService::USER_ROLE_ADMIN) {
                    return json_encode([
                        'successUrl' => $this->createUrl('Site:Property@indexAction', [null])
                    ]);
                }

                return '1';
            } else {
                // Return raw string indicating failure
                return $this->translator->translate('Invalid login or password');
            }

        } else {
            return $formValidator->getErrors();
        }
    }
}
