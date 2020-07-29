<?php

/**
  класс модуля
 * */

namespace Nyos\api;

if (!defined('IN_NYOS_PROJECT'))
    throw new \Exception('Сработала защита от розовых хакеров, обратитесь к администрратору');

class Beeline {

    public static $token = [];
    public static $login = '';
    public static $pass = '';
    public static $ban = '';

    function __construct() {
        
    }

    static public function getToten($db, $login = '', $pass = '') {

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

        $c = new SoapClient('https://my.beeline.ru/api/AuthService?WSDL', array('trace' => false, 'cache_wsdl' => WSDL_CACHE_NONE));
        $response = $c->auth(['login' => self::$login, 'password' => self::$pass ]);
        // \f\pa($response);
        // \f\pa($c->__getLastRequestHeaders());
        // \f\pa($c->__getLastRequest());
        if (!empty($response->return)) {
            \f\Cash::setVar('bee_token', $response->return);
            self::$token = $response->return;
            return \f\end3('ok', true, ['cash' => false, 'token' => $response->return]);
        } else {
            return false;
        }
    }

    static public function getListNumber($db) {

        if( empty(self::$token) )
            if( empty(self::getToten($db)) ){
                throw new \Exception( 'нет токена' );
            }
        
        $c = new SoapClient('https://my.beeline.ru/api/SubscriberService?WSDL', array('trace' => false, 'cache_wsdl' => WSDL_CACHE_NONE));
        $response = $c->getCTNInfoList([ 'token'=>self::$token, 'ban' => self::$ban ]);
        \f\pa($response);
        
        // \f\pa($c->__getLastRequestHeaders());
        // \f\pa($c->__getLastRequest());

        // https://my.beeline.ru/api/SubscriberService?WSDL
    }

}
