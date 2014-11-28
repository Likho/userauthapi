<?php namespace Likho\Users;

use Cartalyst\Sentry;
use Cartalyst\Sentry\Users;
use User;
use Illuminate\Support\Facades\Response;
use Cartalyst\Sentry\Users\UserExistsException;
use Cartalyst\Sentry\Users\PasswordRequiredException;
use Cartalyst\Sentry\Users\LoginRequiredException;
use Cartalyst\Sentry\Users\UserNotFoundException;
use Cartalyst\Sentry\Users\UserAlreadyActivatedException;

class LikhoUser implements LikhoUserInterface
{

    public function __construct(\Cartalyst\Sentry\Sentry $sentry, Response $response)
    {
        $this->sentry = $sentry;
        $this->response = $response;
    }

    public function registerUser($email, $password, $firstname, $lastname)
    {
        try {
            $newUser = $this->sentry->register(array(
                'email' => $email,
                'password' => $password,
                'first_name' => $firstname,
                'last_name' => $lastname
            ));

            $activationCode = $newUser->getActivationCode();
            return $this->response->json(array('type' => 'success', 'response_body' => array('id' => $newUser->id, 'activation_code' => $activationCode)));

        } catch (LoginRequiredException $e) {
            return $this->response->json(array('type' => 'error', 'response_body' => array('login_required' => true)));
        } catch (PasswordRequiredException $e) {
            return $this->response->json(array('type' => 'error', 'response_body' => array('password_required' => true)));
        } catch (UserExistsException $e) {
            return $this->response->json(array('type' => 'error', 'response_body' => array('user_exists' => true)));
        }
    }

    public function activateUser($id, $activationCode)
    {
        try {
            $activateUser = $this->sentry->findUserById($id);

            if ($activateUser->attemptActivation($activationCode)) {

                return $this->response->json(array('type' => 'success', 'response_body' => array('activated' => true)));
            } else {
                return $this->response->json(array('type' => 'error', 'response_body' => array('activated' => false)));
            }
        } catch (UserNotFoundException $e) {
            return $this->response->json(array('type' => 'error', 'response_body' => array('user_not_found' => true)));
        } catch (UserAlreadyActivatedException $e) {
            return $this->response->json(array('type' => 'error', 'response_body' => array('already_activated' => true)));
        }
    }

    public function getResetPasswordCode($email)
    {
        try {
            // Find the user using the user email address and get password reset code
            $user = $this->sentry->findUserByLogin($email);
            $resetCode = $user->getResetPasswordCode();

            return $this->response->json(array('type' => 'success', 'response_body' => array('reset_password_code' => $resetCode, 'user_id' => $user->id)));

        } catch (UserNotFoundException $e) {
            return $this->response->json(array('type' => 'error', 'response_body' => array('user_not_found' => true)));
        }
    }

    public function resetPassword($passwordResetCode, $id, $password)
    {
        try {
            // Find the user using the user id
            $user = $this->sentry->findUserById($id);

            // Check if the reset password code is valid
            if ($user->checkResetPasswordCode($passwordResetCode)) {
                // Attempt to reset the user password
                if ($user->attemptResetPassword($passwordResetCode, $password)) {
                    return $this->response->json(array('type' => 'success', 'response_body' => array('password_reset' => true)));
                } else {
                    return $this->response->json(array('type' => 'error', 'response_body' => array('password_reset' => false)));
                }
            } else {
                return $this->response->json(array('type' => 'error', 'response_body' => array('invalid_code' => true)));
            }
        } catch (UserNotFoundException $e) {
            return $this->response->json(array('type' => 'error', 'response_body' => array('user_not_found' => true)));
        }
    }

    public function findAllUsers()
    {
        $users = $this->sentry->findAllUsers();

        if (count($users)) {

            $userList = array();
            $eachUser = array();

            foreach ($users as $user) {
                $eachUser['first_name'] = $user->first_name;
                $eachUser['last_name'] = $user->last_name;

                array_push($userList, $eachUser);
            }

            return $this->response->json(array('type' => 'success', 'response_body' => array('users' => $userList)));

        } else {

            return $this->response->json(array('type' => 'success', 'response_body' => array('users' => "No listed users")));
        }
    }

    public function findUser($id)
    {
        try {
            // Find the user by their id
            $user = $this->sentry->findUserById($id);

            //Only returning fields that can be updated
            $userFields['first_name'] = $user->first_name;
            $userFields['last_name'] = $user->last_name;

            return $this->response->json(array('type' => 'success', 'response_body' => array('user' => $userFields)));

        } catch (UserNotFoundException $e) {
            return $this->response->json(array('type' => 'error', 'response_body' => array('user_not_found' => true)));
        }
    }

    public function updateUser($id,$firstname,$lastname )
    {
        try {
            // Find the user using the user id
            $user = $this->sentry->findUserById($id);

            // Update the user details
            $user->first_name = $firstname;
            $user->last_name = $lastname;

            // Update the user
            if ($user->save()) {
                return $this->response->json(array('type' => 'success', 'response_body' => array('updated' => true)));
            }
        } catch (UserNotFoundException $e) {
            return $this->response->json(array('type' => 'error', 'response_body' => array('user_not_found' => true)));
        }
    }

    public function deleteUser($id)
    {
        try {
            // Find the user using the user id and delete
            $this->sentry->findUserById($id)->delete();

            return $this->response->json(array('type' => 'success', 'response_body' => array('deleted' => true)));
        } catch (UserNotFoundException $e) {
            return $this->response->json(array('type' => 'error', 'response_body' => array('user_not_found' => true)));
        }
    }
}