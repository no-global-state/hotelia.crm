<?php

namespace Site\Controller;

use Krystal\Validate\Pattern;

final class Contact extends AbstractSiteController
{
    /**
     * Renders a contact form
     * 
     * @return string
     */
    public function indexAction()
    {
        // If that's a POST request, then handle the submission
        if ($this->request->isPost()) {
            return $this->sendAction();
        } else {
            // Add a breadcrumb
            $this->view->getBreadcrumbBag()->addOne('Home', '/')
                                           ->addOne('Contact', '#');

            // Otherwise just render a form
            return $this->view->render('contact');
        }
    }

    /**
     * Sends a contact form
     * 
     * @return string
     */
    private function sendAction()
    {
        // Build a validator
        $formValidator = $this->createValidator(array(
            'input' => array(
                'source' => $this->request->getPost(),
                'definition' => array(
                    //'name' => new Pattern\Name(),
                    'email' => new Pattern\Email(),
                    'message' => new Pattern\Message(),
                    'captcha' => new Pattern\Captcha($this->captcha)
                )
            )
        ));

        if ($formValidator->isValid()) {
            // Do send it here, and then:
            $this->flashBag->set('success', 'Your request has been sent!');
            return '1';
        } else {
            return $formValidator->getErrors();
        }
    }
}
