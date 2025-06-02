<?php

namespace App\Http\Middleware;

use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Support\Facades\Cookie;
use phpseclib3\Crypt\RSA;
use Spatie\Crypto\Rsa\PublicKey;

class CookieAuth implements AuthenticatesRequests
{
    /**
     * The authentication factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $this->authenticate($request, $guards);

        return $next($request);
    }

    /**
     * Determine if the user is logged in to any of the given guards.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $guards
     * @return void
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function authenticate($request, array $guards)
    {
        if(Cookie::get("username")) {
            return;
        }

        $this->unauthenticated($request, $guards);
    }

    /**
     * Handle an unauthenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $guards
     * @return void
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function unauthenticated($request, array $guards)
    {
        throw new AuthenticationException(
            'Unauthenticated.', $guards, $this->redirectTo($request)
        );
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        //
    }

    public static function rsaUserIntegrityCheck($username_input = null, $private_key_input = null)
    {
        if($username_input == null || $private_key_input == null)
        {
            $username_input = Cookie::get("username");
            $private_key_input = Cookie::get("private_key");
        }

        $client = new Client();
        $url = "https://thecircle-thruyou.herokuapp.com/api/key/" . $username_input;

        try {
            $response = $client->get($url, ['verify' => false]);
        } catch(\Exception $e)
        {
            return false;
        }

        $result = json_decode($response->getBody());

        if($result != null) {
            $private_key = $private_key_input;
            $public_key = $result->public_key;

            //TRUYOU INTEGRITY
            $public_key_truyou = $result->thruyou_public_key;
            $hash_truyou = $response->getHeader('x-hash');

            $public_key_truyou = RSA::loadPublicKey($public_key_truyou); //Convert key fix
            $verify_truyou = PublicKey::fromString($public_key_truyou);
            $hash_truyou_decrypted = $verify_truyou->decrypt(base64_decode(reset($hash_truyou)));
            $hash_user_info = hash("sha256", $result->username . $public_key);

            if($hash_truyou_decrypted != $hash_user_info) return false;

            //TRUYOU AUTHENTICATIE
            RSA::loadPublicKey($public_key);

            try {
                $rsa = RSA::loadPrivateKey($private_key);
            } catch (\Exception $e) {
                return false;
            }

            //MAAK EEN SIGNATURE MET DE PRIVATE KEY DIE DE USER HEEFT INGEVOERD
            $signature = $rsa->sign("test");

            //VERIFY SIGNATURE MET PUBLIC KEY, true = het is een keypair en de user mag verder, false = niet de private key van usser in db
            if ($rsa->getPublicKey()->verify("test", $signature)) {
                return true;
            }
        }
        return false;
    }
}
