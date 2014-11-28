<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;

class User extends Eloquent {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';
    protected $guarded = array('id');

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array('password', 'remember_token');

    public static function validate( $input ){

        $rules = array(
            'email'=>'sometimes|required|email',
            'password'=>'sometimes|required',
        );

        $messages = array(
            'required' => 'The :attribute field is required.',
            'required.email' => 'Please provide a valid email address.'
        );

        return Validator::make($input,$rules,$messages);
    }


}
