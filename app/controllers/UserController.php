<?php

class UserController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		//
	}

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
                    //User created , send activation via email
                    $activationCode = $user->getActivationCode();

                    return Response::json(array('user_created'=>'User created.','activation_code'=>$activationCode));
                }


            }catch (Cartalyst\Sentry\Users\LoginRequiredException $e)
            {
                return Response::json(array('login_required'=>'Login field is required.'));

            }catch (Cartalyst\Sentry\Users\PasswordRequiredException $e)
            {
                return Response::json(array('password_required'=>'Password field is required.'));
            }catch (Cartalyst\Sentry\Users\UserExistsException $e)
            {
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

                return Response::json(array('activation_success'=>'User activated successfully ..'));
            }else{
                return Response::json(array('activation_failed'=>'User activation failed ..'));
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
     * Send Password Request
     */
    public function postResetPassword(){

        $input = Input::all();
        $v = User::validate($input);

        if( $v->passes() ){

            try{

                // Find the user using the user email address
                $user = Sentry::findUserByLogin($input['email']);
                // Get the password reset code
                $resetCode = $user->getResetPasswordCode();

                return Response::json(array('reset_password_code'=>$resetCode));

            }catch (Cartalyst\Sentry\Users\UserNotFoundException $e)
            {
                return Response::json(array('user_not_found'=>'User was not found.'));
            }
        }else{
            return Response::json($v->messages());
        }
    }

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}


}
