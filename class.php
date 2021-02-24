<?php

/**
  класс модуля
 * */

namespace Nyos\api;

//if (!defined('IN_NYOS_PROJECT'))
//    throw new \Exception('Сработала защита от розовых хакеров, обратитесь к администрратору');

class Beeline {

    public static $token = [];
    public static $login = '';
    public static $pass = '';
    public static $ban = '';

    /**
     * номер договора в моей бд
     * @var цифра
     */
    public static $dog_id = '';

    /**
     * модуль договора
     * @var type 
     */
    public static $mod_dogovors = '701.beeline_dogovors';

    /**
     * модуль доступы договоров ( для супер админа и всех остальных )
     * @var type 
     */
    public static $mod_dogovors_access = '701.beeline_dogovors_access';

    /**
     * модуль телефоны
     * @var type 
     */
    public static $mod_phones = '701.beeline_phones';

    /**
     * модуль телефон - статус занятости 
     * рега возможна / бронь / рега не возможна / тел уже зареген
     * @var type 
     */
    public static $mod_phones_reg_status = 'phone_status';

    /**
     * модуль запросы
     * @var type 
     */
    public static $mod_request = '701.beeline_requests';

    /**
     * модуль платежи
     * @var type 
     */
    public static $mod_pays = '702.pays';

    /**
     * симки на телефоне
     * @var type 
     */
    public static $mod_sim_on_phone = 'sim_on_phone';

    /**
     * инфа телефона
     * @var type 
     */
    public static $mod_phone_info = 'phone_info';

    function __construct() {
        
    }

    /**
     * нормализовать массив с инфой о телефоне
     * @param type $ar
     */
    static public function normArrayPhone($ar) {

        $in = [];

        foreach ($ar as $k => $v) {
            $kk = \f\translit($k, 'uri2');

            if ($kk == 'status') {
                $kk = 'phone_status';
            } elseif ($kk == 'statusdate') {
                $v = date('Y-m-d H:i:s', strtotime($v));
            } elseif ($kk == 'activationdate') {
                $v = date('Y-m-d H:i:s', strtotime($v));
            } elseif ($kk == 'ctn') {
                $v = substr($v, 1, 10);
            }

            $in[$kk] = $v;
        }
        
        return $in;
    }

    static public function getTokenFromNumber($db, $number) {

        $sql = 'SELECT '
//                . ' t.ctn '
//                . ' , '
//                . ' t.phone_status '
//                . ' , '
//            .' t.status '
//             .' , '
                . ' d.id dog_id '
                . ', '
                . ' d.login '
                . ', '
                . ' d.pass '
//                . ', '
//                . ' d.head dog, '
//                . ' a.access_block, '
//                . ' a.access_unblock, '
//                . ' SUM( p.paymentcurrentamt ) summa '
                . ' FROM mod_' . \f\translit('701.beeline_phones', 'uri2') . ' t '
                . ' LEFT JOIN mod_' . \f\translit('701.beeline_dogovors', 'uri2') . ' d ON d.id = t.dogovor_id AND d.status = :status '

//            . ' LEFT JOIN mod_' . \f\translit('701.beeline_dogovors_access', 'uri2') . ' a ON d.id = a.dogovor_id AND a.status = :status '
//            . ' LEFT JOIN mod_' . \f\translit('702.pays', 'uri2') . ' p ON t.ctn = CONCAT( 7, p.ctn ) AND p.status = :status AND p.paymentdate >= :date_start_pays '
                . ' WHERE '
                . ' t.ctn = :phone '
                . ' AND t.status = :status '
                . ' GROUP BY t.ctn '
                . ' ORDER BY t.ctn DESC'
        ;

        // \f\pa($sql);
        $ff = $db->prepare($sql);

        $ww = [
            ':phone' => $number,
            ':status' => 'show'
        ];
        // \f\pa($ww);
        $ff->execute($ww);
        $v = $ff->fetch();

        if (empty($v['login']) || empty($v['pass'])) {
            throw new \Exception('не найден договор');
            \f\end2('что то пошло не так, обновите страницу, повторите и если не получится обратитесь к администратору', false, $_REQUEST);
        }

        self::$dog_id = $v['dog_id'];

        self::$login = trim('A' . $v['login']);
        self::$ban = trim($v['login']);
        self::$pass = trim($v['pass']);

        self::getToken($db, self::$login, self::$pass);
    }

