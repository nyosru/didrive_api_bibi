<?php

//    $date = $in['date'] ?? $_REQUEST['date'] ?? null;
//

if (isset($skip_start) && $skip_start === true) {
    
} else {
    require_once '../../../../didrive/base/start-for-microservice.php';
    $skip_start = false;
}

ob_start('ob_gzhandler');

try {

    // \f\Cash::$cancel_memcash = true;

    if (empty($r))
        $r = $_REQUEST;

    $msg = '';

    $sql_price = '';

    if (!empty($_REQUEST['price'])) {

        foreach ($_REQUEST['price'] as $pri) {
            if ($pri == '0' or $pri == 'NULL') {
                $sql_price .= ( !empty($sql_price) ? ' OR ' : '' ).' t.price IS NULL ';
            } 
            //
            else {
                $sql_price .= ( !empty($sql_price) ? ' OR ' : '' ).' t.price = ' . (int) $pri . ' ';
            }
        }
    }

    // ' AND ( t.price = `'. implode('` OR t.price = `', $_REQUEST['price'] ).'` ) ' : '' )


    $sql = 'SELECT '

//            . ' t.* '
//            . ' , '
            . ' t.ctn number '
            . ' , '
            . ' t.price '
//            . ' , '
//            . ' t.ctn '
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
            . ' FROM mod_' . \f\translit('701.beeline_phones', 'uri2') . ' t '
            . ' INNER JOIN mod_' . \f\translit('phone_status', 'uri2') . ' s ON s.ctn = t.ctn AND s.status = :status AND s.reg_status = \'reg_on\' '
//            . ' LEFT JOIN mod_' . \f\translit('701.beeline_dogovors_access', 'uri2') . ' a ON d.id = a.dogovor_id AND a.status = :status '
//            . ' LEFT JOIN mod_' . \f\translit('702.pays', 'uri2') . ' p ON t.ctn = CONCAT( 7, p.ctn ) AND p.status = :status AND p.paymentdate >= :date_start_pays '
            . ' WHERE '
            . ' t.ctn LIKE :phone '

            // . ( !empty($_REQUEST['price']) ? ' t.price IN :price AND ' : '' )
            // . ( !empty($_REQUEST['price']) ? ' AND ( t.price = `'. implode('` OR t.price = `', $_REQUEST['price'] ).'` ) ' : '' )
            . ( !empty($sql_price) ? ' AND ('.$sql_price.') ' : '' )
        
            . ' AND '
            . ' t.status = :status '
//            . ' GROUP BY t.ctn '
            . ' ORDER BY t.ctn ASC'
    ;

    \f\pa($sql);
    $ff = $db->prepare($sql);

    $ww = [
        ':phone' => '%' . ( $_REQUEST['phone'] ?? '9' ) . '%',
//        ':date_start_pays' => date('Y-m-d 00:00:00', strtotime(' -2 month')),
        ':status' => 'show'
    ];

//    if( !empty($_REQUEST['price']) ){
//        $ww[':price'] = '('. implode(' OR ', $_REQUEST['price'] ).')';
//    }

    \f\pa($ww);
    $ff->execute($ww);
    // $vv['tels'] = $ff->fetchAll();

    $r = ob_get_contents();
    ob_end_clean();

    \f\end2('ok ' . $r, true, ['data' => $ff->fetchAll()]);
} catch (\Exception $exc) {
    \f\pa($exc, 2);

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