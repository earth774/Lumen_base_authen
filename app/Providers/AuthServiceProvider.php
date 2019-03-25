<?php

namespace App\Providers;

use App\User;
use Firebase\JWT\JWT;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        // $this->app['auth']->viaRequest('api', function ($request) {
        //     if ($request->input('api_token')) {
        //         return User::where('api_token', $request->input('api_token'))->first();
        //     }
        // });

        $this->app['auth']->viaRequest('api', function ($request) {
            $token = $request->header('Authorization');
            $token = str_replace('Bearer ', '', $token);
            if (!$token) {
                // Unauthorized response if token not there
                return null;
            }

            try {
                $credentials = JWT::decode($token, env('JWT_SECRET'), ['HS256']);
            } catch (Exception $e) {
                return null;
            }

            $user = User::find($credentials->sub);
            return $user;
        });
    }
}
