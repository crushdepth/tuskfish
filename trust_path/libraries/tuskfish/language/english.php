<?php

/**
 * Tuskfish core language constants (English).
 * 
 * Translate this file to convert Tuskfish to another language. To actually use a translated language
 * file, edit /trust_path/masterfile.php and change the TFISH_DEFAULT_LANGUAGE constant to point at
 * your translated language file.
 *
 * @copyright   Simon Wilkinson 2013+ (https://tuskfish.biz)
 * @license     https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html GNU General Public License (GPL) V2
 * @author      Simon Wilkinson <simon@isengard.biz>
 * @since       1.0
 * @package     language
 */
// System wide and generic constants.
define("TFISH_TUSKFISH", "Tuskfish");
define("TFISH_CMS", "Tuskfish CMS");
define("TFISH_WELCOME", "Welcome to Tuskfish CMS");
define("TFISH_ID", "ID");
define("TFISH_TYPE", "Type");
define("TFISH_TITLE", "Title");
define("TFISH_TEASER", "Teaser");
define("TFISH_DESCRIPTION", "Description");
define("TFISH_FILE_SIZE", "Bytes");
define("TFISH_DATE", "Date");
define("TFISH_ONLINE_STATUS", "Status");
define("TFISH_ONLINE", "Online");
define("TFISH_OFFLINE", "Offline");
define("TFISH_TAGS", "Tags");
define("TFISH_SUBMISSION_TIME", "Submitted");
define("TFISH_LAST_UPDATED", "Last updated");
define("TFISH_EXPIRES_ON", "Expires on");
define("TFISH_COUNTER", "Counter");
define("TFISH_VIEWS", "views"); // Alternative representation of counter (type-dependent context).
define("TFISH_META_TITLE", "Title");
define("TFISH_META_DESCRIPTION", "Description");
define("TFISH_SEO", "SEO");

// admin/login.php
define("TFISH_LOGIN", "Login");
define("TFISH_LOGIN_DESCRIPTION", "Login to the administrative interface of Tuskfish.");
define("TFISH_LOGOUT", "Logout");
define("TFISH_PASSWORD", "Password");
define("TFISH_EMAIL", "Email");
define("TFISH_ACTION", "Action");
define("TFISH_YOU_ARE_ALREADY_LOGGED_IN", "You are already logged in.");
define("TFISH_YUBIKEY", "Yubikey");
define("TFISH_YUBIKEY_NO_SIGNATURE_KEY", "No Yubikey signature key in config.php. See the manual "
        . "for Yubikey setup.");

// admin/admin.php
define("TFISH_ADMIN", "Admin");
define("TFISH_DASHBOARD", "Dashboard");
define("TFISH_SELECT_STATUS", "- Select status -");
define("TFISH_SELECT_TAGS", "- Select tag -");
define("TFISH_SELECT_TYPE", "- Select type -");
define("TFISH_SELECT_PARENT", "- Select parent -");
define("TFISH_SELECT_BOX_ZERO_OPTION", "---");
define("TFISH_META_TAGS", "Meta tags");
define("TFISH_DELETE", "Delete");
define("TFISH_EDIT", "Edit");
define("TFISH_CHANGE_PASSWORD", "Change password");
define("TFISH_CHANGE_PASSWORD_EXPLANATION", "Please enter and confirm your new administrative "
        . "password in the form below to change it. Passwords must be at least 15 characters long "
        . "and contain at least least one upper and lower case letter, number and symbol.");
define("TFISH_STATIC_PAGES", "Static pages");
define("TFISH_FLUSH_CACHE", "Flush cache");
define("TFISH_DO_YOU_WANT_TO_FLUSH_CACHE", "Do you want to flush the cache?");
define("TFISH_CONFIRM_FLUSH", "Are you sure?");
define("TFISH_CACHE_WAS_FLUSHED", "Cache was flushed.");
define("TFISH_CACHE_FLUSH_FAILED", "Cache flush failed.");
define("TFISH_CACHE_FLUSH_FAILED_TO_UNLINK", "Cache flush failed, could not unlink file(s).");
define("TFISH_CACHE_FLUSH_FAILED_BAD_PATH", "Cache flush failed due to bad file path(s).");
define("TFISH_VIEW", "View");
define("TFISH_SETTINGS", "Settings");