    static public function getTokenFromBan($db, $ban) {

        $sql = 'SELECT '
//                . ' t.ctn '
//                . ' , '
//                . ' t.phone_status '
//                . ' , '
//            .' t.status '
//             .' , '
                . ' d.id dog_id '
                . ', '
                . ' d.login '
                . ', '
                . ' d.pass '
//                . ', '
//                . ' d.head dog, '
//                . ' a.access_block, '
//                . ' a.access_unblock, '
//                . ' SUM( p.paymentcurrentamt ) summa '
                . ' FROM mod_' . \f\translit(self::$mod_dogovors, 'uri2') . ' d '
                . ' WHERE ( d.login = :login OR d.id = :login )
                    AND d.status = :status '

//            . ' LEFT JOIN mod_' . \f\translit('701.beeline_dogovors_access', 'uri2') . ' a ON d.id = a.dogovor_id AND a.status = :status '
//            . ' LEFT JOIN mod_' . \f\translit('702.pays', 'uri2') . ' p ON t.ctn = CONCAT( 7, p.ctn ) AND p.status = :status AND p.paymentdate >= :date_start_pays '
//                . ' WHERE '
//                . ' t.ctn = :phone '
//                . ' AND t.status = :status '
//                . ' GROUP BY t.ctn '
//                . ' ORDER BY t.ctn DESC'
                . ' LIMIT 1 '
        ;

        // \f\pa($sql);
        $ff = $db->prepare($sql);

        $ww = [
            ':login' => $ban,
            ':status' => 'show'
        ];
        // \f\pa($ww);
        $ff->execute($ww);
        $v = $ff->fetch();

        if (empty($v['login']) || empty($v['pass'])) {
            \f\end2('что то пошло не так, обновите страницу, повторите и если не получится обратитесь к администратору', false, $_REQUEST);
        }

        self::$dog_id = $v['dog_id'];

        self::$login = trim('A' . $v['login']);
        self::$ban = trim($v['login']);
        self::$pass = trim($v['pass']);

        self::getToken($db, self::$login, self::$pass);
    }

    static public function getToken($db, $login = '', $pass = '') {

        // $cash_var = 'bee_token';

        if (!empty($cash_var)) {

            $vv = \f\Cash::getVar($cash_var);

            if (!empty($vv)) {
                self::$token = $vv;
                // echo '<br/>#'.__LINE__;
                // return $vv;
                return \f\end3('ok', true, ['cash' => true, 'token' => $vv]);
            }
        }

        if (empty(self::$login))
            throw new \Exception('нет логина');

        $c = new \SoapClient('https://my.beeline.ru/api/AuthService?WSDL', array('trace' => false, 'cache_wsdl' => WSDL_CACHE_NONE));
        $response = $c->auth(['login' => self::$login, 'password' => self::$pass]);
        // \f\pa($response);
        // \f\pa($c->__getLastRequestHeaders());
        // \f\pa($c->__getLastRequest());
        if (!empty($response->return)) {

            if (!empty($cash_var))
                \f\Cash::setVar($cash_var, $response->return, 6200);

            self::$token = $response->return;

            return \f\end3('ok', true, ['cash' => false, 'token' => $response->return]);
        } else {
            return false;
        }
    }

    static public function getListNumber($db) {

        self::getToken($db);

        if (empty(self::$token))
            if (empty(self::getToken($db))) {
                throw new \Exception('нет токена');
            }

        // \f\pa(self::$token);
        // \f\pa(self::$token);

        $c = new \SoapClient('https://my.beeline.ru/api/SubscriberService?WSDL', array('trace' => false, 'cache_wsdl' => WSDL_CACHE_NONE));
        $response = $c->getCTNInfoList(['token' => self::$token, 'ban' => self::$ban]);

        // \f\pa($response);
        // \f\pa($c->__getLastRequestHeaders());
        // \f\pa($c->__getLastRequest());
        // https://my.beeline.ru/api/SubscriberService?WSDL

        return $response;
    }

}
