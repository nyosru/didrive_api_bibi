<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

if (isset($skip_start) && $skip_start === true) {
    
} else {
    require_once '../../../../didrive/base/start-for-microservice.php';
    $skip_start = false;
}

if (empty($request)) {
    $request = $_REQUEST;
}

// \f\pa($_POST);
// \f\pa($_REQUEST);
// \f\pa($request); die();

if (!isset($request['skip_ob']))
    ob_start('ob_gzhandler');


try {



//    $date = $in['date'] ?? $_REQUEST['date'] ?? null;
//
    if (empty($request['phone']))
        throw new \Exception('нет телефона');

    if (empty($request['new_sim']))
        throw new \Exception('нет симки');

    \f\pa($request);

    \Nyos\api\Beeline::getTokenFromNumber($db, $request['phone']);

    $c = new \SoapClient('https://my.beeline.ru/api/SubscriberService?WSDL', array('trace' => false, 'cache_wsdl' => WSDL_CACHE_NONE));
    $opt = [
        'token' => \Nyos\api\Beeline::$token,
        'ctn' => $request['phone'],
        'serialNumber' => $request['new_sim'],
    ];

    $response = $c->replaceSIM($opt);
    // \f\pa($response);

    if (!empty($response->return))
        $request = $response->return;

    if (!empty($request)) {

        $opt = [
            'dogovor' => \Nyos\api\Beeline::$dog_id,
            'phone' => $request['phone'],
            'tip' => 'Замена сим карты',
            'tip_eng' => 'replaceSim',
            'requestid' => $request
        ];
        \Nyos\mod\items::add($db, \Nyos\api\Beeline::$mod_request, $opt);

        sleep(5);

        $c = new \SoapClient('https://my.beeline.ru/api/SubscriberService?WSDL', array('trace' => false, 'cache_wsdl' => WSDL_CACHE_NONE));
        $opt = [
            'token' => \Nyos\api\Beeline::$token,
            'login' => \Nyos\api\Beeline::$login,
            // 'login' => \Nyos\api\Beeline::$ban,
            'page' => 1,
            'requestId' => $request,
        ];

        $response = $c->getRequestList($opt);
        \f\pa($response);

        if (!empty($response->requestList->requests->requestStatus)) {
            foreach ($response->requestList->requests as $k => $v) {

                $in[strtolower($k)] = $v;
            }
            \Nyos\mod\items::add($db, \Nyos\api\Beeline::$mod_request, $in);
        }
    }



    echo '<b>Номер ' . $request['phone'] . ' перенесён на сим ' . $request['new_sim'] . '</b>';

    if (!isset($request['skip_ob'])) {
        $r = ob_get_contents();
        ob_end_clean();
    }

    if (!empty($request['return']) && $request['return'] == 'json') {
        return json_encode(['request' => $response->return ?? '', 'other' => $r]);
    } else {
        \f\end2($r, true);
    }
} catch (\Exception $exc) {

    if (!empty($request['return']) && $request['return'] == 'json') {

        return json_encode(['error' => $exc->detail->UssWsApiException->errorDescription, 'other' => $r]);
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