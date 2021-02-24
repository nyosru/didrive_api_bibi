<?php

//    $date = $in['date'] ?? $_REQUEST['date'] ?? null;
//

if (isset($skip_start) && $skip_start === true) {
    
} else {
    require_once '0start.php';
}


try {

    \f\Cash::$cancel_memcash = true;

    if (empty($_REQUEST['phone']))
        throw new \Exception('нет телефона');

// получаем токен
    if (1 == 1) {
        // \Nyos\mod\items::$search['who_added'] = $_SESSION['now_user_di']['id'];
        \Nyos\mod\items::$search['id'] = $_REQUEST['ban'];
        \Nyos\mod\items::$type_module = 3;
        $dogs = \Nyos\mod\items::get($db, '701.beeline_dogovors');

        // \f\end2('asd',true,$dogs);

        if (!empty($dogs)) {
            foreach ($dogs as $v) {

                if (empty($v['login']) || empty($v['pass']))
                    throw new \Exception('что то пошло не так #' . __LINE__ . ', повторите и обратитесь в тех. поддержку', false);

                \Nyos\api\Beeline::$login = trim('A' . $v['login']);
                \Nyos\api\Beeline::$ban = trim($v['login']);
                \Nyos\api\Beeline::$pass = trim($v['pass']);

                break;
            }
        }

        \Nyos\api\Beeline::getToken($db);

        $r = $_REQUEST;
        // $r['phone'] = substr(1,10,$_REQUEST['phone']);
//        if (empty(\Nyos\api\Beeline::$token)){
//            if (empty(\Nyos\api\Beeline::getToken($db)))
//                throw new \Exception('нет токена');
//        }
        // \f\pa(\Nyos\api\Beeline::$token);
    }

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
        'CTNList' => $r['phone'],
            // 'CTN' =>  $r['phone'] ,
    ];

    \f\pa($opt);
    $response = $c->createBillCallsRequest($opt);
    \f\pa($response);

    $req_nom = $response->requestId;

    sleep(1);

    $r2 = '';

    ob_start('ob_gzhandler');

    try {

        $c = new \SoapClient('https://my.beeline.ru/api/SubscriberService?WSDL', array('trace' => false, 'cache_wsdl' => WSDL_CACHE_NONE, 'exceptions' => true));
    } catch (\Exception $ex) {

        if (!empty($ex->detail)) {
            \f\pa($ex->detail);
        } else {
            \f\pa($ex);
        }
    }

    $opt = [
        'token' => \Nyos\api\Beeline::$token,
        'requestId' => $_REQUEST['req_nom'] ?? $req_nom,
            // 'ban' => \Nyos\api\Beeline::$ban,
            // 'ctn' => $_REQUEST['phone'],
            // 'actvDate' => date('Y-m-d\TH:i:s.000', $_SERVER['REQUEST_TIME']), // 2013-04-26T00:00:00.000<
            // 'billDate' => date('Y-m-d\T00:00:00.000', $_SERVER['REQUEST_TIME']), // 2013-04-26T00:00:00.000<
            // 'reasonCode' => $_REQUEST['reasonCode'],
            // 'CTNList' => '<CTN>'.substr(1,10,$_REQUEST['phone']).'</CTN>',
            // 'CTNList' => $_REQUEST['phone'],
    ];

    \f\pa($opt);

    try {

        // \f\pa($opt);
        $response = $c->getBillCalls($opt);
        \f\pa($response);

        // \f\pa($c->__getLastRequest());
    } catch (\Exception $ex) {

//        if (!empty($ex->detail)) {
//            \f\pa($ex->detail);
//        } else {
        \f\pa($ex, 2);
//        }
    }

    $r2 = ob_get_contents();
    ob_end_clean();

    \f\end2('номер отчёта ' . $response->requestId . ' ' . $r2, true, [$r, $opt]);

