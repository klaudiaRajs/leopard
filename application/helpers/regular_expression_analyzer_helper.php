<?php


class RegularExpressionsAnalyzer{

    public static function findDeprecatedFunctions($line){
        $functions = [
            '/call_user_method/' => 'call_user_func()',
            '/call_user_method_array/' => 'call_user_func_array()',
            '/define_syslog_variables/' => null,
            '/dl/' => null,
            '/ereg/' => 'preg_match()',
            '/ereg_replace/' => 'preg_replace()',
            '/eregi/' => 'preg\_match with the \'i\' modifier',
            '/eregi_replace/' => 'preg\_replace with the \'i\' modifier',
            '/mcrypt_generic_end/' => null,
            '/set_magic_quotes_runtime/' => null,
            '/magic_quotes_runtime/' => null,
            '/session_register/' => '$\_SESSION',
            '/session_unregister/' => '$\_SESSION',
            '/session_is_registered/' => '$\_SESSION',
            '/set_socket_blocking/' => 'stream\_set\_blocking',
            '/split/' => 'preg\_split',
            '/spliti/' => 'preg\_split with the \'i\' modifier',
            '/sql_regcase/'=> null,
            '/mysql_db_query/' => 'mysql\_select\_db and mysql\_query',
            '/mysql_escape_string/' => 'mysql\_real\_escape\_string',
            '/mysql_list_dbs/' => null,
            '/datefmt_set_timezone_id/' => 'datefmt\_set\_timezone',
            '/mcrypt_cbc/' => null,
            '/mcrypt_cfb/' => null,
            '/mcrypt_ecb/' => null,
            '/mcrypt_ofb/' => null,
            '/ldap_sort/' => null,
        ];

        foreach ($functions as $function => $alternative) {
            preg_match($function, $line, $found);
            if (!empty($found)) {
                $line = $line . Messages::MarkLineWithDeprecatedMethod($function, $alternative);
            }
        }
        return $line;
    }

    public static function findUsingStaticMethods($line){
        //Regex -> finds anything that contains 'static function' outside of single or double quoated string
            $globals = ['/(?<!\')(?<!\").static function.(?<!\')(?<!\")/'];

        foreach ($globals as $global) {
            preg_match($global, $line, $found);
            if (!empty($found)) {
                $line = $line . Messages::MarkLineWithStatic();
            }
        }

        return $line;
    }

    public static function findGlobals($line)
    {
        $globals = ['/\$_SESSION/', '/\$_POST/', '/\$_GET/', '/\$_FILES/', '/\$_SERVER/', '/\$_COOKIE/', '/\$_ENV/', '/\$_REQUEST/', '/\$GLOBALS/'];

        foreach ($globals as $global) {
            preg_match($global, $line, $found);
            if (!empty($found)) {
                $line = $line . Messages::MarkLineWithGlobals();
            }
        }

        return $line;
    }

}