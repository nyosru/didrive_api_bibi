<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


//    $date = $in['date'] ?? $_REQUEST['date'] ?? null;
//

if (isset($skip_start) && $skip_start === true) {
    
} else {
    // require_once '0start.php';
    require_once '../../../../didrive/base/start-for-microservice.php';
    $skip_start = true;
}

//ob_start('ob_gzhandler');

try {

//    \Nyos\mod\items::$type_module = 3;
//    $dogs = \Nyos\mod\items::get($db, '701.beeline_dogovors');


    $sql = 'SELECT d.*,t.ctn FROM mod_' . \f\translit('701.beeline_dogovors', 'uri2') . ' d 
            LEFT JOIN mod_' . \f\translit('701.beeline_phones', 'uri2') . ' t ON t.dogovor_id = d.id AND t.ctn is not null '
            . ' GROUP BY d.id '
            . ';';
    $ff = $db->prepare($sql);
    $ff->execute();
    $dogs = $ff->fetchAll();

    // \f\pa($dogs);

    \f\timer_start(22);

    $return = [
        'loaded' => 0,
        'added' => 0
    ];

    foreach ($dogs as $dog) {
        sleep(1);
        $time = \f\timer_stop(22, 'ar');
        // \f\pa(\f\timer_stop(22, 'ar'));

        try {

//            \Nyos\mod\items::$type_module = 3;
//            $dogs = \Nyos\mod\items::get($db, '701.beeline_dogovors');

            $r = [];
            $r['ban'] = $dog['id'];
            $r['phone'] = $dog['ctn'];
            $r['send'] = 'scip';
            $r['scip_send_telega'] = '123';

            $uri = 'http://' . $_SERVER['HTTP_HOST'] . '/vendor/didrive_api/bibi/1/micro-service/getPaymentList.php?' . http_build_query($r);
            // \f\pa($uri);

            $res = file_get_contents($uri);
            $re = json_decode($res, true);
            // \f\pa($re);

            if (!empty($re['loaded']) && is_numeric($re['loaded']) && $re['loaded'] > 0)
                $return['loaded'] += $re['loaded'];

            if (!empty($re['added']) && is_numeric($re['added']) && $re['added'] > 0)
                $return['added'] += $re['added'];

            if (!empty($re['datain'])) {
//                $return['datain'][] = [
//                    'dog' => $dog['id'],
//                    'add' => $re['datain']
//                ];

                foreach ($re['datain'] as $nom) {
                    $nom['dog'] = $dog['id'];
                    $return['datain'][] = $nom;
                }
            }
        } catch (\Exception $ex) {
            // \f\pa($ex);
        } catch (\ErrorException $ex) {
            // \f\pa($ex);
        }

        if ($time['sec'] > 20)
            break;
    }


    $r2 = ob_get_contents();
    ob_end_clean();

    if (!empty($_REQUEST['print']))
        \f\pa([$return, $r2]);

//    \f\pa([
//        'loaded' => $return['loaded'], 
//        'added' => $return['added'],
//        'datain' => $return['datain']
//        ]);
//        
    // шлём оповещение в телегу
    if (!empty($return['datain'])) {

        //$nn = 0;
        $msg = '';
            
        foreach ($return['datain'] as $d) {
            if (!empty($d['ctn']) && !empty($d['paymentcurrentamt'])) {

                $msg .= PHP_EOL . $d['ctn'] . ' +' . $d['paymentcurrentamt'];
//                echo '<br/>' . PHP_EOL . $d['ctn'] . ' +' . $d['paymentcurrentamt'];
//
//                if ($nn > 20) {
//
//                    echo '<br/>' . __LINE__;
//                    \Nyos\Msg::sendTelegramm('Произведены пополнения балансов' . $msg, null, 2);
//                    //$msg = '';
//                    $nn = 0;
//
//                } else {
//
//                    $nn++;
//
//                }
            }
        }

        if (!empty($msg)) {
            \Nyos\Msg::sendTelegramm('Балансы пополнены' . $msg, null, 2);
        }
        
    }

    // \f\end2('ок' . $r2, true, [
    \f\end2('ок', true, [
        'loaded' => ( $return['loaded'] ?? 0 ),
        'added' => ( $return['added'] ?? 0 ),
        'datain' => ( $return['datain'] ?? [] )
    ]);
} catch (\PDOException $exc) {

    \f\pa($exc, 2);
    $r = ob_get_contents();
    ob_end_clean();

    echo $r;
} catch (\Exception $exc) {

    \f\pa($exc, 2);

    $r = ob_get_contents();
    ob_end_clean();

    echo $r;

    \nyos\Msg::sendTelegramm($exc->getMessage(), null, 2);
    \f\end2($r, false);
}