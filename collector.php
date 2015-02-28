<?php
class collector {
    private static $VID = 'vid';
    private static $VERSION = 'bate_01';
    private static $COOKIE_PATH = "/";
    private static $COOKIE_USER_PERSISTENCE = 3600 * 24 * 30 * 12;
    private static $SEARCH_ENGINE_LIST = array(
        array("1", "baidu.com", "word|wd")
    );

    public static function sendImg($fid = false, $channel = false) {
        header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
        $keyword = '';
        $rurl = '';
        $times = time();
        $domain = 'http://blog.zhukejin.com';
        $documentReferer = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : '';
        $documentPath = isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : '';

        if ($documentReferer) {
            $parsedReferer = parse_url($documentReferer);
            $sel = self::$SEARCH_ENGINE_LIST;
            $rurl = urlencode($parsedReferer["host"]);
            if (!preg_match('/zhukejin.com/', $parsedReferer["host"])) {
                for ($i = 0, $l = count($sel); $i < $l; $i++) {
                    if (preg_match("/" . $sel[$i][1] . "/", $parsedReferer["host"])) {
                        $keyword = self::getQ($documentReferer, $sel[$i][2]);
                        if (!is_null($keyword)) {
                            $rurl = $sel[$i][1];
                            break;
                        }
                    }
                }
            }
        }
        $userAgent = isset($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : '';

        if (isset($_COOKIE[self::$VID]))
            $cookie = $_COOKIE[self::$VID];
        else if (isset($_COOKIE[self::$COOKIE_NAME]))
            $cookie = $_COOKIE[self::$COOKIE_NAME];
        else
            $cookie = null;
        $inu = $cookie ? 0 : 1;

        $guid = isset($_SERVER["HTTP_X_DCMGUID"]) ? $_SERVER["HTTP_X_DCMGUID"] : '';
        if (empty($guid))
            $guid = isset($_SERVER["HTTP_X_UP_SUBNO"]) ? $_SERVER['HTTP_X_UP_SUBNO'] : '';
            ;
        
        if (empty($guid))
            $guid = isset($_SERVER["HTTP_X_JPHONE_UID"]) ? $_SERVER['HTTP_X_JPHONE_UID'] : '';
        
        if (empty($guid))
            $guid = isset($_SERVER["HTTP_X_EM_UID"]) ? $_SERVER['HTTP_X_EM_UID'] : '';
        
        $visitorId = self::getVisitorId($guid, $userAgent, $cookie);
        setrawcookie(self::$VID, $visitorId, $times + self::$COOKIE_USER_PERSISTENCE, self::$COOKIE_PATH, '.zhukejin.com');
        $utmUrl = "http://zdn.zhukejin.com/collector.gif?&vid=" . $visitorId . "&ref=" . urlencode($documentReferer) . "&path=" . urlencode($domain . $documentPath) . "&keyword=" . $keyword . "&rurl=" . $rurl . "&ip=" . self::checkIp(self::getIP()) . "&ver=" . self::$VERSION . "&inu=" . $inu .
                "&utmn=" . $times . self::getRandomNumber();
        return $utmUrl;
    }

    private static function getQ($url, $key) {
        preg_match("/(^|&|\\?|#)(" . $key . ")=([^&#]*)(&|$|#)/", $url, $matches);
        return count($matches) > 0 ? $matches[3] : NULL;
    }

    private static function checkIp($remoteAddress) {
        if (empty($remoteAddress)) return "";
        $regex = "/^([^.]+\.[^.]+\.[^.]+\.).*/";
        if (preg_match($regex, $remoteAddress, $matches)) return $matches[1] . "0";
        return "";
    }


    private static function getVisitorId($guid, $userAgent, $cookie) {
        if (!empty($cookie)) return $cookie;
        $message = "";
        if (!empty($guid)) $message = $guid;
        else $message = $userAgent . uniqid(self::getRandomNumber(), true);
        $md5String = md5($message);
        return "0x" . substr($md5String, 0, 16);
    }

    private static function getRandomNumber() {
        return rand(0, 0x7fffffff);
    }

    /**
    * [create_uuid 获取UUID]
    * @param  string $prefix [自定义前缀]
    * @return [string]         [uuid结果]
    */
    private static function getSign($prefix = "")
    {
        $str = md5(uniqid(mt_rand(), true));   
        $uuid  = substr($str,0,8) . '-';   
        $uuid .= substr($str,8,4) . '-';   
        $uuid .= substr($str,12,4) . '-';   
        $uuid .= substr($str,16,4) . '-';   
        $uuid .= substr($str,20,12);   
        return $prefix . $uuid;
    }

    private static function getActionId() {
        return self::getSign() . time();
    }

    /**
     * [getIp 获取ip 地址]
     * @return [type] [description]
     */
    private static function getIp() {
      static $realip = NULL;
      if ($realip !== NULL)
          return $realip;
      
      if (isset($_SERVER)) {
          if(isset($_SERVER['HTTP_CDN_SRC_IP'])) {
            $realip = $_SERVER['HTTP_CDN_SRC_IP'];
          } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
              $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
              foreach ($arr as $ip) {
                  $ip = trim($ip);
                  if ($ip != 'unknown') {
                      $realip = $ip;
                      break;
                  }
              }
          } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) { 
              $realip = $_SERVER['HTTP_CLIENT_IP'];
          } else {
              if (isset($_SERVER['REMOTE_ADDR'])) {
                  $realip = $_SERVER['REMOTE_ADDR'];
              } else { 
                  $realip = '0.0.0.0';
              }
          }
      } else {
          if(getenv('HTTP_CDN_SRC_IP')) {
            $realip = getenv('HTTP_CDN_SRC_IP');
          } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
              $realip = getenv('HTTP_X_FORWARDED_FOR');
          } elseif (getenv('HTTP_CLIENT_IP')) {
              $realip = getenv('HTTP_CLIENT_IP');
          } else {
              $realip = getenv('REMOTE_ADDR');
          }
      }
      preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
      $realip = ! empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
      return $realip;
    } 
}

/*FileEnd*/
