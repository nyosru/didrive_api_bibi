<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


//    $date = $in['date'] ?? $_REQUEST['date'] ?? null;

if (isset($skip_start) && $skip_start === true) {
    
} else {
    require_once $_SERVER['DOCUMENT_ROOT'].'/vendor/didrive/base/start-for-microservice.php';
}

ob_start('ob_gzhandler');

    \f\pa($_REQUEST);

    
    \Nyos\api\Beeline::getTokenFromNumber($db, $_REQUEST['phone']);

    \f\pa(\Nyos\api\Beeline::$token);


    $r = ob_get_contents();
    ob_end_clean();
    \f\end2( 'окей '.$r.' '.__FILE__.' '.__LINE__ );

    
    if( 1 == 2 ){
    
    $c = new \SoapClient('https://my.beeline.ru/api/SubscriberService?WSDL', array('trace' => false, 'cache_wsdl' => WSDL_CACHE_NONE));
    $opt = [
        'token' => \Nyos\api\Beeline::$token,
        'ctn' => $_REQUEST['phone'],
        // 'serialNumber' => $request['new_sim'],
        'pricePlan' => $_REQUEST['new_tp'],
    ];

    // $response = $c->replaceSIM($opt);
    $response = $c->changePP($opt);
    // \f\pa($response);

    }
    
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










\f\end2('окей '.__FILE__.' '.__LINE__ );

//try {
//
//    ob_start('ob_gzhandler');
//    // \f\pa( $_REQUEST );
//
//    if (isset($_REQUEST['s']) && \Nyos\Nyos::checkSecret($_REQUEST['s'], $_REQUEST['phone'] ) !== false) {
//    }else{
//        \f\end2( 'что то пошло не так (обновите страницу)', false );
//    }
//
//    // $dogs = \Nyos\mod\items::get($db, '701.beeline_dogovors');    
//    // \Nyos\mod\items::$show_sql = true;
//    // \Nyos\mod\items::$search['ctn'] = $_REQUEST['value'];
//
//    $loader = new Twig_Loader_Filesystem(DR);
//    $vv = [];
//
//// инициализируем Twig
//    $twig = new Twig_Environment($loader, array(
//        'cache' => $_SERVER['DOCUMENT_ROOT'] . '/templates_c',
//        'auto_reload' => true
//            //'cache' => false,
//            // 'debug' => true
//    ));
//
//    $vv['dir_site_tpl'] = dir_site_tpl;
//
////    \Nyos\mod\items::$search['ctn'] = substr($_REQUEST['phone'],1,10);
////    $vv['pays'] = \Nyos\mod\items::get($db, \Nyos\api\Beeline::$mod_pays , 'show', 'add_dt DESC' );
//    
//    
//    
//    
//    
//    
//    
//    
//    
//    
//    
//    
//    
//    
//    
//    
//    $twig->addGlobal('session', $_SESSION);
//    $twig->addGlobal('server', $_SERVER);
//    $twig->addGlobal('post', $_POST);
//    $twig->addGlobal('get', $_GET);
//    $twig->addGlobal('request', $_REQUEST);
//
//    require_once DR . '/all/twig.function.php';
////    if (file_exists(DR . '/vendor/didrive/f/twig.function.php')) {
//    require_once DR . '/vendor/didrive/f/twig.function.php';
////        //echo '<br/>'.__FILE__.' #'.__LINE__;
////    }
//
//    $ttwig = $twig->loadTemplate('vendor/didrive_api/bibi/1/micro-service/tpl/api-form-replaceSim.htm');
//    echo $ttwig->render($vv);
//
//    $r = ob_get_contents();
//    ob_end_clean();
//
//    \f\end2($r, true, ['numbers' => ( $tel ?? [] )]);
//    
//} catch (\PDOException $exc) {
//
//    \f\pa($exc, 2);
//
////    $r = ob_get_contents();
////    ob_end_clean();
////
////    \f\pa($r);
//} catch (\Exception $exc) {
//
//    \f\pa($exc, 2);
//
////    $r = ob_get_contents();
////    ob_end_clean();
////
//////    if (isset($_REQUEST['print']))
//////        echo $r;
//////
//////    if (isset($_REQUEST['send']) && $_REQUEST['send'] == 'scip') {
//////    } else {
//////        \nyos\Msg::sendTelegramm($r, null, 2);
//////    }
////
////    \f\end2($r, false);
//}
//

\f\end2('упс', false);
