<?php

/**
 * Description of markup
 *
 */

namespace Shared {

    class Markup {

        public function __construct() {
            // do nothing
        }

        public function __clone() {
            // do nothing
        }

        public static function errors($array, $key, $separator = "<br />", $before = "<br />", $after = "") {
            if (isset($array[$key])) {
                return $before . join($separator, $array[$key]) . $after;
            }
            return "";
        }

        public static function message($msg=NUll) {
            if (!isset($msg)) return '';
            if (is_array($msg)) {
                switch ($msg["type"]) {
                    case 'success':
                        return '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>'.$msg["text"].'</div>';
                    case 'error':
                        return '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>'.$msg["text"].'</div>';
                    default:
                        return '<div class="alert alert-info alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>'.$msg["text"].'</div>';
                }
            } else {
                return '<div class="alert alert-info alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>'.$msg.'</div>';
            }
        }

      

        public static function nice_pagination($tpages, $limit, $pg) {
            if ($tpages > 1 && $tpages <= 6) {
                $str = '<ul class="pagination justify-content-center pagination-sm">';
                for ($i=0; $i < $tpages; $i++) {
                    $pag = 1 + $i;$cls = "";
                    if ($pg == $pag) {
                        $cls = "active";
                    }
                    $str .= '<li class="page-item '.$cls.'"><a class="page-link" href="'.self::pagination($pag).'">'.($i*$limit + 1).' - '.$pag*$limit.'</a></li>';
                }

                $str .= '</ul>';
                return $str;
            }

            if ($tpages > 6) {
                $str = '<br><nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
                if ($pg != 1) {
                    $str .= '<li class="page-item"><a class="page-link" href="'.self::pagination($pg - 1).'">Previous</a></li>';
                }
                if ($pg < $tpages) {
                    $str .= '<li class="page-item"><a class="page-link" href="'.self::pagination($pg + 1).'">Next</a></li>';
                }
                $str .= '</ul></nav>';
                return $str;
            }
        }

        public static function pagination($page) {
            if (strpos(URL, "?")) {
                $request = explode("?", URL);
                if (strpos($request[1], "=")) {
                    parse_str($request[1], $params);
                }
                $params["page"] = $page;
                $finalurl = $request[0]."?".http_build_query($params);
            } else {
                $params["page"] = $page;
                $finalurl = URL."?".http_build_query($params);
            }
            return $finalurl;
        }

        public static function models() {
            $model = array();
            $path = APP_PATH . "/application/models";
            $iterator = new \DirectoryIterator($path);

            foreach ($iterator as $item) {
                if (!$item->isDot() && $item->isFile()) {
                    array_push($model, substr($item->getFilename(), 0, -4));
                }
            }
            return $model;
        }

        public function nice_number($n = null) {
            if (!isset($n)) return 0;
            // strip any formatting;
            $n = (0+str_replace(",", "", $n));
            // is this a number?
            if (!is_numeric($n)) return false;
            // now filter it;
            if ($n > 1000000000000) return round(($n/1000000000000), 2).'T';
            elseif ($n > 1000000000) return round(($n/1000000000), 2).'B';
            elseif ($n > 1000000) return round(($n/1000000), 2).'M';
            elseif ($n > 1000) return round(($n/1000), 2).'K';
            return number_format($n);
        }

        /**
         * Get either a Gravatar URL or complete image tag for a specified email address.
         *
         * @param string $email The email address
         * @param string $s Size in pixels, defaults to 80px [ 1 - 2048 ]
         * @param string $d Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
         * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
         * @param boole $img True to return a complete IMG tag False for just the URL
         * @param array $atts Optional, additional key/value attributes to include in the IMG tag
         * @return String containing either just a URL or a complete image tag
         * @source https://gravatar.com/site/implement/images/php/
         */
        function get_gravatar( $email, $s = 80, $d = 'mm', $r = 'g', $img = false, $atts = array() ) {
            $url = 'https://en.gravatar.com/avatar/';
            $url .= md5( strtolower( trim( $email ) ) );
            $url .= "?s=$s&d=$d&r=$r";
            if ( $img ) {
                $url = '<img src="' . $url . '"';
                foreach ( $atts as $key => $val )
                    $url .= ' ' . $key . '="' . $val . '"';
                $url .= ' />';
            }
            return $url;
        }


        public static function randomPass() {
            $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
            $pass = array(); //remember to declare $pass as an array
            $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
            for ($i = 0; $i < 8; $i++) {
                $n = rand(0, $alphaLength);
                $pass[] = $alphabet[$n];
            }
            return implode($pass); //turn the array into a string
        }

        

        public static function isActiveHtml($obj) {
            $html = "<span ";
            if ($obj->live) {
                $html .= 'class = "badge badge-success">Active';
            } else {
                $html .= 'class = "badge badge-warning">Inactive';
            }
            $html .= "</span>";
            return $html;
        }

        public static function parse_url($url = null) {
            if (is_null($url)) {
                $url = URL; // constant defined in "public/index.php"
            }
            return parse_url($url);
        }

        public static function falseto0($value) {
            if ($value) {
                return 1;
            }
            return 0;
        }

        protected static function _uaDevice($ua) {
            if (preg_match('/(iPad|SCH-I800|xoom|kindle)/', $ua)) {
                $device = 'tablet';
            }
            else if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $ua)) {
                $device = 'mobile';
            } else {
                $device = 'desktop';
            }

            return $device;
        }

        public static function uaParser($ua = null) {
            if (is_null($ua)) {
                $ua = \Framework\RequestMethods::server('HTTP_USER_AGENT');
            }
            $parser = \UAParser\Parser::create(); $parserResult = $parser->parse($ua);

            return [
                'browser' => $parserResult->ua->toString(),
                'os' => $parserResult->os->toString(),
                'device' => self::_uaDevice($ua)
            ];
        }

        public static function status($status) {
            if (is_bool($status)) {
                $status = (int) $status;
            }
            $status = (string) $status;
            switch ($status) {
                case 'active':
                case 'authenticated':
                    return '<span class="badge badge-success">'.ucfirst($status).'</span>';
                case 'paused':
                case 'disabled':
                case 'pending':
                case 'halted':
                    return '<span class="badge badge-warning">'.ucfirst($status).'</span>';
                case 'pending':
                case 'completed':
                    return '<span class="badge badge-info">'.ucfirst($status).'</span>';
                case 'deleted':
                case 'cancelled':
                    return '<span class="badge badge-danger">'.ucfirst($status).'</span>';
                case '1':
                    return '<span class="badge badge-success">Active</span>';
                case '0':
                    return '<span class="badge badge-warning">Inactive</span>';
                default:
                    return '';
            }
        }

      
    }

}
