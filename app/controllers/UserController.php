<?php

class UserController extends \BaseController {

	/**
	 * Store a newly created user in the database.
	 *
	 * @return Response
	 */
	public function create()
	{
        $input = Input::all();
        $v = User::validate($input);

        if( $v->passes() ){
            //Create the user with sentry
            try{
                $user = Sentry::register($input);
                if( $user ){
                    //User created , return activation email.
                    $activationCode = $user->getActivationCode();

                    return Response::json(array('user_created'=>true,'activation_code'=>$activationCode));
                }
            }catch (Cartalyst\Sentry\Users\UserExistsException $e){
                return Response::json(array('user_exists'=>'User with this login already exists.'));
            }
        }else{
            return Response::json($v->messages());
        }
	}

    /**
     * Activate a user
     * @param $id
     * @param $activationCode
     * @return mixed
     */
    public function activate( $id,$activationCode ){

        try{
            $user = Sentry::findUserById($id);

            if( $user->attemptActivation($activationCode) ){

                return Response::json(array('user_activated'=>true));
            }else{
                return Response::json(array('user_activated'=>false));
            }
        }catch (Cartalyst\Sentry\Users\UserNotFoundException $e)
        {
            return Response::json(array('user_not_found'=>'User was not found.'));
        }
        catch (Cartalyst\Sentry\Users\UserAlreadyActivatedException $e)
        {
            return Response::json(array('already_activated'=>'User is already activated.'));
        }
    }

    /**
     * Login a user
     */
    public function postLogin(){

        $input = Input::all();
        $v = User::validate($input);

        if( $v->passes() ){

            try{
                // Authenticate the user
                $user = Sentry::authenticate($input, false);
                if( $user ){
                    return Response::json($user);
                }
            }
            catch (Cartalyst\Sentry\Users\WrongPasswordException $e)
            {
                return Response::json(array('wrong_password'=>'Wrong password, try again.'));
            }
            catch (Cartalyst\Sentry\Users\UserNotFoundException $e)
            {
                return Response::json(array('user_not_found'=>'User was not found.'));
            }
            catch (Cartalyst\Sentry\Users\UserNotActivatedException $e)
            {
                return Response::json(array('not_activated'=>'User not activated.'));
            }
        }else{
            return Response::json($v->messages());
        }
    }

    /**
     * Retrieve user's reset password
     */
    public function retrieveResetPasswordCode(){

        $input = Input::all();
        //Check that the email field is sent through
        $v = User::validate($input);

        if( $v->passes() ){

            try{
                // Find the user using the user email address and get password reset code
                $user = Sentry::findUserByLogin($input['email']);
                $resetCode = $user->getResetPasswordCode();

                return Response::json(array('reset_password_code'=>$resetCode,'user_id'=>$user->id));

            }catch (Cartalyst\Sentry\Users\UserNotFoundException $e)
            {
                return Response::json(array('user_not_found'=>'User was not found.'));
            }
        }else{
            return Response::json($v->messages());
        }
    }

    /**
     * Now Reset Password, user id can be sent
     */
    public function postResetPassword( $passwordResetCode, $userID ){

        //Get the user's new password
        $input = Input::all();
        $v = User::validate($input);

        if( $v->passes() ){
            try
            {
                // Find the user using the user id
                $user = Sentry::findUserById($userID);
                // Check if the reset password code is valid
                if ($user->checkResetPasswordCode($passwordResetCode))
                {
                    // Attempt to reset the user password
                    if ($user->attemptResetPassword($passwordResetCode, $input['password']))
                    {
                        return Response::json(array('password_reset_success'=>true));
                    }
                    else
                    {
                        return Response::json(array('password_reset_success'=>false));
                    }
                }
                else
                {
                    return Response::json(array('invalid_reset_code'=>'Invalid code'));
                }
            }
            catch (Cartalyst\Sentry\Users\UserNotFoundException $e)
            {
                return Response::json(array('user_not_found'=>'User was not found.'));
            }
        }else{
            return Response::json($v->messages());
        }
    }

    /**
     * List all users
     */
    public function getList(){


        $users = Sentry::findAllUsers();

        if( count($users) ){

            $userList = array();
            $eachUser = array();

            foreach( $users as $user ){
                $eachUser['first_name'] = $user->first_name;
                $eachUser['last_name'] = $user->last_name;

                array_push($userList,$eachUser);
            }

            return Response::json(array('users'=>$userList));

        }else{
            return Response::json(array('no_users'=>$users));
        }
    }

    /**
	 * Getting user data for editing.
	 * @param  int  $id
	 * @return Response
	 */
	public function getUserByID($id)
	{
        try
        {
            // Find the user by their id
            $user = Sentry::findUserById($id);

            //Only returning fields that can be updated
            $userFields['first_name'] = $user->first_name;
            $userFields['last_name'] = $user->last_name;

            return Response::json($userFields);

        }
        catch (Cartalyst\Sentry\Users\UserNotFoundException $e)
        {
            return Response::json(array('user_not_found'=>'User was not found'));
        }
	}

    /**
     * Update user data
     */
    public function postUpdate($id){

        try
        {
            // Find the user using the user id
            $user = Sentry::findUserById($id);

            // Update the user details
            $user->first_name = Input::get('first_name');
            $user->last_name = Input::get('last_name');

            // Update the user
            if ($user->save())
            {
                return Response::json(array('user_updated'=>true));
            }
        }
        catch (Cartalyst\Sentry\Users\UserNotFoundException $e)
        {
            return Response::json(array('user_not_found'=>'User was not found'));
        }

    }

    /**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function delete($id)
	{
        try
        {
            // Find the user using the user id
            $user = Sentry::findUserById($id);
            // Delete the user
            $user->delete();
            return Response::json(array('user_deleted'=>true));
        }
        catch (Cartalyst\Sentry\Users\UserNotFoundException $e)
        {
            return Response::json(array('user_not_found'=>'User was not found'));
        }
	}


}