// admin/gallery.php
define("TFISH_IMAGE_GALLERY", "Gallery");

// admin/password.php
define("TFISH_NEW_PASSWORD", "Enter new password");
define("TFISH_CONFIRM_PASSWORD", "Re-enter new password to confirm");
define("TFISH_ENTER_PASSWORD_TWICE", "You must enter the new password twice for confirmation.");
define("TFISH_PASSWORDS_DO_NOT_MATCH", "Passwords do not match, please try again.");
define("TFISH_PASSWORD_MINIMUM_LENGTH_WEAKNESS", "Password must be at least 15 characters long to "
        . "resist exhaustive searches of the keyspace.");
define("TFISH_PASSWORD_LOWER_CASE_WEAKNESS", "Password must include at least one lower case "
        . "letter.");
define("TFISH_PASSWORD_UPPER_CASE_WEAKNESS", "Password must include at least one upper case "
        . "letter.");
define("TFISH_PASSWORD_NUMBERIC_WEAKNESS", "Password must include at least one number.");
define("TFISH_PASSWORD_SYMBOLIC_WEAKNESS", "Password must include at least one non-alphanumeric "
        . "character.");
define("TFISH_PASSWORD_CHANGE_FAILED", "Sorry, password change failed.");
define("TFISH_PASSWORD_CHANGED_SUCCESSFULLY", "Password successfully changed.");

// Home page stream.
define("TFISH_LATEST_POSTS", "Latest posts");
define("TFISH_NEWS", "News"); // Alternative.

// Preferences.
define("TFISH_PREFERENCES", "Preferences");
define("TFISH_PREFERENCE", "Preference");
define("TFISH_PREFERENCE_EDIT_PREFERENCES", "Edit preferences");
define("TFISH_PREFERENCE_VALUE", "Value");
define("TFISH_PREFERENCE_SITE_NAME", "Site name");
define("TFISH_PREFERENCE_SITE_EMAIL", "Site email");
define("TFISH_PREFERENCE_CLOSE_SITE", "Close site");
define("TFISH_PREFERENCE_SERVER_TIMEZONE", "Server timezone");
define("TFISH_PREFERENCE_SITE_TIMEZONE", "Site timezone");
define("TFISH_PREFERENCE_MIN_SEARCH_LENGTH", "Min. search length");
define("TFISH_PREFERENCE_SEARCH_PAGINATION", "Search pagination");
define("TFISH_PREFERENCE_ADMIN_PAGINATION", "Admin-side pagination");
define("TFISH_PREFERENCE_GALLERY_PAGINATION", "Gallery pagination");
define("TFISH_PREFERENCE_RSS_POSTS", "RSS posts");
define("TFISH_PREFERENCE_SESSION_NAME", "Session name");
define("TFISH_PREFERENCE_SESSION_TIMEOUT", "Session timeout");
define("TFISH_PREFERENCE_SESSION_DOMAIN", "Session domain");
define("TFISH_PREFERENCE_DEFAULT_LANGUAGE", "Default language");
define("TFISH_PREFERENCE_DATE_FORMAT", 
        "<a href=\"http://php.net/manual/en/function.date.php\">Date format</a>");
define("TFISH_PREFERENCE_PAGINATION_ELEMENTS", "Max. pagination elements");
define("TFISH_PREFERENCE_USER_PAGINATION", "User-side pagination");
define("TFISH_PREFERENCE_SITE_DESCRIPTION", "Site description");
define("TFISH_PREFERENCE_SITE_AUTHOR", "Site author / publisher");
define("TFISH_PREFERENCE_SITE_COPYRIGHT", "Site copyright");
define("TFISH_PREFERENCE_ENABLE_CACHE", "Enable cache");
define("TFISH_PREFERENCE_CACHE_LIFE", "Cache life (seconds)");
define("TFISH_PREFERENCE_SESSION_LIFE", "Session life (minutes)");