//    // ob_start('ob_gzhandler');
//
//    \f\pa($_REQUEST);
//
//    if (empty(\Nyos\api\Beeline::$token)) {
//
//        \Nyos\mod\items::$type_module = 3;
//        \Nyos\mod\items::$search['id'] = $_REQUEST['ban'];
//        $ee = \Nyos\mod\items::get($db, '701.beeline_dogovors');
//
//        \f\pa($ee);
//
//        if (empty($ee[0]))
//            throw new \Exception('нет');
//
//        // \f\pa($ee);
//        \Nyos\api\Beeline::getToken($db, $ee[0]['login'], $ee[0]['pass']);
//        // \Nyos\api\Beeline::getToken($db);
//        
//    }
//
//    \f\pa(\Nyos\api\Beeline::$token);
//    
//    
//    $c = new \SoapClient('https://my.beeline.ru/api/SubscriberService?WSDL', array('trace' => false, 'cache_wsdl' => WSDL_CACHE_NONE));
//    $opt = [
//        'token' => \Nyos\api\Beeline::$token,
//        'ctn' => $_REQUEST['phone'],
//        // 'actvDate' => date('Y-m-d\TH:i:s.000', $_SERVER['REQUEST_TIME']), // 2013-04-26T00:00:00.000<
//        'actvDate' => date('Y-m-d\T00:00:00.000', $_SERVER['REQUEST_TIME']), // 2013-04-26T00:00:00.000<
//        'reasonCode' => $_REQUEST['reasonCode'],
//    ];
//    
//    \f\pa($opt);
//    $response = $c->suspendCTN($opt);
//    \f\pa($response);
//
//
////    if( isset( $response->return ) )
////        \Nyos\mod\items:: if( isset( $response->return ) );
//    
//    
////    
////        $c = new \SoapClient('https://my.beeline.ru/api/SubscriberService?WSDL', array('trace' => false, 'cache_wsdl' => WSDL_CACHE_NONE));
////        $response = $c->auth(['login' => self::$login, 'password' => self::$pass ]);
//    // \f\pa($response);
//    // \f\pa($c->__getLastRequestHeaders());
//    // \f\pa($c->__getLastRequest());
//    // if (!empty($response->return)) {
////    
//
//    $r = ob_get_contents();
//    ob_end_clean();
//
//    \f\end2($r, true);
//    \f\end2('(обработано) номеров в договоре 1', true);
//
//
//    if (\Nyos\Nyos::checkSecret($_REQUEST['s'], $_REQUEST['id'])) {
//        
//    } else {
//        throw new \Exception('что то пошло не так, повторите и обратитесь в тех. поддержку', false);
//    }
//
//    \Nyos\mod\items::$search['who_added'] = $_SESSION['now_user_di']['id'];
//    \Nyos\mod\items::$search['id'] = $_REQUEST['id'];
//    \Nyos\mod\items::$type_module = 3;
//    $dogs = \Nyos\mod\items::get($db, '701.beeline_dogovors');
//
//    // \f\end2('asd',true,$dogs);
//
//    if (!empty($dogs)) {
//        foreach ($dogs as $v) {
//
//            if (empty($v['login']) || empty($v['pass']))
//                throw new \Exception('что то пошло не так #' . __LINE__ . ', повторите и обратитесь в тех. поддержку', false);
//
//            \Nyos\api\Beeline::$login = trim('A' . $v['login']);
//            \Nyos\api\Beeline::$ban = trim($v['login']);
//            \Nyos\api\Beeline::$pass = trim($v['pass']);
//
//            break;
//        }
//    }
//
////    $list = \Nyos\api\Beeline::getToten($db);
////    \f\pa($list);
////    \f\end2('23');
//    $list = (array) \Nyos\api\Beeline::getListNumber($db);
//    // \f\pa( $list );
//    // \f\pa((array) $list['CTNInfoList']);
//    $res = (array) $list['CTNInfoList'];
//
////    \f\pa(sizeof( $res ));
//// \f\pa( $res );
//    // if ( sizeof( $res ) == 8 ) {
//
//    \Nyos\mod\Bibi_v1::deletePhonesFromDogovor($db, (int) $_REQUEST['id']);
//
//
//
//    \Nyos\mod\items::$type_module = 3;
//    // \f\pa( (array) $list['CTNInfoList']);
//    $res['dogovor_id'] = $_REQUEST['id'];
//    \Nyos\mod\items::add($db, \Nyos\mod\Bibi_v1::$mod_phones, $res);
//
//    \f\end2('(обработано) номеров в договоре 1', true);
//
//    // }
//
//
//  \f\end2('end', false);
//
////    // \f\pa($_REQUEST);
//////  require_once( $_SERVER['DOCUMENT_ROOT'] . DS . '0.all' . DS . 'f' . DS . 'db.2.php' );
//////  require_once( $_SERVER['DOCUMENT_ROOT'] . DS . '0.all' . DS . 'f' . DS . 'txt.2.php' );
//////  $_SESSION['status1'] = true;
//////  $status = '';
//
////    $e = array('id' => (int) $_POST['item_id']);
//
////    $ff = $db->prepare('DELETE FROM `mitems-dops` WHERE `id_item` = :id_item AND `name` = :name ');
////    $ff->execute(
////            array(
////                ':id_item' => $_POST['item_id'],
////                ':name' => $_POST['dop_name']
////            )
////    );
//
////    if (isset($_POST['new_val'])) {
////        $sql = 'INSERT INTO `mitems-dops` ( `id_item`, `name`, `value` ) values ( :id, :name , :val ) ';
////        // \f\pa($sql);
////        $ff = $db->prepare($sql);
////        $in_sql = [
////            ':id' => $_POST['item_id'],
////            ':name' => $_POST['dop_name'],
////            ':val' => $_POST['new_val'] ?? 0,
////        ];
////        // \f\pa($in_sql);
////        $ff->execute($in_sql);
////    }
////
//////    $dir_for_cash = DR . dir_site;
//////    $list_cash = scandir($dir_for_cash);
//////    foreach ($list_cash as $k => $v) {
//////        if (strpos($v, 'cash.items.') !== false) {
//////            unlink($dir_for_cash . $v);
//////        }
//////    }
////// f\end2( 'новый статус ' . $status);
//  f\end2('ок');
} catch (\Exception $exc) {

    \f\pa($exc, 2);

//    $msg = '';
//
////    $r = ob_get_contents();
////    ob_end_clean();
//
//    \nyos\Msg::sendTelegramm($r, null, 2);
//
//    \f\end2($r, false);
//
//    if (isset($exc->detail->UssWsApiException->errorCode))
//        $msg .= ' ошибка #' . $exc->detail->UssWsApiException->errorCode . ' / ';
//
//    
//    if (isset($exc->detail->UssWsApiException->errorDescription))
//        $msg .= $exc->detail->UssWsApiException->errorDescription;
//
//    // \f\end2($msg . ' ( #' . $exc->getCode() . ' ' . $exc->getMessage() . ' )', false, (array) $exc);
////    echo '<pre>';
////    print_r($exc);
////    echo '</pre>';
//    // echo $exc->getTraceAsString();
}