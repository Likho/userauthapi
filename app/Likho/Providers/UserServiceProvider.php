<?php namespace Likho\Providers;

use Illuminate\Support\ServiceProvider;

class UserServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->bind('Likho\Users\LikhoUserInterface', 'Likho\Users\LikhoUser');

    }


}