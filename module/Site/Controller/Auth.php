<?php

namespace Site\Controller;

use Krystal\Validate\Pattern;

final class Auth extends AbstractSiteController
{
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
            return $this->response->redirect('/');
        } else {
            // Add a breadcrumb
            $this->view->getBreadcrumbBag()->addOne('Home', '/')
                                           ->addOne('Login', '#');

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
                    'email' => new Pattern\Email(),
                    'password' => new Pattern\Password()
                )
            )
        ));

        if ($formValidator->isValid()) {

            // Grab request data
            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');
            $remember = (bool) $this->request->getPost('remember');

            if ($this->getAuthService()->authenticate($email, $password, $remember)) {
                return '1';
            } else {
                // Return raw string indicating failure
                return $this->translator->translate('Invalid email or password');
            }
        } else {
            return $formValidator->getErrors();
        }
    }
}
