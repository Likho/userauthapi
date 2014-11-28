<?php

class UserController extends \BaseController
{

    public function __construct(\Likho\Users\LikhoUserInterface $user)
    {
        $this->user = $user;
    }

    /**
     * Store a newly created user in the database.
     * @return Response
     */
    public function create()
    {
        $input = Input::all();
        $v = User::validate($input);

        if ($v->passes()) {
            //Create user
            $user = $this->user->registerUser(Input::get('email'), Input::get('password'), Input::get('first_name'),Input::get('last_name'));
            return $user;
        } else {
            return Response::json($v->messages());
        }
    }

    /**
     * Activate a user
     * @param $id
     * @param $activationCode
     * @return mixed
     */
    public function activate($id, $activationCode)
    {
        return $this->user->activateUser($id, $activationCode);
    }

    /**
     * Retrieve user's reset password
     */
    public function retrieveResetPasswordCode()
    {

        $input = Input::all();
        //Check that the email field is sent through
        $v = User::validate($input);

        if ($v->passes()) {
            return $this->user->getResetPasswordCode(Input::get('email'));
        } else {
            return Response::json($v->messages());
        }
    }

    /**
     * Now Reset Password, user id can be sent
     */
    public function postResetPassword($passwordResetCode, $userID)
    {
        //Get the user's new password
        $input = Input::all();
        $v = User::validate($input);

        if ($v->passes()) {
            return $this->user->resetPassword($passwordResetCode, $userID, Input::get('password'));
        } else {
            return Response::json($v->messages());
        }
    }

    /**
     * List all users
     */
    public function getList()
    {
        return $this->user->findAllUsers();
    }

    /**
     * Getting user data for editing.
     * @param  int $id
     * @return Response
     */
    public function getUserByID($id)
    {
        return $this->user->findUser($id);
    }

    /**
     * Update user data
     */
    public function postUpdate($id)
    {

        return $this->user->updateUser($id,Input::get('first_name'),Input::get('last_name'));

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function delete($id)
    {

    }

    /**
     * Login a user
     */
    public function postLogin()
    {

        $input = Input::all();
        $v = User::validate($input);

        if ($v->passes()) {

            try {
                // Authenticate the user
                $user = Sentry::authenticate($input, false);
                if ($user) {
                    return Response::json($user);
                }
            } catch (Cartalyst\Sentry\Users\WrongPasswordException $e) {
                return Response::json(array('type' => 'error', 'response_body' => array('incorrect_password' => true)));
            } catch (Cartalyst\Sentry\Users\UserNotFoundException $e) {
                return Response::json(array('type' => 'error', 'response_body' => array('user_not_found' => true)));
            } catch (Cartalyst\Sentry\Users\UserNotActivatedException $e) {
                return Response::json(array('type' => 'error', 'response_body' => array('activated' => false)));
            }
        } else {
            return Response::json($v->messages());
        }
    }


}
