<?php

/**
 * TfValidator class file.
 * 
 * @copyright   Simon Wilkinson 2013+ (https://tuskfish.biz)
 * @license     https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html GNU General Public License (GPL) V2
 * @author      Simon Wilkinson <simon@isengard.biz>
 * @version     Release: 1.0
 * @since       1.0
 * @package     security
 */

// Enable strict type declaration.
declare(strict_types=1);

if (!defined("TFISH_ROOT_PATH")) die("TFISH_ERROR_ROOT_PATH_NOT_DEFINED");

/**
 * Provides methods to validate data types and to conduct range checks on parameters.
 * 
 * WARNING: The methods in this class validate TYPE COMPLIANCE ONLY. They DO NOT PROVIDE DATABASE
 * SAFETY in their own right. Use them in conjunction with prepared statements and bound values to
 * mitigate SQL injection.
 *
 * 1. Pass ALL STRING type data through the trimString() function first to check for UTF-8 encoding 
 * and basic whitespace & control character removal. Note that this function always returns a string,
 * so DO NOT USE IT ON NON-STRINGS. 
 * 
 * 2. Use the relevant type and pattern-specific methods to validate that other data types meet your
 * expectations.
 * 
 * @copyright   Simon Wilkinson 2013+ (https://tuskfish.biz)
 * @license     https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html GNU General Public License (GPL) V2
 * @author      Simon Wilkinson <simon@isengard.biz>
 * @version     Release: 1.0
 * @since       1.0
 * @package     security
 * @var         HTMLPurifier $htmlPurifier Instance of HTMLPurifier, used to filter HTML data.
 */
class TfValidator
{
    
    protected $htmlPurifier;
    
    /**
     * Constructor.
     * 
     * @param HTMLPurifier $htmlPurifier Instance of the HTMLPurifier library, used to validate
     * HTML. Note that HTMLPurifier should be configured before it is passed in, which is handled
     * automatically for you if you instantiate it via the TfValidatorFactory->getValidator()
     * factory class/method.
     */
    public function __construct(HTMLPurifier $htmlPurifier)
    {
        if (is_a($htmlPurifier, 'HTMLPurifier')) {
            $this->htmlPurifier = $htmlPurifier;
        } else {
            trigger_error(TFISH_ERROR_NOT_PURIFIER, E_USER_ERROR);
        }
    }
    
    /**
     * URL-encode and escape a query string for use in a URL.
     * 
     * Trims, checks for UTF-8 compliance, rawurlencodes and then escapes with htmlspecialchars().
     * If you wish to use the data on a landing page you must decode it with
     * htmlspecialchars_decode() followed by rawurldecode() in that order. But really, if you are
     * using any characters that need to be encoded in the first place you should probably just
     * stop.
     * 
     * @param string $url Unescaped input URL.
     * @return string Encoded and escaped URL.
     */
    public function encodeEscapeUrl(string $url)
    {
        $url = $this->trimString($url); // Trim control characters, verify UTF-8 character set.
        $url = rawurlencode($url); // Encode characters to make them URL safe.
        $cleanUrl = $this->escapeForXss($url); // Encode entities with htmlspecialchars()

        return $cleanUrl;
    }
    
    /**
     * Escape data for display to mitigate XSS attacks.
     * 
     * Casts to string and applies htmlentities to text fields destined for output / display to
     * limit XSS attacks. Encoding of quotes and use of UTF-8 character set is hardcoded in.
     *
     * @param mixed $output Unescaped string intended for display.
     * @return string XSS-escaped output string safe for display.
     */
    public function escapeForXss($dirty_text)
    {
        $dirty_text = (string) $dirty_text;
        
        if (isset($dirty_text)) {
            return htmlspecialchars($dirty_text, ENT_QUOTES, 'UTF-8', false);
        } else {
            return '';
        }
    }

    /**
     * Validate (and to some extent, "sanitise") HTML input to conform with whitelisted tags.
     * 
     * Applies HTMLPurifier to validate and sanitise HTML input. The precise operation can be
     * modified by altering the configuration of HTMLPurifier. Default options are to mandate
     * UTF-8 encoding and to enable HTML5-style IDs (anchor targets).
     *
     * @param string $dirty_html Unvalidated HTML input.
     * @return string Validated HTML content.
     */
    public function filterHtml(string $dirty_html)
    {
        if ($this->isUtf8($dirty_html)) {
            $clean_html = (string) $this->htmlPurifier->purify($dirty_html);
            return $clean_html;
        } else {
            return false;
        }
    }
    
