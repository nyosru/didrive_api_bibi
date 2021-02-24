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

    $dogs = \Nyos\mod\items::get($db, '701.beeline_dogovors');
    //\f\pa($dogs, 2);

    \f\timer_start(77);

    $nn = 0;
    
    foreach ($dogs as $k => $dog) {

        $timer = \f\timer_stop(77, 'ar');

        // \f\pa($timer);
        
        if ($timer['sec'] > 15)
            continue;

        $cash_var = 'scan_sim_dog_' . $dog['login'];
        
        // \f\Cash::setVar($cash_var, 123, 1);
        // sleep(2);
        
        $runed = \f\Cash::getVar($cash_var);

        echo '<br/>'.$dog['login'].' ';
        var_dump($runed);
        echo '<br/>';
        
        if (!empty($runed))
            continue;

        $nn++;
        
        \f\Cash::setVar($cash_var, 123, 60 );

        // \Nyos\Msg::sendTelegramm( '(cron) скан измененией icc в '.$dog['login'], null, 2 );
        
        $uri = 'https://nomtel.ru/vendor/didrive_api/bibi/1/micro-service/api-what-sim-on-phone.php';
        $post = [
            // 'type=button 
            // 'res_to_id=stat79634482913
            // 'href_to_ajax' = '/vendor/didrive_api/bibi/1/micro-service/api-what-sim-on-phone.php'
            // 'xphone=79634482913
            'ban' => $dog['login'],
            'show' => 'html',
            // 's' => \Nyos\Nyos::creatSecret( $dog['login'] )
        ];
        $res = \f\get_curl_https_uri($uri, $post);
        //\f\pa($res);
        \f\pa(json_decode($res));
    }

    echo 'Обработок: '.$nn;
    
    if (1 == 2) {
        // \f\pa( $_REQUEST );
//    if (isset($_REQUEST['s']) && \Nyos\Nyos::checkSecret($_REQUEST['s'], $_REQUEST['phone'] ) !== false) {
//        
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
//    $ttwig = $twig->loadTemplate('vendor/didrive_api/bibi/1/micro-service/tpl/showFormMoveSim.htm');
//    echo $ttwig->render($vv);
    }

    $r = ob_get_contents();
    ob_end_clean();

    if (isset($_REQUEST['show']) && $_REQUEST['show'] == 'html')
        die( $r);

    \f\end2($r, true, ['numbers' => ( $tel ?? [] )]);
    
} catch (\PDOException $exc) {

    //  \f\pa($exc, 2);

    echo $exc->getMessage();

    $r = ob_get_contents();
    ob_end_clean();
//
//    \f\pa($r);
} catch (\Exception $exc) {

    // \f\pa($exc, 2);

    echo $exc->getMessage();

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
//    \f\end2($r, false);
}

die('упс ошибочка ' . $r);


