<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

if (isset($skip_start) && $skip_start === true) {
    
} else {
    require_once '../../../../didrive/base/start-for-microservice.php';
}

ob_start('ob_gzhandler');

try {

//    $date = $in['date'] ?? $_REQUEST['date'] ?? null;

    if (!empty($_REQUEST['phone']) || !empty($_REQUEST['ban'])) {
        
    } else {
        throw new \Exception('нет телефона или бана');
    }

    echo '<br/>'.__LINE__;

    if (empty(\Nyos\api\Beeline::$token)) {
        if (!empty($_REQUEST['ban'])) {
            \Nyos\api\Beeline::getTokenFromBan($db, $_REQUEST['ban']);
        }
        //
        elseif (!empty($_REQUEST['phone'])) {
            \Nyos\api\Beeline::getTokenFromNumber($db, $_REQUEST['phone']);
        }
    }

    echo '<br/>'.__LINE__;
    
    if (empty(\Nyos\api\Beeline::$token))
        throw new \Exception('нет ключа');

    $c = new \SoapClient('https://my.beeline.ru/api/SubscriberService?WSDL', array('trace' => false, 'cache_wsdl' => WSDL_CACHE_NONE));
    $opt = [
        'token' => \Nyos\api\Beeline::$token,
            // 'actvDate' => date('Y-m-d\TH:i:s.000', $_SERVER['REQUEST_TIME']), // 2013-04-26T00:00:00.000<
            //'actvDate' => date('Y-m-d\T00:00:00.000', $_SERVER['REQUEST_TIME']), // 2013-04-26T00:00:00.000<
            //'reasonCode' => $request['reasonCode'],
    ];

    if (!empty($_REQUEST['ban'])) {
        $opt['ban'] = $_REQUEST['ban'];
    }
    //
    elseif (!empty($_REQUEST['phone'])) {
        $opt['ctn'] = $_REQUEST['phone'];
        $opt['ban'] = \Nyos\api\Beeline::$ban;
    }

    // \f\pa($opt);
    $response = $c->getSIMList($opt);

    //if( !empty($_REQUEST['show']) )
//    \f\pa($response, 2);


    $msg_text = '';

    $search = [];

    // если 1 номер
    if (!empty($response->SIMList->serialNumber)) {

        $v = $response->SIMList;

        //$search['ctn'] = $_REQUEST['phone'];
//        $in = [
//            'ctn' => $_REQUEST['phone'],
//            'sim' => $response->SIMList->serialNumber
//        ];

        $in = [];
        $in[$v->ctn] = [
            'ctn' => $v->ctn,
            'sim' => $v->serialNumber
        ];

        $in2[$v->ctn] = $v->serialNumber;

        $search['ctn'][] = $v->ctn;

//        \Nyos\mod\items::add($db, \Nyos\api\Beeline::$mod_sim_on_phone, $in);
//
//        $kolvo_scan = 1;
        $msg_text = 'sim: ' . $response->SIMList->serialNumber;
    }
    // если много номеров
    elseif (!empty($response->SIMList[0]->serialNumber)) {

        $kolvo_scan = sizeof($response->SIMList);

        //echo '['.__FILE__.' #'.__LINE__.'] ';
        $in = [];
        $in2 = [];
        $del = ['ctn' => []];


        foreach ($response->SIMList as $v) {

            //$del['ctn'][] = $v->ctn;

            $in2[$v->ctn] = $v->serialNumber;

            $search['ctn'][] = $v->ctn;


            $in[$v->ctn] = [
                'ctn' => $v->ctn,
                'sim' => $v->serialNumber
            ];
        }
    }

    // \f\pa($in2, 2, '', 'in2');
    // $in2['99988877661'] = 123123;

    \Nyos\mod\items::$sql_select_vars = ['id', 'ctn', 'sim'];
    \Nyos\mod\items::$search = $search;
    $res2 = \Nyos\mod\items::get($db, \Nyos\api\Beeline::$mod_sim_on_phone);

    if (!empty($_REQUEST['show']))
        \f\pa($res2, 2, '', 'res2');

    $res2_ar = [];

    foreach ($res2 as $k => $v) {
        $res2_ar[$v['ctn']] = $v['sim'];
    }

    //$res2_ar['9998887766'] = 123123456;

    if (!empty($_REQUEST['show']))
        \f\pa($res2_ar, 2, '', '$res2_ar');

    $diff = array_diff_assoc($in2, $res2_ar);

    if (!empty($_REQUEST['show']))
        \f\pa($diff, 2, '', 'diff');

    $in = [];
    $msg = [];

    if (!empty($diff)) {
        \Nyos\mod\items::deleteItemForDops($db, \Nyos\api\Beeline::$mod_sim_on_phone, ['ctn' => array_keys($diff)]);

        $nn = 0;

        foreach ($diff as $nom => $sim) {

            $in[] = [
                'ctn' => $nom,
                'sim' => $sim
            ];

            if (!isset($msg[$n]))
                $msg[$n] = '';

            $msg[$n] .= $nom . ' - ' . $sim . PHP_EOL;

            if ($nn > 100) {
                $nn = 0;
                $n++;
            }

            $nn++;
        }

        if (!empty($_REQUEST['show']))
            \f\pa($in, 2, '', 'add items in sql');

        if (!empty($in)) {

            //\Nyos\mod\items::$show_sql = true;
            $e = \Nyos\mod\items::adds($db, \Nyos\api\Beeline::$mod_sim_on_phone, $in);
            //\f\pa($e);

            foreach ($msg as $m) {
                \Nyos\Msg::sendTelegramm('Новые симки на номерах' . PHP_EOL . $m, null, 2);
            }
        }
    }

//if( !empty($_REQUEST['show']) )
//    \f\pa($del, 2, '', 'del');
    //\Nyos\mod\items::deleteItemForDops($db, \Nyos\api\Beeline::$mod_sim_on_phone, $del);

    $r = ob_get_contents();
    ob_end_clean();

    if (isset($_REQUEST['html'])) {

        die($r);
    } else {

        \f\end2(
                'загружено и проверено: ' . ( $kolvo_scan ?? 1 ) . ' , изменений ' . sizeof($in)
                . (!empty($msg_text) ? '<br/>(' . $msg_text . ')' : '' ),
                true,
                ['scan' => $kolvo_scan, 'edited' => sizeof($in)]
        );
        // \f\end2('что то не так ' . $r, false);
    }
}
//
catch (\Exception $exc) {

    if (isset($_REQUEST['html'])) {

        echo '<pre>';
        print_r($exc);
        echo '<pre>';
        
    } else {

        \f\pa($exc);
    }

    $msg = '';

    $r = ob_get_contents();
    ob_end_clean();

    if (isset($exc->detail->UssWsApiException->errorCode))
        $msg .= ' ошибка #' . $exc->detail->UssWsApiException->errorCode . ' / ';

    if (isset($exc->detail->UssWsApiException->errorDescription))
        $msg .= $exc->detail->UssWsApiException->errorDescription;

    if( !empty($msg) )
    \nyos\Msg::sendTelegramm($msg, null, 2);

    if (isset($_REQUEST['html'])) {
        echo $r;
    } else {
        \f\end2($r, false);
    }

    // \f\end2($msg . ' ( #' . $exc->getCode() . ' ' . $exc->getMessage() . ' )', false, (array) $exc);
//    echo '<pre>';
//    print_r($exc);
//    echo '</pre>';
    // echo $exc->getTraceAsString();
}