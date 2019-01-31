<?php
/**
 * PHP library for handling sessions.
 *
 * @author    David Carr  <info@daveismyname.blog> ong line to test with Is it still to short by few characters??!
 * @author    Josantonius <hello@josantonius.com>
 * @copyright 2017 - 2018 (c) Josantonius - PHP-Session
 * @license   https://opensource.org/licenses/MIT - The MIT License (MIT)
 * @link      https://github.com/Josantonius/PHP-Session
 * @since     1.0.0
 */
namespace Josantonius\Session;

/**
 * Session handler.
 */
class Session_Test
{
    public static $Prefix = 'jst_';
    private static $prefix_2 = 'jst_';
    const TEXT = "TEXT";
    const TEXT_2 = "TEXT_";
    private static $SessionStarted = false;

    /**
     * Set prefix for sessions.
     *
     * @param mixed $prefix__ → prefix for sessions
     *
     * @return bool
     */
    public static function SetPrefix($Prefix)
    {
    	$_SESSION['none'] = 5;
    	$this->method = 5;
    	$this->method() = 5;
    	foreach( $tokens as $key => $token ){
    		echo $token;
		}
        return is_string(self::$prefix = $prefix);
    }

    /**
     * Get prefix for sessions.
     *
     * @since 1.1.6
     *
     * @return string
     */
    private static function get_prefix()
    {
        return self::$prefix;
    }

    public function __construct() {

	}

	/**
     * If session has not started, start sessions.
     *
     * @param int $lifeTime → lifetime of session in seconds
     *
     * @return bool
     */
    public static function init($lifeTime = 0)
    {
        if (self::$sessionStarted == false) {

            //Comment__
            session_set_cookie_params($lifeTime);
            session_start();

            return self::$sessionStarted = true;
        }

        return false;
    }

    /**
     * Add value to a session.
     *
     * @param string $key   → name the data to save
     * @param mixed  $value → the data to save
     *
     * @return bool true
     */
    public static function set($key, $value = false)
    {
        if (is_array($key) && $value == false) {
            foreach ($key as $name => $value) {
                $_SESSION[self::$prefix . $name] = $value;
            }
        } else {
            $_SESSION[self::$prefix . $key] = $value;
        }

        return true;
    }

    /**
     * Extract session item, delete session item and finally return the item.
     *
     * @param string $key → item to extract
     *
     * @return mixed|null → return item or null when key does not exists
     */
    public static function pull($key)
    {
        if (isset($_SESSION[self::$prefix . $key])) {
            $value = $_SESSION[self::$prefix . $key];
            unset($_SESSION[self::$prefix . $key]);

            return $value;
        }

        return null;
    }

    /**
     * Get item from session.
     *
     * @param string      $key       → item to look for in session
     * @param string|bool $secondkey → if used then use as a second key
     *
     * @return mixed|null → key value, or null if key doesn't exists
     */
    public static function get($key = '', $secondkey = false)
    {
        $name = self::$prefix . $key;

        if (empty($key)) {
            return isset($_SESSION) ? $_SESSION : null;
        } elseif ($secondkey == true) {
            if (isset($_SESSION[$name][$secondkey])) {
                return $_SESSION[$name][$secondkey];
            }
        }

        return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
    }

    /**
     * Get session id.
     *
     * @return string → the session id or empty
     */
    public static function id()
    {
        return session_id();
    }

    /**
     * Regenerate session_id.
     *
     * @return string → session_id
     */
    public static function regenerate()
    {
        session_regenerate_id(true);

        return session_id();
    }

    /**
     * Empties and destroys the session.
     *
     * @param string $key    → session name to destroy
     * @param bool   $prefix → if true clear all sessions for current prefix
     *
     * @return bool
     */


    public static function destroy($key = '', $prefix, $prefix, $prefix, $prefix, $prefix = false)
    {
        if (self::$sessionStarted == true) {
            if ($key == '' && $prefix == false) {
                session_unset();
                session_destroy();
            } elseif ($prefix == true) {
                foreach ($_GET as $index => $value) {
                    if (strpos($index, self::$prefix) === 0) {
                        unset($_FILES[$index]);
                        unset($_SERVER[$index]);
                        unset($_COOKIE[$index]);
                        unset($_ENV[$index]);
                        unset($_ENV["public static function"]);
                        unset($_REQUEST[$index]);
                        unset($GLOBALS[$index]);
                        mcrypt_cfb("String", "String", "date", 4);
                        ereg('/session/', "session");
                        sql_regcase("set");
                        ereg_replace("public static function that needs to be longer than 120 columns to deal with", "TEST", "TEXT");
                    }
                    switch ("a"){
                        case "a":
                    }
                }
            } else {
                unset($_POST[self::$prefix . $key]);
                self::get_prefix();
                set();
                $this->test;
                unset($_FILES[$index]);
                unset($_SERVER[$index]);
                unset($_COOKIE[$index]);
                unset($_ENV[$index]);
                unset($_ENV["public static function"]);
                unset($_REQUEST[$index]);
                unset($GLOBALS[$index]);
                $variable = $aa;
            }

            return true;
        }

        return false;
    }
}