// Search
define("TFISH_SEARCH", "Search");
define("TFISH_KEYWORDS", "Keywords");
define("TFISH_SEARCH_DESCRIPTION", "Search the contents of this website");
define("TFISH_SEARCH_ENTER_TERMS", "Enter search terms");
define("TFISH_SEARCH_ALL", "All (AND)");
define("TFISH_SEARCH_ANY", "Any (OR)");
define("TFISH_SEARCH_EXACT", "Exact match");
define("TFISH_SEARCH_NO_RESULTS", "No results.");
define("TFISH_SEARCH_RESULTS", "result(s)");

// RSS
define("TFISH_RSS", "RSS");

// Permalinks
define("TFISH_TYPE_PERMALINKS", "Permalink");

// Pagination controls
define("TFISH_PAGINATION_FIRST", "First");
define("TFISH_PAGINATION_LAST", "Last");

// Base intellectual property licenses.
define("TFISH_RIGHTS_COPYRIGHT", "Copyright, all rights reserved.");
define("TFISH_RIGHTS_ATTRIBUTION", "Creative Commons Attribution.");
define("TFISH_RIGHTS_ATTRIBUTION_SHARE_ALIKE", "Creative Commons Attribution-ShareAlike.");
define("TFISH_RIGHTS_ATTRIBUTION_NO_DERIVS", "Creative Commons Attribution-NoDerivs");
define("TFISH_RIGHTS_ATTRIBUTION_NON_COMMERCIAL", "Creative Commons Attribution-NonCommercial.");
define("TFISH_RIGHTS_ATTRIBUTION_NON_COMMERCIAL_SHARE_ALIKE", "Creative Commons "
        . "Attribution-NonCommercial-ShareAlike.");
define("TFISH_RIGHTS_ATTRIBUTION_NON_COMMERCIAL_NO_DERIVS", "Creative Commons "
        . "Attribution-NonCommercial-NoDerivs.");
define("TFISH_RIGHTS_GPL2", "GNU General Public License Version 2.");
define("TFISH_RIGHTS_GPL3", "GNU General Public License Version 3.");
define("TFISH_RIGHTS_PUBLIC_DOMAIN", "Public domain.");

// Confirmation messages.
define("TFISH_SUBMIT", "Submit");
define("TFISH_UPDATE", "Update");
define("TFISH_CONFIRM_DELETE", "Are you sure?");
define("TFISH_DO_YOU_WANT_TO_DELETE", "Do you want to delete");
define("TFISH_YES", "Yes");
define("TFISH_NO", "No");
define("TFISH_CANCEL", "Cancel");
define("TFISH_BACK", "Back");
define("TFISH_SUCCESS", "Success");
define("TFISH_FAILED", "Failed");
define("TFISH_OBJECT_WAS_INSERTED", "The object was successfully inserted.");
define("TFISH_OBJECT_INSERTION_FAILED", "Object insertion failed.");
define("TFISH_OBJECT_WAS_DELETED", "The object was successfully deleted.");
define("TFISH_OBJECT_DELETION_FAILED", "Object deletion failed");
define("TFISH_OBJECT_WAS_UPDATED", "The object was successfully updated.");
define("TFISH_OBJECT_UPDATE_FAILED", "Object update failed.");
define("TFISH_PREFERENCES_WERE_UPDATED", "Preferences were successfully updated.");
define("TFISH_PREFERENCES_UPDATE_FAILED", "Preference update failed.");

