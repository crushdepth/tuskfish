<?php

/**
 * Tuskfish footer script, must be included on every page.
 * 
 * Includes the main layout template, kills the database connection and flushes the output buffer.
 * The page will not render unless this file is included.
 *
 * @copyright   Simon Wilkinson 2013+ (https://tuskfish.biz)
 * @license     https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html GNU General Public License (GPL) V2
 * @author      Simon Wilkinson <simon@isengard.biz>
 * @since       1.0
 * @package     core
 */
// Enable strict type declaration.
declare(strict_types=1);

// Include the relevant page template, or the default if not set.
if ($tfTemplate && !empty($tfTemplate->getTheme())) {
    include_once TFISH_THEMES_PATH . $tfTemplate->getTheme() . "/theme.html";
} else {
    include_once TFISH_THEMES_PATH . "default/theme.html";
}

// Close the database connection.
$tfDatabase->close();

// Write the contents of the buffer to the cache.
if ($tfPreference->enableCache && isset($basename)) {
    $tfCache->cachePage($basename, $cacheParameters, ob_get_contents());
}

// Flush the output buffer to screen and clear it.
ob_end_flush();
