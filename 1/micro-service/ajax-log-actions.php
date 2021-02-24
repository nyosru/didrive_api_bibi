<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


//    $date = $in['date'] ?? $_REQUEST['date'] ?? null;

if (isset($skip_start) && $skip_start === true) {
    
} else {
    require_once '0start.php';
}

try {

    ob_start('ob_gzhandler');

    // \f\pa( $_REQUEST );

    if (isset($_REQUEST['s']) && \Nyos\Nyos::checkSecret($_REQUEST['s'], 123 ) !== false) {
        
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

    $vv['dir_site_tpl'] = dir_site_tpl;

//    \Nyos\mod\items::$liked_or['ctn'] = $_REQUEST['value'];
//    $vv['tels'] = \Nyos\mod\items::get($db, '701.beeline_phones');
    // \f\pa($tels);

    $sql = 'SELECT 
        
            t.ctn '
            . ' , '
            . ' t.phone_status '
            . ' , '
//            .' t.status '
//             .' , '
            . ' d.id dog_id, '
            . ' d.head dog, '
            . ' a.access_block, '
            . ' a.access_unblock, '
            . ' SUM( p.paymentcurrentamt ) summa '
            . ' FROM mod_' . \f\translit('701.beeline_phones', 'uri2') . ' t '
            . ' LEFT JOIN mod_' . \f\translit('701.beeline_dogovors', 'uri2') . ' d ON d.id = t.dogovor_id AND d.status = :status '
            . ' LEFT JOIN mod_' . \f\translit('701.beeline_dogovors_access', 'uri2') . ' a ON d.id = a.dogovor_id AND a.status = :status '
            . ' LEFT JOIN mod_' . \f\translit('702.pays', 'uri2') . ' p ON t.ctn = CONCAT( 7, p.ctn ) AND p.status = :status AND p.paymentdate >= :date_start_pays '
            . ' WHERE '
            . ' t.ctn LIKE :phone '
            . ' AND t.status = :status '
            . ' GROUP BY t.ctn '
            . ' ORDER BY t.ctn DESC'
    ;

    // \f\pa($sql);
    $ff = $db->prepare($sql);

    $ww = [
        ':phone' => '%' . $_REQUEST['value'] . '%',
        ':date_start_pays' => date('Y-m-d 00:00:00', strtotime(' -2 month')),
        ':status' => 'show'
    ];
    // \f\pa($ww);
    $ff->execute($ww);
    $vv['tels'] = $ff->fetchAll();








    $twig->addGlobal('session', $_SESSION);
//$vv['session'] = $_SESSION;
    $twig->addGlobal('server', $_SERVER);
    $twig->addGlobal('post', $_POST);
    $twig->addGlobal('get', $_GET);

    require_once DR . '/all/twig.function.php';
//    if (file_exists(DR . '/vendor/didrive/f/twig.function.php')) {
    require_once DR . '/vendor/didrive/f/twig.function.php';
//        //echo '<br/>'.__FILE__.' #'.__LINE__;
//    }

    $ttwig = $twig->loadTemplate('vendor/didrive_api/bibi/1/micro-service/tpl/show_search_numbers.htm');
    echo $ttwig->render($vv);

    $r = ob_get_contents();
    ob_end_clean();

    \f\end2($r, true, ['numbers' => ( $tel ?? [] )]);
} catch (\PDOException $exc) {

    \f\pa($exc, 2);

//    $r = ob_get_contents();
//    ob_end_clean();
//
//    \f\pa($r);
} catch (\Exception $exc) {

    \f\pa($exc, 2);

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


