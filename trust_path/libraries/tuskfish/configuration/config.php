<?php

/**
* Tuskfish configuration script.
* 
* Stores the site salt (used for recursive password hashing), key and database path. Included in
* every page via mainfile.php / masterfile.php  
*
* @copyright	Simon Wilkinson (Crushdepth) 2013-2016
* @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html GNU General Public License (GPL) V2
* @since		1.0
* @author		Simon Wilkinson (Crushdepth) <simon@isengard.biz>
* @package		core
*/

if (!defined("TFISH_ROOT_PATH")) die("TFISH_ERROR_ROOT_PATH_NOT_DEFINED");

// Constants that make use of the physical path.
define("TFISH_ADMIN_PATH", TFISH_ROOT_PATH . "admin/");
define("TFISH_CACHE_PATH", TFISH_ROOT_PATH . "cache/");
define("TFISH_TEMPLATES_PATH", TFISH_ROOT_PATH . "templates/");
define("TFISH_TEMPLATES_BLOCK_PATH", TFISH_ROOT_PATH . "templates/blocks/");
define("TFISH_UPLOADS_PATH", TFISH_ROOT_PATH . "uploads/");
define("TFISH_MEDIA_PATH", TFISH_UPLOADS_PATH . "media/");
define("TFISH_IMAGE_PATH", TFISH_UPLOADS_PATH . 'image/');


// Constants that make use of the trust path (which is a derivative of the physical path).
define("TFISH_CLASS_PATH", TFISH_PATH . "class/");
define("TFISH_DATABASE_PATH", TFISH_TRUST_PATH . "database/");
define("TFISH_ERROR_LOG_PATH", TFISH_TRUST_PATH . "log/tuskfish_log.txt");
define("TFISH_FORM_PATH", TFISH_PATH . "form/");
define("TFISH_LIBRARIES_PATH", TFISH_TRUST_PATH . "libraries/");

// Constants that make use of the virtual (URL) path, these refer to assets accessed by URL
define("TFISH_ADMIN_URL", TFISH_URL . "admin/");
define("TFISH_CACHE_URL" , TFISH_URL . "cache/");
define("TFISH_TEMPLATES_URL", TFISH_URL . "templates/");
define("TFISH_RSS_URL", TFISH_URL . "rss.php");
define("TFISH_PERMALINK_URL", TFISH_URL . "permalink.php?id=");
define("TFISH_MEDIA_URL", TFISH_URL . "uploads/media/");
define("TFISH_IMAGE_URL", TFISH_URL . "uploads/image/");

// RSS enclosure URL - the spec requires that the URL use http protocol, as https will invalidate feed.
if (parse_url(TFISH_URL, PHP_URL_SCHEME) == 'https') {
	define("TFISH_ENCLOSURE_URL", "http://" . parse_url(TFISH_URL, PHP_URL_HOST) . "/enclosure.php?id=");
} else {
	define("TFISH_ENCLOSURE_URL", TFISH_URL . "enclosure.php?id=");
}

/*
 * Preferences
 */
// Language: Specify the file name of the default language file
define("TFISH_LANGUAGE_PATH", TFISH_PATH . "language/");
define("TFISH_DEFAULT_LANGUAGE", TFISH_LANGUAGE_PATH . "english.php");

if (!defined("TFISH_SITE_SALT")) define("TFISH_SITE_SALT", "YN1j+i/CnV933fNii0lycDYvQ+HfpGEiK4NB6jk/7sUnfV0SEfEURa3y1ZaAkxd2");
if (!defined("TFISH_KEY")) define("TFISH_KEY", "vkOgkkp2vl27riArGxK486Ei1M2sak4D0neezxJjBYr0Q4LQpdlEXKLZoSno2nK");
if (!defined("TFISH_DATABASE")) define("TFISH_DATABASE", "/home/isengard/public_html/tuskfish/trust_path/database/244542853_tfish11.db");