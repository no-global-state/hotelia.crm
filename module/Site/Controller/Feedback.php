<?php

namespace Site\Controller;

use Krystal\Validate\Pattern;
use Site\Collection\SubjectCollection;

final class Feedback extends AbstractCrmController
{
    /**
     * Renders feedback form
     * 
     * @return string
     */
    public function indexAction() : string
    {
        return $this->view->render('feedback', [
            'subjects' => (new SubjectCollection())->getAll()
        ]);
    }

    /**
     * Submits a form
     * 
     * @return int
     */
    public function submitAction()
    {
        if ($this->request->isPost()) {
            // Post data
            $data = $this->request->getPost();

            $formValidator = $this->createValidator([
                'input' => [
                    'source' => $data,
                    'definition' => [
                        'name' => new Pattern\Name(),
                        'message' => new Pattern\Message()
                    ]
                ]
            ]);

            if ($formValidator->isValid()) {

                $this->flashBag->set('success', 'Your inqury has been sent');
                return 1;

            } else {
                return $formValidator->getErrors();
            }
        }
    }
}
