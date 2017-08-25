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
     * This action gets executed when a request to non-existing route has been made
     * 
     * @return string
     */
    public function notFoundAction()
    {
        return $this->view->render('404');
    }
}
