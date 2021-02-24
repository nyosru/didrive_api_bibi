<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


//    $date = $in['date'] ?? $_REQUEST['date'] ?? null;

if (isset($skip_start) && $skip_start === true) {
    
} else {
    require_once '../../../../didrive/base/start-for-microservice.php';
}

// \f\end2('окей');

try {

    ob_start('ob_gzhandler');





//    $post = [
//        'skip_ob' => 'da'
//    ];
    $post = $_REQUEST;
    $res = \f\get_curl_https_uri('http://' . $_SERVER['HTTP_HOST'] . '/vendor/didrive_api/bibi/1/micro-service/api-replaceSIM.php?' . http_build_query($post));

    
    \f\pa( json_decode($res) );






    echo 'номер перенесён';

    $r = ob_get_contents();
    ob_end_clean();

        \f\end2($r, true);
        
} catch (\PDOException $exc) {

    \f\pa($exc, 2);

    $r = ob_get_contents();
    ob_end_clean();

    \f\pa($r);
} catch (\Exception $exc) {

    \f\pa($exc, 2);

    $r = ob_get_contents();
    ob_end_clean();
//
////    if (isset($_REQUEST['print']))
////        echo $r;
////
////    if (isset($_REQUEST['send']) && $_REQUEST['send'] == 'scip') {
////    } else {
////        \nyos\Msg::sendTelegramm($r, null, 2);
////    }
//
    \f\end2($r, false);
}

\f\end2('упс', false);