// ERROR MESSAGES.
define("TFISH_ERROR", "Oops...");
define("TFISH_SORRY_WE_ENCOUNTERED_AN_ERROR", "Sorry, we encountered an error.");
define("TFISH_ERROR_NO_SUCH_OBJECT", "Object does not exist.");
define("TFISH_ERROR_NO_SUCH_PROPERTY", "Trying to set value of non-existant property");
define("TFISH_ERROR_NO_RESULT", "Database query did not return a statement; query failed.");
define("TFISH_ERROR_NOT_ALPHA", "Illegal characters: Non-alpha.");
define("TFISH_ERROR_NOT_ALNUM", "Illegal characters: Non-alnum.");
define("TFISH_ERROR_NOT_ALNUMUNDER", "Illegal characters: Non-alnumunder.");
define("TFISH_ERROR_NOT_CRITERIA_OBJECT", "Not a TfCriteria object.");
define("TFISH_ERROR_NOT_DIGIT", "Illegal characters: Non-digit.");
define("TFISH_ERROR_NOT_IP", "Not an IP address.");
define("TFISH_ERROR_INSERTION_FAILED", "Insertion to the database failed.");
define("TFISH_ERROR_NOT_URL", "Not a valid URL.");
define("TFISH_ERROR_NOT_ARRAY", "Not an array.");
define("TFISH_ERROR_NOT_ARRAY_OR_EMPTY", "Not an array, or array empty.");
define("TFISH_ERROR_REQUIRED_PROPERTY_NOT_SET", "Required object property not set.");
define("TFISH_ERROR_COUNT_MISMATCH", "Count mismatch.");
define("TFISH_ERROR_NOT_CRITERIA_ITEM_OBJECT", "Not a TfCriteriaItem object.");
define("TFISH_ERROR_ILLEGAL_TYPE", "Illegal data type (not whitelisted).");
define("TFISH_ERROR_TYPE_MISMATCH", "Data type mismatch.");
define("TFISH_ERROR_ILLEGAL_VALUE", "Illegal value (not whitelisted).");
define("TFISH_ERROR_NOT_INT", "Not an integer, or integer range violation.");
define("TFISH_ERROR_NOT_BOOL", "Not a boolean.");
define("TFISH_ERROR_NOT_FLOAT", "Not a float.");
define("TFISH_ERROR_NOT_EMAIL", "Not an email.");
define("TFISH_ERROR_NOT_OBJECT", "Not an object, or illegal class type.");
define("TFISH_ERROR_ILLEGAL_MIMETYPE", "Illegal mimetype.");
define("TFISH_ERROR_UNKNOWN_MIMETYPE", "Unknown mimetype.");
define("TFISH_ERROR_NO_STATEMENT", "No statement object.");
define("TFISH_ERROR_REQUIRED_PARAMETER_NOT_SET", "Required parameter not set.");
define("TFISH_ERROR_FILE_UPLOAD_FAILED", "File upload failed.");
define("TFISH_ERROR_IMAGE_UPLOAD_FAILED", "Image file upload failed.");
define("TFISH_ERROR_MEDIA_UPLOAD_FAILED", "Media file upload failed.");
define("TFISH_ERROR_FAILED_TO_APPEND_FILE", "Failed to append to file.");
define("TFISH_ERROR_FAILED_TO_DELETE_FILE", "Failed to delete file.");
define("TFISH_ERROR_FAILED_TO_DELETE_DIRECTORY", "Failed to delete directory");
define("TFISH_ERROR_FAILED_TO_SEND_DOWNLOAD", "Failed to initiate download stream.");
define("TFISH_ERROR_BAD_PATH", "Bad file path.");
define("TFISH_ERROR_NOT_AN_UPLOADED_FILE", "Not an uploaded file, possible upload attack.");
define("TFISH_ERROR_NOT_TEMPLATE_OBJECT", "Not a template object.");
define("TFISH_ERROR_TEMPLATE_DOES_NOT_EXIST", "Template file does not exist.");
define("TFISH_CANNOT_OVERWRITE_TEMPLATE_VARIABLE", "Cannot overwrite template variable.");
define("TFISH_ERROR_PAGINATION_PARAMETER_ERROR", "Pagination control parameter error.");
define("TFISH_ERROR_NO_SUCH_CONTENT", "Sorry, this content is not available.");
define("TFISH_ERROR_BAD_DATE_DEFAULTING_TO_TODAY", "Bad date supplied, defaulting to today.");
define("TFISH_ERROR_NEED_TO_CONFIGURE_STATIC_PAGE", "Please configure this page to point to a "
        . "static content object.");
