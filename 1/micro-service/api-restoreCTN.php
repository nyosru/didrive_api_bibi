<?php

ob_start('ob_gzhandler');

try {


//    $date = $in['date'] ?? $_REQUEST['date'] ?? null;
//
    if (empty($_REQUEST['phone']))
        throw new \Exception('нет телефона');

    if (isset($skip_start) && $skip_start === true) {
        
    } else {
        require_once '0start.php';
    }


    // \f\pa($_REQUEST);


    if (empty(\Nyos\api\Beeline::$token)) {

        \Nyos\mod\items::$type_module = 3;
        \Nyos\mod\items::$search['id'] = $_REQUEST['ban'];
        $ee = \Nyos\mod\items::get($db, '701.beeline_dogovors');

        if (empty($ee[0]))
            throw new \Exception('нет');

        // \f\pa($ee);
        \Nyos\api\Beeline::getToken($db, $ee[0]['login'], $ee[0]['pass']);
        // \Nyos\api\Beeline::getToken($db);
    }
    
} catch (\Exception $exc) {

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