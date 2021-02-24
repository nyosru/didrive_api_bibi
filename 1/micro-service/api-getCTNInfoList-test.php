<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

if (isset($skip_start) && $skip_start === true) {
    
} else {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/didrive/base/start-for-microservice.php';
}

ob_start('ob_gzhandler');

try {

//    $date = $in['date'] ?? $_REQUEST['date'] ?? null;

    if (!empty($_REQUEST['phone']) || !empty($_REQUEST['ban'])) {
        
    } else {
        throw new \Exception('нет телефона или бана');
    }

//echo '<br/>'.__LINE__;

    if (empty(\Nyos\api\Beeline::$token)) {
        if (!empty($_REQUEST['ban'])) {
            \Nyos\api\Beeline::getTokenFromBan($db, $_REQUEST['ban']);
        }
//
        elseif (!empty($_REQUEST['phone'])) {
            \Nyos\api\Beeline::getTokenFromNumber($db, $_REQUEST['phone']);
        }
    }

//    \f\pa(
//            [
//        'token' => \Nyos\api\Beeline::$token,
//        'ban' => \Nyos\api\Beeline::$ban,
//        'ctn' => ( $_REQUEST['phone'] ?? '' ),
//            ]);
    
    $c = new \SoapClient('https://my.beeline.ru/api/SubscriberService?WSDL', array('trace' => false, 'cache_wsdl' => WSDL_CACHE_NONE));
    $opt = [
        'token' => \Nyos\api\Beeline::$token,
        'ban' => \Nyos\api\Beeline::$ban,
        'ctn' => ( $_REQUEST['phone'] ?? '' ),
            // 'actvDate' => date('Y-m-d\TH:i:s.000', $_SERVER['REQUEST_TIME']), // 2013-04-26T00:00:00.000<
//        'actvDate' => date('Y-m-d\T00:00:00.000', $_SERVER['REQUEST_TIME']), // 2013-04-26T00:00:00.000<
//        'reasonCode' => $request['reasonCode'],
    ];

    // \f\pa($opt);
    $response = $c->getCTNInfoList($opt);
    // \f\pa($response, 3, '', '$response');
    
// если 1 номер
    if (!empty($_REQUEST['phone'])) {

        $in = \Nyos\api\Beeline::normArrayPhone($response->CTNInfoList);

        \f\pa( [ ( \Nyos\api\Beeline::$token ?? 'xx' ) , $in ] );
        
        //\Nyos\mod\items::$show_status = true;
        $rr = \Nyos\mod\items::addDiffItem($db, \Nyos\api\Beeline::$mod_phone_info, ['ctn' => $in['ctn']], $in);
    }

// если много номеров    
    else {

// echo '<Br/>#'.__LINE__;

        $new = $ctns = [];

        foreach ($response->CTNInfoList as $k => $v) {

// \f\pa( $v );
            $v1 = \Nyos\api\Beeline::normArrayPhone($v);

            if (!empty($v1['ctn'])) {
                $ctns[] = $v1['ctn'];

                $new[$v1['ctn']] = $v1;
            }
        }

        // \f\pa($ctns, 3, '', '$ctns список номеров');
        // \Nyos\mod\items::$show_sql = true;
        \Nyos\mod\items::$search['ctn'] = $ctns;
        $list = \Nyos\mod\items::get($db, \Nyos\api\Beeline::$mod_phone_info);

        // \f\pa($list, 3, '', '$list получили инфу по номерам в базе сейчас ');

        /**
         * шлём в телегу инфу
         */
        $sms = [];
        /**
         * добавляем новые номера
         */
        $new_add = [];
        /**
         * 
         */
        $delete_ctn = $edit_items_id = [];

        foreach ($new as $ctn => $v) {

            $now_phone = '';

            foreach ($list as $vv) {
                if ($vv['ctn'] == $ctn) {
                    $now_phone = $ctn;
                    break;
                }
            }

            if (empty($now_phone)) {

                // echo '<br/>новый номер ' . $ctn;
                $new_add[] = $v;
                // \f\pa($v);

                continue;
            }


            $now_phone = [];

            foreach ($list as $k1 => $v1) {

                if (isset($v1['ctn']) && $v1['ctn'] == $ctn) {

                    $now_phone = $v1;
// \f\pa($v1);
                    break;
                }
            }

            $diff = false;

// есть текущий телефон в базе
            if (!empty($now_phone)) {

                $diff = \f\diff_array($v, $now_phone);
//                echo '<Br/>'.var_dump([ $diff , 'ctn' => $v['ctn'] ] );

                if ($diff !== true) {

                    if (!empty($_REQUEST['print'])) {
                        echo '<table><tr><td>';
                        \f\pa($v);
                        echo '</td>'
                        . '<td>';
                        \f\pa($now_phone);
                        echo '</td>'
                        . '<td>';
                        \f\pa($diff);
                        echo '</td>'
                        . '</tr></table>';
                    }

                    if (!empty($now_phone['id'])) {
                        $delete_ctn[] = (int) $now_phone['ctn'];
                        $new_add[] = $v;

                        $sms2 = $now_phone['ctn'] . ' обновление инфы';
                        foreach ($diff as $u1 => $i1) {
                            $sms2 .= PHP_EOL . $u1 . ' = ' . $i1;
                        }

                        $sms[] = $sms2;
                    }
                }
            }
// нет текущего телефона в базе
            else {
                
            }
        }

        if (!empty($delete_ctn)) {
            // \Nyos\mod\items::$show_sql = true;
            // $r = \Nyos\mod\items::deleteFromDops($db, \Nyos\api\Beeline::$mod_phone_info, $edit_items_id);
            // \Nyos\mod\items::deleteItems2($db, \Nyos\api\Beeline::$mod_phone_info, $edit_items_id);
            // \Nyos\mod\items::deleteItems(); // eleteItems2($db, \Nyos\api\Beeline::$mod_phone_info, $edit_items_id);
            // \f\pa($r,2,'','результат удалений');

            $in_sql = '';
            $ctn_edited = [];
            $n = 1;

            foreach ($delete_ctn as $i2) {
                $in_sql .= (!empty($in_sql) ? ' OR ' : '' ) . ' `ctn`=:id' . $n . ' ';
                $ctn_edited[':id' . $n] = $i2;
                $n++;
                
                echo '<br/>изменился - '.$i2;
                
            }

            $sql = 'UPDATE `' . \f\db_table(\Nyos\api\Beeline::$mod_phone_info) . '` SET `status` = \'delete\' WHERE ' . $in_sql . ' ;';
            // \f\pa($sql);

            $ff = $db->prepare($sql);
            $ff->execute($ctn_edited);
        }

// \Nyos\mod\items::$show_status = true;
//$rr = \Nyos\mod\items::diff($db, \Nyos\api\Beeline::$mod_phone_info, ['ctn' => $in['ctn']], $in);

        if (!empty($new_add)) {

            // \Nyos\mod\items::$show_sql = true;
            $ee = \Nyos\mod\items::adds($db, \Nyos\api\Beeline::$mod_phone_info, $new_add );
// \f\pa($ee);
            $return['added'] = $ee['data']['kolvo'] ?? 0;
        }


        if (!empty($sms)) {

            $nn = 0;
            $sms001 = (!empty($_REQUEST['ban']) ? 'договор ' . $_REQUEST['ban'] . PHP_EOL : '' );
            $sms00 = $sms001;

            foreach ($sms as $m) {

                $nn++;
                $sms00 .= $m . PHP_EOL;

                if ($nn == 5) {
                    \nyos\Msg::sendTelegramm($sms00, null, 2);
                    $nn = 0;
                    $sms00 = $sms001;
                }
                
            }

            if ($nn != 0) {
                \nyos\Msg::sendTelegramm($sms00, null, 2);
                $nn = 0;
            }
        }
    }

    //\f\pa($rr, 2, '', 'diff result = $rr');
//    if( isset( $response->return ) )
//        \Nyos\mod\items:: if( isset( $response->return ) );
//    
//        $c = new \SoapClient('https://my.beeline.ru/api/SubscriberService?WSDL', array('trace' => false, 'cache_wsdl' => WSDL_CACHE_NONE));
//        $response = $c->auth(['login' => self::$login, 'password' => self::$pass ]);
// \f\pa($response);
// \f\pa($c->__getLastRequestHeaders());
// \f\pa($c->__getLastRequest());
// if (!empty($response->return)) {

    // \f\pa($_SESSION);
    
    if (!isset($request['skip_ob'])) {
        $r = ob_get_contents();
        ob_end_clean();
    }

    if (!empty($request['return']) && $request['return'] == 'json') {
        return json_encode(['request' => $response->return ?? '', 'other' => $r]);
    } else {
        \f\end2($r. ( !empty($ctn_edited) ? '<br/>изменено: '.sizeof($ctn_edited).' тел' : '' ), true, ( $return ?? []));
    }
} catch (\Exception $exc) {

    if (!empty($request['return']) && $request['return'] == 'json') {

        return json_encode(['error' => $exc->detail->UssWsApiException->errorDescription, 'other' => $r]);

    } else {

        //\f\pa($exc);

        $msg = '';

        $r = ob_get_contents();
        ob_end_clean();

        // \nyos\Msg::sendTelegramm($r, null, 2);

        \f\end2($r, false);

        if (isset($exc->detail->UssWsApiException->errorCode))
            $msg .= ' ошибка #' . $exc->detail->UssWsApiException->errorCode . ' / ';

        if (isset($exc->detail->UssWsApiException->errorDescription))
            $msg .= $exc->detail->UssWsApiException->errorDescription;

        \nyos\Msg::sendTelegramm($exc->getMessage() . ' #' . $exc->getLine(), null, 2);

        // \f\end2($msg . ' ( #' . $exc->getCode() . ' ' . $exc->getMessage() . ' )', false, (array) $exc);
        //    echo '<pre>';
        //    print_r($exc);
        //    echo '</pre>';
        // echo $exc->getTraceAsString();
    }
}
