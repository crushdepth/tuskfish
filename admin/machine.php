<?php

/**
 * Admin controller script for the Machines module.
 * 
 * @copyright   Simon Wilkinson 2018+ (https://tuskfish.biz)
 * @license     https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html GNU General Public License (GPL) V2
 * @author      Simon Wilkinson <simon@isengard.biz>
 * @since       1.0
 * @package     machines
 */
// Enable strict type declaration.
declare(strict_types=1);

// Boot! Set file paths, preferences and connect to database.
require_once "../mainfile.php";
require_once TFISH_ADMIN_PATH . "tfAdminHeader.php";
require_once TFISH_MODULE_PATH . "machines/tfMachinesHeader.php";

// Specify the admin theme you want to use.
$tfTemplate->setTheme('admin');

/**
 * Validate input parameters here.
 **/

// Permitted options.
$op = isset($_REQUEST['op']) ? $tfValidator->trimString($_REQUEST['op']) : false;
$optionsWhitelist = array();

if (in_array($op, $optionsWhitelist, true)) {
    exit;
}
    
// Cross-site request forgery check.
if (!in_array($op, $optionsWhitelist, true)) {
    TfSession::validateToken($cleanToken);
}

// Business logic goes here.
switch ($op) {
    // Various cases.
}

/**
 * Override page template here (otherwise default site metadata will display).
 */
// $tfMetadata->setTitle('');
// $tfMetadata->setDescription('');
// $tfMetadata->setAuthor('');
// $tfMetadata->setCopyright('');
// $tfMetadata->setGenerator('');
// $tfMetadata->setSeo('');
$tfMetadata->setRobots('noindex,nofollow');

// Include page template and flush buffer
require_once TFISH_PATH . "tfFooter.php";