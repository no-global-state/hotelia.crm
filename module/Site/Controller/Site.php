<?php

namespace Site\Controller;

final class Site extends AbstractSiteController
{
    /**
     * Renders a CAPTCHA
     * 
     * @return void
     */
    public function captchaAction()
    {
        $this->captcha->render();
    }

    /**
     * Shows a home page
     * 
     * @return string
     */
    public function indexAction()
    {
        return $this->view->render('home');
    }

    /**
     * This simple action demonstrates how to deal with variables in route maps
     * 
     * @param string $name
     * @return string
     */
    public function helloAction($name)
    {
        return $this->view->render('hello', array(
            'name' => urldecode($name)
        ));
    }

    /**
     * This action gets executed when a request to non-existing route has been made
     * 
     * @return string
     */
    public function notFoundAction()
    {
        return $this->view->render('404');
    }
}
