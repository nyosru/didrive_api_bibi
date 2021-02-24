<?php

//    $date = $in['date'] ?? $_REQUEST['date'] ?? null;
//

if (isset($skip_start) && $skip_start === true) {
    
} else {
    // require_once '0start.php';
    require_once '../../../../didrive/base/start-for-microservice.php';
    $skip_start = false;
}


ob_start('ob_gzhandler');

\f\pa($_REQUEST);

try {

    // \f\Cash::$cancel_memcash = true;

    if (empty($r))
        $r = $_REQUEST;

    $msg = '';

//    if (empty($r['phone']))
//        throw new \Exception('нет телефона');
// получаем токен
    if (1 == 1) {
        /*
          // \Nyos\mod\items::$search['who_added'] = $_SESSION['now_user_di']['id'];
          \Nyos\mod\items::$search['id'] = $r['ban'];
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
         */


        // запуск не чаще 1 раза в минуту
        if (1 == 1 && !isset($_REQUEST['nocahe'])) {

            $v_cash = 'read_dog_' . ( $_REQUEST['phone'] ?? '' ) . ( $_REQUEST['ban'] ?? '' );
            $e = \f\Cash::getVar($v_cash);

            if (!empty($e)) {
//            echo __LINE__;
//            var_dump($e);
                \f\end2('запускали ранее менее 1 минуты назад', false);
            }

            \f\Cash::setVar($v_cash, 123, 119);
        }


        if (!empty($_REQUEST['phone'])) {
            \Nyos\api\Beeline::getTokenFromNumber($db, $_REQUEST['phone']);
        }
        //
        elseif (!empty($_REQUEST['ban'])) {
            \Nyos\api\Beeline::getTokenFromBan($db, $_REQUEST['ban']);
        }

        if (empty(\Nyos\api\Beeline::$login))
            throw new \Exception('111111 не найден договор');


        // \Nyos\api\Beeline::getToken($db);
        // $r['phone'] = substr(1,10,$_REQUEST['phone']);
    }

    //echo '<br/>' . __LINE__ . ' asdf ' . __FILE__;

    $c = new \SoapClient('https://my.beeline.ru/api/SubscriberService?WSDL', array('trace' => false, 'cache_wsdl' => WSDL_CACHE_NONE));

    $opt = [
        'token' => \Nyos\api\Beeline::$token,
        'ban' => \Nyos\api\Beeline::$ban,
        'ctn' => $r['phone'],
        'startDate' => date('Y-m-d\T00:00:00.000', $_SERVER['REQUEST_TIME'] - 3600 * 24 * ( $r['scan_days'] ?? 60 )), // 2013-04-26T00:00:00.000<
        'endDate' => date('Y-m-d\T00:00:00.000', $_SERVER['REQUEST_TIME']), // 2013-04-26T00:00:00.000<
    ];

    //\f\pa($opt);
    // $response = (array) $c->getPaymentList($opt);

    $response = $c->getPaymentList($opt);
    // \f\pa($response);

    $search_pays = [];

    if (isset($response->PaymentList) && sizeof($response->PaymentList) > 0) {

        $kolvo = sizeof($response->PaymentList);
        $res1 = (array) $response->PaymentList;

        if (isset($_REQUEST['print']))
            \f\pa($res1);
        
//        foreach ($response->PaymentList as $pay) {
//            $search_pays[] = $pay->bankPaymentID;
//        }


        \Nyos\mod\items::$type_module = 3;

        if (!empty($search_pays))
            \Nyos\mod\items::$search['bankpaymentid'] = $search_pays;

        // \Nyos\mod\items::$sql_select_vars = ['bankpaymentid','ctn','paymentdate','paymentcurrentamt','CONCAT( ctn , \'-\' , paymentdate , \'-\' , paymentcurrentamt ) as `ss`  '];
        \Nyos\mod\items::$sql_select_vars = ['bankpaymentid', 'CONCAT( ctn , \'-\', bankPaymentid, \'-\' , paymentdate , \'-\' , paymentcurrentamt ) as `ss` '];
        \Nyos\mod\items::$between_date['paymentdate'] = [date('Y-m-d H:i:s', strtotime('-60 day')), date('Y-m-d H:i:s', strtotime('+2 day'))];
        $pay_ids = \Nyos\mod\items::get($db, \Nyos\mod\Bibi_v1::$mod_pays);

        // \f\pa($pay_ids);

        $pays1 = [];

        foreach ($pay_ids as $v1) {
            // \f\pa($v1);
            // $pays1[$v1['bankpaymentid']] = 1;
            // $pays1[ $v1['ctn'].'-'.$v1['paymentdate'].'-'.$v1['paymentcurrentamt'] ] = 1;
            $pays1[$v1['ss']] = 1;
        }

        // \f\pa( $pays1, 2,'','$pays1' );
        // \f\pa( $res1, 2,'','$res1' );

        foreach ($res1 as $pay) {

            // \f\pa($pay);

            $s = $pay->ctn . '-' . date('Y-m-d H:i:s', strtotime($pay->paymentDate)) . '-' . $pay->paymentCurrentAmt;

            if (isset($pays1[$s])) {
                // echo '<br/>'.__LINE__.' пропускаем '.$s;
            } else {
                // echo '<br/>'.__LINE__.' '.$s;
                // echo '<br/>'.$s;
                // \f\pa($pays1);

                if (!empty($_REQUEST['scip_send_telega'])) {
                    
                } else {
                    $msg .= PHP_EOL . $pay->ctn . ' +' . $pay->paymentCurrentAmt;
                }

                $i2 = [
                    'ctn' => $pay->ctn,
                    'paymentdate' => date('Y-m-d H:i:s', strtotime($pay->paymentDate)),
                    'paymentstatus' => ( $pay->paymentStatus ?? '' ),
                    'paymenttype' => $pay->paymentType,
                    'paymentoriginalamt' => $pay->paymentOriginalAmt,
                    'paymentcurrentamt' => $pay->paymentCurrentAmt,
                    'bankpaymentid' => $pay->bankPaymentID,
                    'paymentactivatedate' => date('Y-m-d H:i:s', strtotime($pay->paymentActivateDate)),
                        // 'add_dt' => date('Y-m-d H:i:s')
                ];

                // \f\pa($i2);

                $in[] = $i2;
            }
        }

        // \f\pa($in);

        if (!empty($in)) {
            \Nyos\mod\items::adds($db, \Nyos\mod\Bibi_v1::$mod_pays, $in);

            //
            if (!empty($_REQUEST['scip_send_telega'])) {
                
            }
            //
            else {
                \Nyos\Msg::sendTelegramm('Пополнения балансов' . $msg, null, 2);
            }
        }
    }

    $r2 = ob_get_contents();
    ob_end_clean();

    if (isset($_REQUEST['print']))
        echo $r2;

    $kolvo_n = sizeof($in);

    if (isset($_REQUEST['print'])) {
        echo '<pre>';
        print_r(['ок / загружено ' . $kolvo . ' / добавлено новых ' . $kolvo_n . ' / ' . $r2, true, ['loaded' => $kolvo, 'added' => $kolvo_n, 'datain' => $in]]);
        echo '</pre>';
    }
    //
    else {
        \f\end2('ок / загружено ' . $kolvo . ' / добавлено новых ' . $kolvo_n . ' / ' . $r2, true, ['loaded' => $kolvo, 'added' => $kolvo_n, 'datain' => $in]);
    }
} catch (\Exception $exc) {

    \f\pa($exc, 2);

    $r = ob_get_contents();
    ob_end_clean();


    if (isset($_REQUEST['send']) && $_REQUEST['send'] == 'scip') {
        
    } else {

        \nyos\Msg::sendTelegramm($r, null, 2);
    }

    if (isset($_REQUEST['print']) || isset($_REQUEST['show'])) {
        echo $r;
    } else {
        \f\end2($r, false);
    }
}