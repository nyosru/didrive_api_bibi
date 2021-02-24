<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


//    $date = $in['date'] ?? $_REQUEST['date'] ?? null;

if (isset($skip_start) && $skip_start === true) {
    
} else {
    require_once $_SERVER['DOCUMENT_ROOT'].'/vendor/didrive/base/start-for-microservice.php';
}

// \f\end2('окей');

    ob_start('ob_gzhandler');


    //var_dump($db);
    
try {

    // \f\pa( $_REQUEST );

    if (isset($_REQUEST['s']) && \Nyos\Nyos::checkSecret($_REQUEST['s'], $_REQUEST['phone'] ) !== false) {
    }else{
        \f\end2( 'что то пошло не так (обновите страницу)', false );
    }

    // $dogs = \Nyos\mod\items::get($db, '701.beeline_dogovors');    
    // \Nyos\mod\items::$show_sql = true;
    // \Nyos\mod\items::$search['ctn'] = $_REQUEST['value'];

    $loader = new Twig_Loader_Filesystem(DR);
    $vv = [];

// инициализируем Twig
    $twig = new Twig_Environment($loader, array(
        'cache' => $_SERVER['DOCUMENT_ROOT'] . '/templates_c',
        'auto_reload' => true
            //'cache' => false,
            // 'debug' => true
    ));

    // $vv['dir_site_tpl'] = dir_site_tpl;

//    \Nyos\mod\items::$search['ctn'] = substr($_REQUEST['phone'],1,10);
//    $vv['pays'] = \Nyos\mod\items::get($db, \Nyos\api\Beeline::$mod_pays , 'show', 'add_dt DESC' );
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    $twig->addGlobal('db', $db);
    $twig->addGlobal('session', $_SESSION);
    $twig->addGlobal('server', $_SERVER);
    $twig->addGlobal('post', $_POST);
    $twig->addGlobal('get', $_GET);
    $twig->addGlobal('request', $_REQUEST);

    require_once DR . '/all/twig.function.php';
//    if (file_exists(DR . '/vendor/didrive/f/twig.function.php')) {
    require_once DR . '/vendor/didrive/f/twig.function.php';
    
    require_once DR . '/vendor/didrive_mod/items/3/twig.function.php';
    
//        //echo '<br/>'.__FILE__.' #'.__LINE__;
//    }

    $ttwig = $twig->loadTemplate('vendor/didrive_api/bibi/1/micro-service/tpl/api-form-replaceSim.htm');
    echo $ttwig->render($vv);

    $r = ob_get_contents();
    ob_end_clean();

    \f\end2($r, true, ['numbers' => ( $tel ?? [] )]);
    
} catch (\PDOException $exc) {
    
    
    \f\pa($exc, 2);

    $r = ob_get_contents();
    ob_end_clean();

    \f\end2($r, true, ['numbers' => ( $tel ?? [] )]);

//    $r = ob_get_contents();
//    ob_end_clean();
//
//    \f\pa($r);
} catch (\Exception $exc) {
    
    
    \f\pa($exc, 2);

    $r = ob_get_contents();
    ob_end_clean();

    \f\end2($r, true, ['numbers' => ( $tel ?? [] )]);

    
    
//    $r = ob_get_contents();
//    ob_end_clean();
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

\f\end2('упс', false);


