<?php

namespace Site\Controller;

use Krystal\Validate\Pattern;

final class Settings extends AbstractCrmController
{
    /**
     * Renders and changes password
     * 
     * @return mixed
     */
    public function changePasswordAction()
    {
        if ($this->request->isPost()) {
            // Request data
            $data = $this->request->getPost();

            $formValidator = $this->createValidator([
                'input' => [
                    'source' => $data,
                    'definition' => [
                        'password' => new Pattern\Password,
                        'confirmation' => new Pattern\PasswordConfirmation($data['password'])
                    ]
                ]
            ]);

            if ($formValidator->isValid()) {
                // Update current password
                $userService = $this->getModuleService('userService');
                $userService->updatePasswordById($this->getUserId(), $data['password']);

                $this->flashBag->set('success', 'Your password has been updated successfully');
                return 1;

            } else {
                return $formValidator->getErrors();
            }
            
        } else {
            // Just render empty form
            return $this->view->render('settings/change-password');
        }
    }
}
