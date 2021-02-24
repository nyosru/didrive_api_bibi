<?php

if (isset($skip_start) && $skip_start === true) {
    
} else {

    ini_set('error_reporting', E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    require_once '../../../../didrive/base/start-for-microservice.php';
    $skip_start = false;
    
}

if (empty($request)) {
    $request = $_REQUEST;
}

// \f\pa($request);

if (!isset($request['skip_ob']))
    ob_start('ob_gzhandler');

try {

//    $date = $in['date'] ?? $_REQUEST['date'] ?? null;

//    if (empty($request['phone']))
//        throw new \Exception('нет телефона');

    // \f\pa($request);

    if (empty(\Nyos\api\Beeline::$token)) {

        \Nyos\mod\items::$type_module = 3;
        \Nyos\mod\items::$search['id'] = $request['ban'];
        $ee = \Nyos\mod\items::get($db, '701.beeline_dogovors');

        if (empty($ee[0]))
            throw new \Exception('нет');

        // \f\pa($ee);
        \Nyos\api\Beeline::getToken($db, $ee[0]['login'], $ee[0]['pass']);
        // \Nyos\api\Beeline::getToken($db);
    }

    $c = new \SoapClient('https://my.beeline.ru/api/SubscriberService?WSDL', array('trace' => false, 'cache_wsdl' => WSDL_CACHE_NONE));

    // \f\pa($opt);
    // блокировка
    if (!empty($request['request_type']) && $request['request_type'] == 'suspendCTN') {
        $opt = [
            'token' => \Nyos\api\Beeline::$token,
            'ctn' => $request['phone'],
            // 'actvDate' => date('Y-m-d\TH:i:s.000', $_SERVER['REQUEST_TIME']), // 2013-04-26T00:00:00.000<
            'actvDate' => date('Y-m-d\T00:00:00.000', $_SERVER['REQUEST_TIME']), // 2013-04-26T00:00:00.000<
            'reasonCode' => $request['reasonCode'],
        ];
        $response = $c->suspendCTN($opt);
    }

    // раз блокировка
    elseif (!empty($request['request_type']) && $request['request_type'] == 'restoreCTN') {
        $opt = [
            'token' => \Nyos\api\Beeline::$token,
            'ctn' => $request['phone'],
            // 'actvDate' => date('Y-m-d\TH:i:s.000', $_SERVER['REQUEST_TIME']), // 2013-04-26T00:00:00.000<
            'actvDate' => date('Y-m-d\T00:00:00.000', $_SERVER['REQUEST_TIME']), // 2013-04-26T00:00:00.000<
            'reasonCode' => $request['reasonCode'],
        ];
        $response = $c->restoreCTN($opt);
    }

    // получение данных по ранее отправленному запросу
    elseif (!empty($request['request_type']) && $request['request_type'] == 'getRequestList') {
        $opt = [
            'token' => \Nyos\api\Beeline::$token,
            'login' => \Nyos\api\Beeline::$login,
            // 'ctn' => $request['phone'],
            // 'actvDate' => date('Y-m-d\TH:i:s.000', $_SERVER['REQUEST_TIME']), // 2013-04-26T00:00:00.000<
            // 'actvDate' => date('Y-m-d\T00:00:00.000', $_SERVER['REQUEST_TIME']), // 2013-04-26T00:00:00.000<
            'requestId' => $request['requestId'],
            'page' => 1,
        ];
        $response = $c->getRequestList($opt);
        // \f\pa($response);
        //echo __FILE__.' #'.__LINE__;
        
    }

    // \f\pa($response);
//    if( isset( $response->return ) )
//        \Nyos\mod\items:: if( isset( $response->return ) );
//    
//        $c = new \SoapClient('https://my.beeline.ru/api/SubscriberService?WSDL', array('trace' => false, 'cache_wsdl' => WSDL_CACHE_NONE));
//        $response = $c->auth(['login' => self::$login, 'password' => self::$pass ]);
    // \f\pa($response);
    // \f\pa($c->__getLastRequestHeaders());
    // \f\pa($c->__getLastRequest());
    // if (!empty($response->return)) {
//    

    if (!isset($request['skip_ob'])) {
        $r = ob_get_contents();
        ob_end_clean();
    }

    if (!empty($request['return']) && $request['return'] == 'json') {

        return json_encode([
            'request' => ( $response->return ?? ( $response ?? '' ) ),
            // 'other' => ( $r ?? [] ),
            // 'res_request' => ( $response_req ?? [] )
        ]);
    } else {
        \f\end2($r, true, $response);
    }
} catch (\Exception $exc) {

    if (!empty($request['return']) && $request['return'] == 'json') {

        return json_encode(['error' => (!empty($exc->detail->UssWsApiException->errorDescription) ? $exc->detail->UssWsApiException->errorDescription : 'ошибка' ), 'all' => $exc, 'other' => $r]);
    } else {

        \f\pa($exc);

        $msg = '';

        $r = ob_get_contents();
        ob_end_clean();

        \nyos\Msg::sendTelegramm($r, null, 2);

        \f\end2($r, false);

        if (isset($exc->detail->UssWsApiException->errorCode))
            $msg .= ' ошибка #' . $exc->detail->UssWsApiException->errorCode . ' / ';

        if (isset($exc->detail->UssWsApiException->errorDescription))
            $msg .= $exc->detail->UssWsApiException->errorDescription;

        // \f\end2($msg . ' ( #' . $exc->getCode() . ' ' . $exc->getMessage() . ' )', false, (array) $exc);
//    echo '<pre>';
//    print_r($exc);
//    echo '</pre>';
        // echo $exc->getTraceAsString();
    }
}