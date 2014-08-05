<?php
/**
 * php-ansi-color
 *
 * Original
 *     https://github.com/loopj/commonjs-ansi-color
 *
 * @code
 * <?php
 * require_once "ansi-color.php";
 *
 * use PhpAnsiColor\Color;
 *
 * // Print the word "Error" to stdout in red
 * error_log(Color::set("Error", "red"));
 *
 * // Print the word "Error" in red and underlined
 * error_log(Color::set("Error", "red+underline"));
 *
 * // Print the word "Success" in bold green, followed by a message
 * error_log(Color::set("Success", "green+bold"), "Something was successful!");
 * @endcode
 */
namespace PhpAnsiColor;

class Color
{
    protected static $ANSI_CODES = array(
        "off"        => 0,
        "bold"       => 1,
        "italic"     => 3,
        "underline"  => 4,
        "blink"      => 5,
        "inverse"    => 7,
        "hidden"     => 8,
        "black"      => 30,
        "red"        => 31,
        "green"      => 32,
        "yellow"     => 33,
        "blue"       => 34,
        "magenta"    => 35,
        "cyan"       => 36,
        "white"      => 37,
        "black_bg"   => 40,
        "red_bg"     => 41,
        "green_bg"   => 42,
        "yellow_bg"  => 43,
        "blue_bg"    => 44,
        "magenta_bg" => 45,
        "cyan_bg"    => 46,
        "white_bg"   => 47
    );

    public static function set($str, $color)
    {
        $color_attrs = explode("+", $color);
        $ansi_str = "";
        foreach ($color_attrs as $attr) {
            $ansi_str .= "\033[" . self::$ANSI_CODES[$attr] . "m";
        }
        $ansi_str .= $str . "\033[" . self::$ANSI_CODES["off"] . "m";
        return $ansi_str;
    }

    public static function log($message, $color)
    {
        error_log(self::set($message, $color));
    }

    public static function replace($full_text, $search_regexp, $color)
    {
        $new_text = preg_replace_callback(
            "/($search_regexp)/",
            function ($matches) use ($color) {
                return Color::set($matches[1], $color);
            },
            $full_text
        );
        return is_null($new_text) ? $full_text : $new_text;
    }

    public static function errorHandler($errno, $errstr, $errfile, $errline)
    {
        if (!(error_reporting() & $errno)) {
            // This error code is not included in error_reporting
            return;
        }

        switch ($errno) {
        case E_USER_ERROR:
            echo self::set("<b>My ERROR</b> [$errno] $errstr<br />\n" .
            "  Fatal error on line $errline in file $errfile" .
            ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n", "red+underline");
            exit(1);
            break;

        case E_USER_WARNING:
            echo self::set("WARNING", "red") . " [$errno] $errstr<br />\n";
            break;

        case E_USER_NOTICE:
            echo self::set("NOTICE", "yellow") . " [$errno] $errstr<br />\n";
            break;

        default:
            echo self::set("Unknown Error Type:", "yellow+italic") . " [$errno] $errstr<br />\n";
            break;
        }

        /* Don't execute PHP internal error handler */
        return true;
    }

    public static function setErrorHandler() {
        set_error_handler(array(self, "errorHandler"));
    }
}
