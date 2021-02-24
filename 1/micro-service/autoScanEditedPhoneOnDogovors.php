<?php

//    $date = $in['date'] ?? $_REQUEST['date'] ?? null;
//

if (isset($skip_start) && $skip_start === true) {
    
} else {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/didrive/base/start-for-microservice.php';
    $skip_start = false;
}

ob_start('ob_gzhandler');

\f\timer_start(7);

/**
 * количество секунд на работу скриптов, не больше 60 сек
 */
$kolvo_sec_on_job = 20;

try {

    \Nyos\mod\items::$sql_select_vars = ['login'];
    $list = \Nyos\mod\items::get($db, \Nyos\api\Beeline::$mod_dogovors);

    // foreach()
    //

    \f\pa($list, 2);

    foreach ($list as $i) {

        $tt = \f\timer_stop(7, 'ar');
        
        echo '<br/>'.$i['login'];
        
        if (empty($i['login']))
            continue;

        echo '<br/>'.__LINE__ .' '.$tt['sec'];
        
        $cash_var = 'auto_scan_dog' . $i['login'];

        $cash = \f\Cash::getVar($cash_var);

        if (!empty($cash))
            continue;
        

        // echo '<br/>'.__LINE__ .' '.$tt['sec'];

        if ($tt['sec'] > ( $kolvo_sec_on_job ?? 10 ) )
            break;

        \f\Cash::setVar($cash_var, 'da', 60 * 60);

        $uri = 'https://' . $_SERVER['HTTP_HOST'] . '/vendor/didrive_api/bibi/1/micro-service/api-getCTNInfoList.php?ban=' . $i['login'] . '&s=' . \Nyos\Nyos::creatSecret($i['login']);
        $res = \f\get_curl_https_uri($uri);
        // \f\pa($res);
        
    }

    $r2 = ob_get_contents();
    ob_end_clean();

    if (isset($_REQUEST['print']))
        echo $r2;

    \f\end2('ок');
    
} catch (\Exception $exc) {

    \f\pa(
            [
                $exc->getFile(),
                $exc->getLine(),
                $exc->getMessage()
            ]
    );

    $r = ob_get_contents();
    ob_end_clean();

    if (isset($_REQUEST['print']))
        echo $r;

    if (isset($_REQUEST['send']) && $_REQUEST['send'] == 'scip') {
        
    } else {

        \nyos\Msg::sendTelegramm($r, null, 2);
    }
    \f\end2($r, false);
}
\f\end2('закончили скан');

