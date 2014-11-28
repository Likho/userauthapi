<?php namespace Likho\Users;

interface LikhoUserInterface{
    public function registerUser($email,$password,$firstname,$lastname);
    public function activateUser($id,$activationCode);
    public function getResetPasswordCode($email);
    public function resetPassword($passwordResetCode,$id,$password);
    public function findAllUsers();
    public function findUser($id);
    public function updateUser($id,$firstname,$lastname);
    public function deleteUser($id);
}