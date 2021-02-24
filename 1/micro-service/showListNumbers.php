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

    //\f\pa( $_REQUEST );

    if (isset($_REQUEST['s']) && \Nyos\Nyos::checkSecret($_REQUEST['s'], 123) !== false) {
        
    } else {
        \f\end2('что то пошло не так (обновите страницу)', false);
    }

    \f\timer_start(112);

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






    $vv['gsm_accesses'] = [
        [
            '_txt' => 'Доступен',
            '_access' => 'reg_on',
            '_class' => 'btn-success'
        ]
        ,
        [
            '_txt' => 'Забронирован',
            '_access' => 'reg_bron',
            '_class' => 'btn-warning'
        ]
        ,
        [
            '_txt' => 'Не Доступен',
            '_access' => 'reg_off',
            '_class' => 'btn-danger'
        ]
        ,
        [
            '_txt' => 'Зареген',
            '_access' => 'reg_ok',
            '_class' => 'btn-info'
        ]
    ];



//if( strlen($_REQUEST['value']) < 3 )
//\f\end2('Введите 3 цифры',false);

//    \Nyos\mod\items::$liked_or['ctn'] = $_REQUEST['value'];
//    $vv['tels'] = \Nyos\mod\items::get($db, '701.beeline_phones');
    // \f\pa($tels);

    $sql = 'SELECT '

            . ' t.id '
            . ' , '
            . ' t.ctn '
            . ' , '
            . ' t.phone_status '
            . ' , '
            . ' t.price '
            . ' , '
//            .' t.status '
//             .' , '
            . ' d.id dog_id, '
            . ' d.head dog, '
            . ' d.login dog_login, '
            . ' a.access_block, '
            . ' a.access_unblock, '
            . ' SUM( p.paymentcurrentamt ) summa ,'
            . ' ac.reg_status access '
            . ' FROM '. \f\db_table('701.beeline_phones') 
            // .' mod_' . \f\translit('701.beeline_phones', 'uri2') 
            . ' t '

            . ' LEFT JOIN mod_' . \f\translit('701.beeline_dogovors', 'uri2') . ' d ON d.id = t.dogovor_id AND d.status = :status '
            . ' LEFT JOIN mod_' . \f\translit('701.beeline_dogovors_access', 'uri2') . ' a ON d.id = a.dogovor_id AND a.status = :status '

//            . ' LEFT JOIN mod_' . \f\translit('702.pays', 'uri2') . ' p ON t.ctn = CONCAT( 7, p.ctn ) AND p.status = :status AND p.paymentdate >= :date_start_pays '
//            . ' LEFT JOIN mod_' . \f\translit(\Nyos\api\Beeline::$mod_phones_reg_status, 'uri2') . ' ac ON ac.ctn = t.ctn AND ac.status = :status '

            . ( 
                ( !empty( $_REQUEST['_pays'] ) && $_REQUEST['_pays'] == 'on' ) 
                // ? ' INNER JOIN mod_' . \f\translit('702.pays', 'uri2') . ' p ON t.ctn = CONCAT( 7, p.ctn ) AND p.status = :status AND p.paymentdate >= :date_start_pays AND p.paymentcurrentamt IS NOT NULL '
                ? ' INNER JOIN '. \f\db_table('702.pays').' '
                    // .'mod_' . \f\translit('702.pays', 'uri2') 
                    . ' p ON t.ctn LIKE \'%p.ctn%\' AND p.status = :status AND p.paymentdate >= :date_start_pays AND p.paymentcurrentamt IS NOT NULL '
                // : ' LEFT JOIN mod_' . \f\translit('702.pays', 'uri2') . ' p ON t.ctn = CONCAT( 7, p.ctn ) AND p.status = :status AND p.paymentdate >= :date_start_pays '
                : ' LEFT JOIN '. \f\db_table('702.pays').' '
                    // .'mod_' . \f\translit('702.pays', 'uri2') 
                    . ' p ON t.ctn LIKE \'%p.ctn\' AND p.status = :status AND p.paymentdate >= :date_start_pays '
            )
            . ( 
                ( !empty( $_REQUEST['_avalible'] ) && $_REQUEST['_avalible'] != 'all' ) 
                ? ' INNER JOIN mod_' . \f\translit( \Nyos\api\Beeline::$mod_phones_reg_status , 'uri2') . ' ac ON ac.ctn = t.ctn AND ac.status = :status AND reg_status = :reg ' 
                : ' LEFT JOIN mod_' . \f\translit( \Nyos\api\Beeline::$mod_phones_reg_status , 'uri2') . ' ac ON ac.ctn = t.ctn AND ac.status = :status ' 
            )

            . ' WHERE '
            . ' t.ctn LIKE :phone '
            . ' AND t.status = :status '
            . ' GROUP BY t.ctn '
            . ' ORDER BY t.ctn DESC'
    //. ' LIMIT 100'
    ;
    
    // \f\timer_start(112);
    
     // echo '<p>'.$sql.'</p>';
     
    $ff = $db->prepare($sql);

    $ww = [
        ':phone' => '%' . $_REQUEST['value'] . '%',
        ':date_start_pays' => date('Y-m-d 00:00:00', strtotime(' -2 month')),
        ':status' => 'show'
    ];

    if (!empty($_REQUEST['_avalible']) && $_REQUEST['_avalible'] != 'all')
        $ww[':reg'] = $_REQUEST['_avalible'];

    // \f\pa($ww);
    $ff->execute($ww);
    $vv['tels'] = $ff->fetchAll();

    //echo '<br/>' . \f\timer_stop(11);

    // $twig->addGlobal('session', $_SESSION);
    //$vv['session'] = $_SESSION;
    // $twig->addGlobal('server', $_SERVER);
    // $twig->addGlobal('post', $_POST);
    $twig->addGlobal('get', $_GET);
    $twig->addGlobal('request', $_REQUEST);

    require_once DR . '/all/twig.function.php';
//    if (file_exists(DR . '/vendor/didrive/f/twig.function.php')) {
    require_once DR . '/vendor/didrive/f/twig.function.php';
//        //echo '<br/>'.__FILE__.' #'.__LINE__;
//    }

    //\f\timer_start(112);
    
    $ttwig = $twig->loadTemplate('vendor/didrive_api/bibi/1/micro-service/tpl/show_search_numbers.htm');
    echo $ttwig->render($vv);

    echo '<div class="d-none d-lg-block" style="position: fixed; bottom: 0px; right: 10px; background-color: rgba(255,255,255,0.5); text-align:right; font-size: 10px; color: gray;" >' . \f\timer_stop(112) .'</div>';
    
    $r = ob_get_contents();
    ob_end_clean();

    \f\end2($r, true, ['numbers' => ( $tel ?? [] )]);
} catch (\PDOException $exc) {

//    \f\pa($exc, 2);

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