//
//ob_start('ob_gzhandler');
//
//try {
//
//    // \f\Cash::$cancel_memcash = true;
//
//    if (empty($r))
//        $r = $_REQUEST;
//
//    $msg = '';
//
////    if (empty($r['phone']))
////        throw new \Exception('нет телефона');
//// получаем токен
//    if (1 == 1) {
//        // \Nyos\mod\items::$search['who_added'] = $_SESSION['now_user_di']['id'];
//        \Nyos\mod\items::$search['id'] = $r['ban'];
//        \Nyos\mod\items::$type_module = 3;
//        $dogs = \Nyos\mod\items::get($db, '701.beeline_dogovors');
//
//        // \f\end2('asd',true,$dogs);
//
//        if (!empty($dogs)) {
//            foreach ($dogs as $v) {
//
//                if (empty($v['login']) || empty($v['pass']))
//                    throw new \Exception('что то пошло не так #' . __LINE__ . ', повторите и обратитесь в тех. поддержку', false);
//
//                \Nyos\api\Beeline::$login = trim('A' . $v['login']);
//                \Nyos\api\Beeline::$ban = trim($v['login']);
//                \Nyos\api\Beeline::$pass = trim($v['pass']);
//
//                break;
//            }
//        }
//
//        // запуск не чаще 1 раза в минуту
//        if (1 == 1) {
//            $v_cash = 'read_dog_' . $v['login'];
//            $e = \f\Cash::getVar($v_cash);
//
//            if (!empty($e)) {
////            echo __LINE__;
////            var_dump($e);
//                \f\end2('запускали ранее менее 1 минуты назад', false);
//            }
//
//            \f\Cash::setVar($v_cash, 123, 60);
//        }
//
//        \Nyos\api\Beeline::getToken($db);
//
//        // $r['phone'] = substr(1,10,$_REQUEST['phone']);
//    }
//
//    $c = new \SoapClient('https://my.beeline.ru/api/SubscriberService?WSDL', array('trace' => false, 'cache_wsdl' => WSDL_CACHE_NONE));
//
//    $opt = [
//        'token' => \Nyos\api\Beeline::$token,
//        'ban' => \Nyos\api\Beeline::$ban,
//        'ctn' => $r['phone'],
//        'startDate' => date('Y-m-d\T00:00:00.000', $_SERVER['REQUEST_TIME'] - 3600 * 24 * ( $r['scan_days'] ?? 60 )), // 2013-04-26T00:00:00.000<
//        'endDate' => date('Y-m-d\T00:00:00.000', $_SERVER['REQUEST_TIME']), // 2013-04-26T00:00:00.000<
//    ];
//
//    //\f\pa($opt);
//    // $response = (array) $c->getPaymentList($opt);
//
//    $response = $c->getPaymentList($opt);
//    // \f\pa($response);
//
//    $search_pays = [];
//
//    if (isset($response->PaymentList) && sizeof($response->PaymentList) > 0) {
//
//        $kolvo = sizeof($response->PaymentList);
//        $res1 = (array) $response->PaymentList;
//
////        foreach ($response->PaymentList as $pay) {
////            $search_pays[] = $pay->bankPaymentID;
////        }
//
//
//        \Nyos\mod\items::$type_module = 3;
//
//        if (!empty($search_pays))
//            \Nyos\mod\items::$search['bankpaymentid'] = $search_pays;
//
//        // \Nyos\mod\items::$sql_select_vars = ['bankpaymentid','ctn','paymentdate','paymentcurrentamt','CONCAT( ctn , \'-\' , paymentdate , \'-\' , paymentcurrentamt ) as `ss`  '];
//        \Nyos\mod\items::$sql_select_vars = ['bankpaymentid', 'CONCAT( ctn , \'-\' , bankpaymentid , \'-\', paymentdate , \'-\' , paymentcurrentamt ) as `ss` '];
//        \Nyos\mod\items::$between_date['paymentdate'] = [date('Y-m-d H:i:s', strtotime('-60 day')), date('Y-m-d H:i:s', strtotime('+2 day'))];
//        $pay_ids = \Nyos\mod\items::get($db, \Nyos\mod\Bibi_v1::$mod_pays);
//
//        // \f\pa($pay_ids);
//
//        $pays1 = [];
//
//        foreach ($pay_ids as $v1) {
//            // \f\pa($v1);
//            // $pays1[$v1['bankpaymentid']] = 1;
//            // $pays1[ $v1['ctn'].'-'.$v1['paymentdate'].'-'.$v1['paymentcurrentamt'] ] = 1;
//            $pays1[$v1['ss']] = 1;
//        }
//
//        // \f\pa( $pays1, 2,'','$pays1' );
//        // \f\pa( $res1, 2,'','$res1' );
//
//        foreach ($res1 as $pay) {
//
//            // \f\pa($pay);
//
//            $s = $pay->ctn . '-' . $pay->bankPaymentID .'-'. date('Y-m-d H:i:s', strtotime($pay->paymentDate)) . '-' . $pay->paymentCurrentAmt;
//
//            if (isset($pays1[$s])) {
//                // echo '<br/>'.__LINE__.' пропускаем '.$s;
//            } else {
//                // echo '<br/>'.__LINE__.' '.$s;
//                // echo '<br/>'.$s;
//                // \f\pa($pays1);
//
//                if( !empty( $_REQUEST['scip_send_telega'] )){}else{
//                $msg .= PHP_EOL . $pay->ctn . ' +' . $pay->paymentCurrentAmt;
//                }
//
//                $i2 = [
//                    'ctn' => $pay->ctn,
//                    'paymentdate' => date('Y-m-d H:i:s', strtotime($pay->paymentDate)),
//                    'paymentstatus' => ( $pay->paymentStatus ?? '' ),
//                    'paymenttype' => $pay->paymentType,
//                    'paymentoriginalamt' => $pay->paymentOriginalAmt,
//                    'paymentcurrentamt' => $pay->paymentCurrentAmt,
//                    'bankpaymentid' => $pay->bankPaymentID,
//                    'paymentactivatedate' => date('Y-m-d H:i:s', strtotime($pay->paymentActivateDate)),
//                        // 'add_dt' => date('Y-m-d H:i:s')
//                ];
//
//                // \f\pa($i2);
//
//                $in[] = $i2;
//            }
//        }
//
//        // \f\pa($in);
//
//        if (!empty($in)) {
//            \Nyos\mod\items::adds($db, \Nyos\mod\Bibi_v1::$mod_pays, $in);
//            
//            //
//            if( !empty( $_REQUEST['scip_send_telega'] )){}
//            //
//            else{
//            \Nyos\Msg::sendTelegramm('Пополнения балансов' . $msg, null, 2);
//            }
//            
//        }
//    }
//
//    $r2 = ob_get_contents();
//    ob_end_clean();
//
//    if (isset($_REQUEST['print']))
//        echo $r2;
//
//    $kolvo_n = sizeof($in);
//
//    \f\end2('ок / загружено ' . $kolvo . ' / добавлено новых ' . $kolvo_n . ' / ' . $r2, true, ['loaded' => $kolvo, 'added' => $kolvo_n, 'datain' => $in]);
//} catch (\Exception $exc) {
//    \f\pa($exc, 2);
//
//    $r = ob_get_contents();
//    ob_end_clean();
//
//    if (isset($_REQUEST['print']))
//        echo $r;
//
//    if (isset($_REQUEST['send']) && $_REQUEST['send'] == 'scip') {
//        
//    } else {
//
//        \nyos\Msg::sendTelegramm($r, null, 2);
//    }
//    \f\end2($r, false);
//}