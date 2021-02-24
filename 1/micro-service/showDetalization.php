<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


//    $date = $in['date'] ?? $_REQUEST['date'] ?? null;

if (isset($skip_start) && $skip_start === true) {
    
} else {
    require_once '0start.php';
}

// \f\end2('окей');

try {

    ob_start('ob_gzhandler');

    // \f\pa( $_REQUEST );

    if (isset($_REQUEST['s']) && \Nyos\Nyos::checkSecret($_REQUEST['s'], $_REQUEST['phone']) !== false) {
        
    } else {
        \f\end2('что то пошло не так (обновите страницу)', false);
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
//    $sql = 'SELECT 
//        
//            t.ctn '
//            . ' , '
//            . ' t.phone_status '
//            . ' , '
////            .' t.status '
////             .' , '
//            . ' d.id dog_id, '
//            . ' d.head dog, '
//            . ' a.access_block, '
//            . ' a.access_unblock, '
//            . ' SUM( p.paymentcurrentamt ) summa '
//            . ' FROM mod_' . \f\translit('701.beeline_phones', 'uri2') . ' t '
//            . ' LEFT JOIN mod_' . \f\translit('701.beeline_dogovors', 'uri2') . ' d ON d.id = t.dogovor_id AND d.status = :status '
//            . ' LEFT JOIN mod_' . \f\translit('701.beeline_dogovors_access', 'uri2') . ' a ON d.id = a.dogovor_id AND a.status = :status '
//            . ' LEFT JOIN mod_' . \f\translit('702.pays', 'uri2') . ' p ON t.ctn = CONCAT( 7, p.ctn ) AND p.status = :status AND p.paymentdate >= :date_start_pays '
//            . ' WHERE '
//            . ' t.ctn LIKE :phone '
//            . ' AND t.status = :status '
//            . ' GROUP BY t.ctn '
//            . ' ORDER BY t.ctn DESC'
//    ;
//
//    // \f\pa($sql);
//    $ff = $db->prepare($sql);
//
//    $ww = [
//        ':phone' => '%' . $_REQUEST['value'] . '%',
//        ':date_start_pays' => date('Y-m-d 00:00:00', strtotime(' -2 month')),
//        ':status' => 'show'
//    ];
//    // \f\pa($ww);
//    $ff->execute($ww);
//    $vv['tels'] = $ff->fetchAll();
//    \Nyos\mod\items::$search['phone'] = $_REQUEST['phone'];
//    $vv['tels'] = \Nyos\mod\items::get($db, \Nyos\api\Beeline::$mod_request, 'show', 'add_dt DESC' );


    \Nyos\api\Beeline::getTokenFromNumber($db, $_REQUEST['phone']);

    $req_nom = \f\Cash::getVar('detail_req' . $_REQUEST['phone']);

    if (empty($req_nom)) {

        $c = new \SoapClient('https://my.beeline.ru/api/SubscriberService?WSDL', array('trace' => false, 'cache_wsdl' => WSDL_CACHE_NONE));
        $opt = [
            'token' => \Nyos\api\Beeline::$token,
            'ban' => \Nyos\api\Beeline::$ban,
            // 'ctn' => $_REQUEST['phone'],
            // 'actvDate' => date('Y-m-d\TH:i:s.000', $_SERVER['REQUEST_TIME']), // 2013-04-26T00:00:00.000<
            'billDate' => date('Y-m-d\T00:00:00.000', $_SERVER['REQUEST_TIME']), // 2013-04-26T00:00:00.000<
            // 'reasonCode' => $_REQUEST['reasonCode'],
            // 'CTNList' => '<CTN>'.substr(1,10,$_REQUEST['phone']).'</CTN>',
            // 'CTNList' => '<CTN>'.$_REQUEST['phone'].'</CTN>',
            // 'CTN' => substr(1,10,$_REQUEST['phone']),
            // 'CTNList' => [ $r['phone'] ],
            'CTNList' => [$_REQUEST['phone']],
                // 'CTN' =>  $r['phone'] ,
        ];

        // \f\pa($opt);
        $vv['info']['opt'] = $opt;

        $response = $c->createBillCallsRequest($opt);
        $vv['info']['request'] = $response;

        $req_nom = $response->requestId;

        \f\Cash::setVar('detail_req' . $_REQUEST['phone'], $req_nom, 3600);

        sleep(2);
    }

    if (empty($req_nom)) {
        
    } else {


        try {

            $vv['info']['$req_nom'] = $req_nom;


            $c = new \SoapClient('https://my.beeline.ru/api/SubscriberService?WSDL', array('trace' => false, 'cache_wsdl' => WSDL_CACHE_NONE, 'exceptions' => true));

            $opt = [
                'token' => \Nyos\api\Beeline::$token,
                'requestId' => $req_nom,
                    // 'ban' => \Nyos\api\Beeline::$ban,
                    // 'ctn' => $_REQUEST['phone'],
                    // 'actvDate' => date('Y-m-d\TH:i:s.000', $_SERVER['REQUEST_TIME']), // 2013-04-26T00:00:00.000<
                    // 'billDate' => date('Y-m-d\T00:00:00.000', $_SERVER['REQUEST_TIME']), // 2013-04-26T00:00:00.000<
                    // 'reasonCode' => $_REQUEST['reasonCode'],
                    // 'CTNList' => '<CTN>'.substr(1,10,$_REQUEST['phone']).'</CTN>',
                    // 'CTNList' => $_REQUEST['phone'],
            ];

            // \f\pa($opt);

            $vv['info']['opt2'] = $opt;
            // \f\pa($opt);

            $response = $c->getBillCalls($opt);
            // \f\pa($response);

            $vv['info']['response2'] = $response;

            $vv['info']['response22'] = $response->detail->UssWsApiException->errorCode;
            // не сформирован ответ
            // [UssWsApiException] => stdClass Object
            // [errorCode] => 20017
        } catch (\Exception $exc) {
            $vv['info']['error2'] = $exc;

            if (isset($exc->detail->UssWsApiException->errorCode) && $exc->detail->UssWsApiException->errorCode == 20017) {
                $vv['error_show'] = 'Ошибка: запрос не обработан (#' . $exc->detail->UssWsApiException->errorCode . ' ' . $exc->detail->UssWsApiException->errorDescription . ' )';
            }
        } finally {
            $vv['info']['end2'] = __LINE__;
        }
    }


//    $c = new \SoapClient('https://my.beeline.ru/api/SubscriberService?WSDL', array('trace' => false, 'cache_wsdl' => WSDL_CACHE_NONE));
//    $opt = [
//        'token' => \Nyos\api\Beeline::$token,
//        // 'login' => 'A' . $ee[0]['login'],
//        'login' => \Nyos\api\Beeline::$ban,
//        'page' => 1,
//            //    'reasonCode' => 'RSBO',
//    ];
//
//    if (!empty($_REQUEST['requestId'])) {
//        $opt['requestId'] = $_REQUEST['requestId'];
//    } else {
//        $opt['startDate'] = date('Y-m-d\T00:00:00.000', $_SERVER['REQUEST_TIME'] - 3600 * 24 * 7); // 2013-04-26T00:00:00.000<
//        $opt['endDate'] = date('Y-m-d\T00:00:00.000', $_SERVER['REQUEST_TIME']); // 2013-04-26T00:00:00.000<
//    }
//
//
////    if (!empty($_REQUEST['phone']))
////        $opt['ctn'] = $_REQUEST['phone'];
//    // \f\pa($opt);
//    $response = (array) $c->getRequestList($opt);
//    // \f\pa($response);
//    // \f\pa($response['requestList']->requests);





    $twig->addGlobal('session', $_SESSION);
    $twig->addGlobal('server', $_SERVER);
    $twig->addGlobal('post', $_POST);
    $twig->addGlobal('get', $_GET);
    $twig->addGlobal('request', $_REQUEST);

    require_once DR . '/all/twig.function.php';
//    if (file_exists(DR . '/vendor/didrive/f/twig.function.php')) {
    require_once DR . '/vendor/didrive/f/twig.function.php';
//        //echo '<br/>'.__FILE__.' #'.__LINE__;
//    }

    $ttwig = $twig->loadTemplate('vendor/didrive_api/bibi/1/micro-service/tpl/show-detalization.htm');
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