    /**
     * Configure HTMLPurifier for use with Tuskfish.
     * 
     * Tuskfish requires HTMLPurifier to use UTF-8 encoding; to allow the ID attribute in HTML,
     * which is required to provide CSS selector targets; and support for HTML5 tags.
     * 
     * By default HTMLPurifier removes ID attributes from HTML markup, as duplicate IDs render
     * markup technically invalid. However, it is widely known that IDs are supposed to be unique
     * and not an issue if you are doing things properly. Removing IDs breaks CSS that uses IDs as 
     * selectors, which *is* an issue. 
     * 
     * @param array $config_options HTMLPurifier configuration options (see HTMLPurifier documentation).
     * @return object HTMLPurifier configuration object.
     */
    private function _configureHTMLPurifier(array $config_options)
    {
        // Set default configuration options.
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Core.Encoding', 'UTF-8');
        $config->set('Attr.EnableID', true);
        $config->set('Attr.ID.HTML5', true);

        // Set optional configuration options.
        if ($config_options) {
            foreach ($config_options as $key => $value) {
                $config->set($key, $value);
            }
        }
        
        return $config;
    }
    
    /**
     * Check if a file path contains traversals (including encoded traversals) or null bytes.
     * 
     * Directory traversals are not permitted in Tuskfish method parameters. If a path is found to
     * contain a traversal it is presumed to be an attack. Encoded traversals are a clear sign of
     * attempted abuse.
     * 
     * In general untrusted data should never be used to construct a file path. This method exists
     * as a second line safety measure.
     * 
     * @see https://www.owasp.org/index.php/Path_Traversal.
     * 
     * @param string $path
     * @return boolean True if a traversal or null byte is found, otherwise false.
     */
    public function hasTraversalorNullByte(string $path)
    {
        // List of traversals and null byte encodings.
        $traversals = array(
            "../",
            "..\\",
            "%2e%2e%2f", // Represents ../
            "%2e%2e/", // Represents ../
            "..%2f", // Represents ../
            "%2e%2e%5c", // Represents ..\
            "%2e%2e", // Represents ..\
            "..%5c", // Represents ..\
            "%252e%252e%255c", // Represents ..\
            "..%255c", // Represents ..\
            "..%c0%af", // Represents ../ (URL encoding)
            "..%c1%9c", // Represents ..\
            "%00", // URL-encoded null byte filename terminator.
            "\0", // C-style null byte (PHP functions are written in C).
            "0x00" // Hex-encoded null byte.
        );
        
        // Search the path for traversals.
        foreach ($traversals as $traverse) {
            if (mb_strripos($path, $traverse, 0, "utf-8")) {
                return true;
            }
        }
        
        // No traversals found.
        return false;
    }

    /**
     * Check that a string is comprised solely of alphabetical characters.
     * 
     * Tolerates vanilla ASCII only. Accented regional characters are rejected. This method is
     * designed to be used to check database identifiers or object property names.
     *
     * @param string $alpha Input to be tested.
     * @return bool True if valid alphabetical string, false otherwise.
     */
    public function isAlpha(string $alpha)
    {
        if (mb_strlen($alpha, 'UTF-8') > 0) {
            return preg_match('/[^a-z]/i', $alpha) ? false : true;
        } else {
            return false;
        }
    }

    /**
     * Check that a string is comprised solely of alphanumeric characters.
     * 
     * Accented regional characters are rejected. This method is designed to be used to check
     * database identifiers or object property names.
     *
     * @param string $alnum Input to be tested.
     * @return bool True if valid alphanumerical string, false otherwise.
     */
    public function isAlnum(string $alnum)
    {
        if (mb_strlen($alnum, 'UTF-8') > 0) {
            return preg_match('/[^a-z0-9]/i', $alnum) ? false : true;
        } else {
            return false;
        }
    }

    /**
     * Check that a string is comprised solely of alphanumeric characters and underscores.
     * 
     * Accented regional characters are rejected. This method is designed to be used to check
     * database identifiers or object property names.
     * 
     * @param string $alnumUnderscore Input to be tested.
     * @return bool True if valid alphanumerical or underscore string, false otherwise.
     */
    public function isAlnumUnderscore(string $alnumUnderscore)
    {
        if (mb_strlen($alnumUnderscore, 'UTF-8') > 0) {
            return preg_match('/[^a-z0-9_]/i', $alnumUnderscore) ? false : true;
        } else {
            return false;
        }
    }
    
    /**
     * Test if input is an array.
     *
     * @param mixed $array Input to be tested.
     * @return bool True if valid array otherwise false.
     */
    public function isArray($array)
    {
        return is_array($array);
    }