define("TFISH_ERROR_ROOT_PATH_NOT_DEFINED", "TFISH_ROOT_PATH not defined.");
define("TFISH_ERROR_CIRCULAR_PARENT_REFERENCE", "Circular reference: Content object cannot declare "
        . "self as parent.");
define("TFISH_ERROR_TRAVERSAL_OR_NULL_BYTE", "File path contains a traversal or null byte (illegal "
        . "value).");
define("TFISH_ERROR_NOT_UTF8", "Not UTF-8, illegal character set.");
define("TFISH_ERROR_DIRECT_PROPERTY_SETTING_DISALLOWED", "Object properties are not permitted to be"
        . " set directly. Use the relevant setter method.");

// Dependency errors.
define("TFISH_ERROR_NOT_VALIDATOR", "Not a validator object.");
define("TFISH_ERROR_NOT_DATABASE", "Not a database object.");
define("TFISH_ERROR_NOT_CRITERIA_FACTORY", "Not a criteria factory object.");
define("TFISH_ERROR_NOT_FILE_HANDLER", "Not a file handler object");
define("TFISH_ERROR_NOT_LOGGER", "Not a logger object.");
define("TFISH_ERROR_NOT_PREFERENCE", "Not a preference object.");
define("TFISH_ERROR_NOT_PURIFIER", "Not a HTMLPurifier object");
define("TFISH_ERROR_NOT_CACHE", "Not a cache object.");
define("TFISH_ERROR_NOT_METADATA", "Not a metadata object.");

// Token errors.
define("TFISH_INVALID_TOKEN", "Invalid token error");
define("TFISH_SORRY_INVALID_TOKEN", "Sorry, the token accompanying your request was invalid. This 
    is usually caused by your session timing out, but it can be an indication of a cross-site 
    request forgery. As a precaution, your request has not been processed. Please try again.");

// File upload error messages.
define("TFISH_ERROR_UPLOAD_ERR_INI_SIZE", "Upload failed: File exceeds maximimum permitted .ini "
        . "size.");
define("TFISH_ERROR_UPLOAD_ERR_FORM_SIZE", "Upload failed: File exceeds maximum size permitted in "
        . "form.");
define("TFISH_ERROR_UPLOAD_ERR_PARTIAL", "Upload failed: File upload incomplete (partial).");
define("TFISH_ERROR_UPLOAD_ERR_NO_FILE", "Upload failed: No file to upload.");
define("TFISH_ERROR_UPLOAD_ERR_NO_TMP_DIR", "Upload failed: No temporary upload directory.");
define("TFISH_ERROR_UPLOAD_ERR_CANT_WRITE", "Upload failed: Can't write to disk.");

// Browser compatibility error messages.
define("TFISH_BROWSER_DOES_NOT_SUPPORT_VIDEO", "Your browser does not support the video tag.");
define("TFISH_BROWSER_DOES_NOT_SUPPORT_AUDIO", "Your browser does not support the audio tag.");

/*
 * Record any new, changed or deleted language constants below by version, to aid translation.
 */

// V1.1.2
define("TFISH_ZERO_OPTION", "---");

// V1.1.3

// One time pad errors.
define("TFISH_ERROR_COULD_NOT_OPEN_PAD", "Could not open pad.");
define("TFISH_ERROR_COULD_NOT_LOCK_PAD", "Could not lock pad.");
define("TFISH_ERROR_COULD_NOT_TRUNCATE_PAD", "Could not truncate pad.");
define("TFISH_ERROR_BURN_PAD_FAILED", "Burn pad failed.");
