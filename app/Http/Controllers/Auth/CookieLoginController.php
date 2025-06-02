<?php

namespace App\Http\Controllers\Auth;

use App\Http\Middleware\CookieAuth;
use App\Providers\RouteServiceProvider;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Foundation\Auth\RedirectsUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Validation\ValidationException;
use GuzzleHttp\Client;
use phpseclib3\Crypt\RSA;
use Spatie\Crypto\Rsa\PublicKey;

trait CookieLoginController
{
    use RedirectsUsers, ThrottlesLogins;

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }


        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);
        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
        ]);
    }

    protected function rsaUserIntegrityCheck(Request $request)
    {
        $client = new Client();
        $url = "https://thecircle-thruyou.herokuapp.com/api/key/" . $request->username;

        try {
            $response = $client->get($url, ['verify' => false]);
        } catch(\Exception $e)
        {
            $this->incrementLoginAttempts($request);
            return $this->sendFailedLoginResponse($request);
        }

        $result = json_decode($response->getBody());

        if($result != null) {
            $private_key = $request->private_key;
            $public_key = $result->public_key;

            //TRUYOU INTEGRITY
            $public_key_truyou = $result->thruyou_public_key;
            $hash_truyou = $response->getHeader('x-hash');

            $public_key_truyou = RSA::loadPublicKey($public_key_truyou); //Convert key fix
            $verify_truyou = PublicKey::fromString($public_key_truyou);
            $hash_truyou_decrypted = $verify_truyou->decrypt(base64_decode(reset($hash_truyou)));
            $hash_user_info = hash("sha256", $result->username . $public_key);

            if($hash_truyou_decrypted != $hash_user_info) {
                $this->incrementLoginAttempts($request);
                return $this->sendFailedLoginResponse($request);
            }

            //TRUYOU AUTHENTICATIE
            $verify = RSA::loadPublicKey($public_key);

            try {
                $sign = RSA::loadPrivateKey($private_key);

                //MAAK EEN SIGNATURE MET DE PRIVATE KEY DIE DE USER HEEFT INGEVOERD
                $signature = $sign->sign("test");

                //VERIFY SIGNATURE MET PUBLIC KEY, true = het is een keypair en de user mag verder, false = niet de private key van user in db
                if ($verify->verify("test", $signature)) {
                    return true;
                }
            } catch (\Exception $e) {
                $this->incrementLoginAttempts($request);
                return $this->sendFailedLoginResponse($request);
            }
        }
        $this->incrementLoginAttempts($request);
        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        if(Cookie::queued("username") || Cookie::get("username"))
            return true;
        else
            return false;
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return $request->only($this->username(), 'password');
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        if ($response = $this->authenticated($request, $this->guard()->user())) {
            return $response;
        }

        return $request->wantsJson()
            ? new JsonResponse([], 204)
            : redirect()->intended($this->redirectPath());
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        return redirect()->route('success');
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'username';
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        Cookie::queue(
            Cookie::forget('username')
        );

        if ($response = $this->loggedOut($request)) {
            return $response;
        }

        return $request->wantsJson()
            ? new JsonResponse([], 204)
            : redirect('/');
    }

    /**
     * The user has logged out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    protected function loggedOut(Request $request)
    {
        Cookie::queue(
            Cookie::forget('username')
        );
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }
}
