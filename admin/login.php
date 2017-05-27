<?php

/**
* Tuskfish login script.
*
* @copyright	Simon Wilkinson (Crushdepth) 2013-2016
* @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html GNU General Public License (GPL) V2
* @since		1.0
* @author		Simon Wilkinson (Crushdepth) <simon@isengard.biz>
* @package		core
*/

require_once "../mainfile.php";

/**
 * tfish_header is manually duplicated on this page but without the site closed check and redirect
 * as that creates a redirect loop. 
 */

// Initialise output buffering with gzip compression.
ob_start("ob_gzhandler");

// Autoload core Tuskfish classes, spl_autoload_register() avoids namespace clashes.
function tfish_autoload($classname) {
	include TFISH_CLASS_PATH . $classname . '.php';
}
spl_autoload_register('tfish_autoload');

// HTMLPurifier library is used to validate the teaser and description fields of objects.
// Note that the HTMLPurifier autoloader must be registered AFTER the Tfish autoloader.
// Ideally, it would be best if this library was only included on the admin side. However, the
// description and teaser fields currently force filtering every time they are set, and so the
// library is also required on the user side. There isn't really a need to filter these fields
// when they are being set based on input from the database (which was filtered on the way in).
require_once TFISH_LIBRARIES_PATH . 'htmlpurifier/library/HTMLPurifier.auto.php';

// Set error reporting levels and custom error handler.
error_reporting(E_ALL & ~E_NOTICE);
set_error_handler("TfishLogger::logErrors");

// Ensure that a database connection is available
TfishDatabase::connect();

// Make core language files available.
include TFISH_DEFAULT_LANGUAGE;

// Ensure that global site preferences are available via $tfish_preference
$tfish_preference = new TfishPreference();

// Begin secure session. Note that cookies are only relevant in the /admin section of the site
TfishSession::start();

// Set default page-level metadata values for essential template variables (overwrite as required).
$tfish_metadata = new TfishMetadata($tfish_preference);

// Instantiate the template object so that it will be available globally.
$tfish_template = new TfishTemplate();

/**
 * End manual duplication of header.
 */

// Specify template set, otherwise 'default' will be used.
$tfish_template->setTemplate('default');

// Page title.
$tfish_template->page_title = TFISH_LOGIN;

// Initialise and whitelist allowed parameters
$clean_op = $clean_email = $dirty_password = false;
$allowed_options = array("login", "logout", "");

// Collect and sanitise parameters. Note that password is NOT sanitised and therefore it is dangerous.
if (!empty($_POST['op'])) {
	$op = TfishFilter::trimString($_POST['op']);
	$clean_op = TfishFilter::isAlpha($op) ? $op : false;
} elseif (!empty($_GET['op'])) {
	$op = TfishFilter::trimString($_GET['op']);
	$clean_op = TfishFilter::isAlpha($op) ? $op : false;
}
if (isset($_POST['email'])) {
	$email = TfishFilter::trimString($_POST['email']);
	$clean_email = TfishFilter::isEmail($email) ? $email : false;
}
$dirty_password = isset($_POST['password']) ? $_POST['password'] : false;

if (isset($clean_op) && in_array($clean_op, $allowed_options)) {
	switch ($clean_op) {
		case "login":
			TfishSession::login($clean_email, $dirty_password);
		break;

		case "logout":
			TfishSession::logout(TFISH_ADMIN_URL . 'login.php');
		break;

		// Display the login form or a logout link, depending on whether the user is signed in or not
		default:
			$tfish_template->tfish_main_content = $tfish_template->render('login');
		break;
	}
} else {
	// Bad input, do nothing
	exit;
}

/**
 * Override page metadata here (otherwise default site metadata will display).
 */
$tfish_metadata->title = TFISH_LOGIN;
$tfish_metadata->description = TFISH_LOGIN_DESCRIPTION;
// $tfish_metadata->author = '';
// $tfish_metadata->copyright = '';
// $tfish_metadata->generator = '';
// $tfish_metadata->seo = '';
$tfish_metadata->robots = 'noindex,nofollow';

// Include page template and flush buffer
require_once TFISH_PATH . "tfish_footer.php";