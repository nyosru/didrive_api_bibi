<?php

//use \SoapClient;

/**
  класс модуля
 * */

namespace Nyos\api;

//if (!defined('IN_NYOS_PROJECT'))
//    throw new \Exception('Сработала защита от розовых хакеров, обратитесь к администрратору');

class Beeline
{

    public static $token = [];
    public static $login = '';
    public static $pass = '';
    public static $ban = '';

    // function __construct() {
    // }

    static public function lara__getToken($login = '', $pass = '')
    {

        if (!empty($login))
            self::$login = $login;

        if (!empty($pass))
            self::$pass = $pass;

        try {

            //code...
            $c = new \SoapClient('https://my.beeline.ru/api/AuthService?WSDL', array('trace' => false, 'cache_wsdl' => WSDL_CACHE_NONE));

            dd( [ 
                $c->__getTypes() , '111',
                // $c->__getFunctions() , '222',
                // // $c->__call() , 
                // // $c->__doRequest() , 
                // $c->__getFunctions(), ' - Returns list of SOAP functions' , 
                $c->__getLastRequest(), ' - Returns last SOAP request ' , 
                // $c->__getLastRequestHeaders() , ' - Returns last SOAP request headers ' , 
                $c->__getLastResponse()  , ' - Returns last SOAP response ' , 
                // $c->__getLastResponseHeaders() , ' - Returns last SOAP response headers' , 
                // $c->__getTypes(), ' - Returns list of SOAP types ' , 
                // $c->__setCookie() , ' - Sets the cookie that will be sent with the SOAP request ' , 
                // $c->__soapCall() , ' - Calls a SOAP function ' ,
            
                self::$login, 
                self::$pass 
                ] );

            $response = $c->auth(['login' => self::$login, 'password' => self::$pass]);


        } catch (\Exception $ex) {

            // dd($ex);
            $response = $ex;

        } 
        // catch (\Throwable $th) {
        //     //throw $th;
        // }


        return [ 
            ( $response ?? 'x' ) , 
            [ 'login' => self::$login, 'pass' => self::$pass] 
        ];
    }



    static public function getToken($db, $login = '', $pass = '')
    {

        $cash_var = 'bee_token';

        if (!empty($cash_var)) {

            $vv = \f\Cash::getVar($cash_var);

            if (!empty($vv)) {
                self::$token = $vv;
                // echo '<br/>#'.__LINE__;
                // return $vv;
                return \f\end3('ok', true, ['cash' => true, 'token' => $vv]);
            }
        }

        if (empty(self::$login))
            throw new \Exception('нет логина');

        $c = new \SoapClient('https://my.beeline.ru/api/AuthService?WSDL', array('trace' => false, 'cache_wsdl' => WSDL_CACHE_NONE));
        $response = $c->auth(['login' => self::$login, 'password' => self::$pass]);
        // \f\pa($response);
        // \f\pa($c->__getLastRequestHeaders());
        // \f\pa($c->__getLastRequest());
        if (!empty($response->return)) {

            if (!empty($cash_var))
                \f\Cash::setVar($cash_var, $response->return, 6200);

            self::$token = $response->return;

            return \f\end3('ok', true, ['cash' => false, 'token' => $response->return]);
        } else {
            return false;
        }
    }

    static public function getListNumber($db)
    {

        self::getToken($db);

        if (empty(self::$token))
            if (empty(self::getToken($db))) {
                throw new \Exception('нет токена');
            }

        // \f\pa(self::$token);

        $c = new \SoapClient('https://my.beeline.ru/api/SubscriberService?WSDL', array('trace' => false, 'cache_wsdl' => WSDL_CACHE_NONE));
        $response = $c->getCTNInfoList(['token' => self::$token, 'ban' => self::$ban]);
        // \f\pa($response);

        // \f\pa($c->__getLastRequestHeaders());
        // \f\pa($c->__getLastRequest());

        // https://my.beeline.ru/api/SubscriberService?WSDL

        return $response;
    }
}