    /**
     * Validate boolean input.
     * 
     * Be careful with the return value; this method simply determines if a value is boolean or
     * not; it does not return the actual value of the parameter.
     *
     * @param mixed $bool Input to be tested.
     * @return bool True if a valid boolean value, false otherwise.
     */
    public function isBool($bool)
    {
        $result = filter_var($bool, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        
        if (is_null($result)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Check that a string is comprised solely of digits.
     *
     * @param string $digit Input to be tested.
     * @return bool True if valid digit string, false otherwise.
     */
    public function isDigit(string $digit)
    {
        if (mb_strlen($digit, 'UTF-8') > 0) {
            return preg_match('/[^0-9]/', $digit) ? false : true;
        } else {
            return false;
        }
    }

    /**
     * Check if an email address is valid.
     * 
     * Note that single quotes ' are a valid character in email addresses, so the output of this 
     * filter does NOT indicate that the value is database safe.
     *
     * @param string $email Input to be tested.
     * @return boolean True if valid email address, otherwise false.
     */
    public function isEmail(string $email)
    {
        if (mb_strlen($email, 'UTF-8') > 2) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        } else {
            return false;
        }
    }

    /**
     * Validate float (decimal point allowed).
     * 
     * Note that is_float() allows exponents.
     *
     * @param mixed $float Input to be tested.
     * @return boolean True if valid float, otherwise false.
     */
    public function isFloat($float)
    {
        return is_float($float);
    }

    /**
     * Validate integer, optionally include range check.
     * 
     * @param mixed $int Input to be tested.
     * @param int $min Minimum acceptable value.
     * @param int $max Maximum acceptable value.
     * @return bool True if valid int and within optional range check, false otherwise.
     */
    public function isInt($int, int $min = null, int $max = null)
    {
        $clean_int = is_int($int) ? (int) $int : null;
        $clean_min = is_int($min) ? (int) $min : null;
        $clean_max = is_int($max) ? (int) $max : null;

        // Range check on minimum and maximum value.
        if (is_int($clean_int) && is_int($clean_min) && is_int($clean_max)) {
            return ($clean_int >= $clean_min) && ($clean_int <= $clean_max) ? true : false;
        }

        // Range check on minimum value.
        if (is_int($clean_int) && is_int($clean_min) && !isset($clean_max)) {
            return $clean_int >= $clean_min ? true : false;
        }

        // Range check on maximum value.
        if (is_int($clean_int) && !isset($clean_min) && is_int($clean_max)) {
            return $clean_int <= $clean_max ? true : false;
        }

        // Simple use case, no range check.
        if (is_int($clean_int) && !isset($clean_min) && !isset($clean_max)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Validates IP addresses. Accepts private (but not reserved) ranges. Optionally IPV6.
     *
     * @param string $ip Input to be tested.
     * @param int $version IP address version ('4' or '6').
     * @return bool True if valid IP address, false otherwise.
     */
    public function isIp(string $ip, int $version = null)
    {
        if (isset($version) && $version === 6) {
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6
                    | FILTER_FLAG_NO_RES_RANGE) === false) {
                return true;
            } else {
                return false;
            }
        } else {
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4
                    | FILTER_FLAG_NO_RES_RANGE) === false) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Tests if the input is null (ie set but without an assigned value) or not.
     * 
     * @param mixed $null Input to be tested.
     * @return bool True if input is null otherwise false.
     */
    public function isNull($null)
    {
        return is_null($null);
    }
    
    /**
     * Test if input is an object.
     * 
     * @param mixed $object Input to be tested.
     * @return bool True if valid object otherwise false.
     */
    public function isObject($object)
    {
        return is_object($object);
    }

    /**
     * Tests if input is a resource.
     * 
     * @param mixed $resource Input to be tested.
     * @return bool True if valid resource otherwise false.
     */
    public function isResource($resource)
    {
        return is_resource($resource);
    }
    
    /**
     * Validate URL.
     * 
     * Only accepts http:// and https:// protocol and ASCII characters. Other protocols
     * and internationalised domain names will fail validation due to limitation of filter.
     *
     * @param string $url Input to be tested.
     * @return bool True if valid URL otherwise false.
     */
    public function isUrl(string $url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED
                | FILTER_FLAG_HOST_REQUIRED)) {
            if (mb_substr($url, 0, 7, 'UTF-8') === 'http://'
                    || mb_substr($url, 0, 8, 'UTF-8') === 'https://') {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if the character encoding of text is UTF-8.
     * 
     * All strings received from external sources must be passed through this function, particularly
     * prior to storage in the database.
     * 
     * @param string $dirty_string Input string to check.
     * @return bool True if string is UTF-8 encoded otherwise false.
     */
    public function isUtf8(string $dirty_string)
    {
        return mb_check_encoding($dirty_string, 'UTF-8');
    }
    
    /**
     * Cast to string, check UTF-8 encoding and strip trailing whitespace and control characters.
     * 
     * Removes trailing whitespace and control characters (ASCII <= 32), checks for UTF-8 character
     * set and casts input to a string. Note that the data returned by this function still
     * requires escaping at the point of use; it is not database or XSS safe.
     * 
     * As the input is cast to a string do NOT apply this function to non-string types (int, float,
     * bool, object, resource, null, array, etc).
     * 
     * @param mixed $dirty_string Input to be trimmed.
     * @return string Trimmed and UTF-8 validated string.
     */
    public function trimString($dirty_string)
    {
        $dirty_string = (string) $dirty_string;
        
        if ($this->isUtf8($dirty_string)) {
            // Trims all control characters plus space (ASCII / UTF-8 points 0-32 inclusive).
            return trim($dirty_string, "\x00..\x20");
        } else {
            return '';
        }
    }

}
