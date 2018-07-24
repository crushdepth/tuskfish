<?php

/**
 * Admin image manager script.
 * 
 * Display and filter image content, including offline content and images associated with non-image
 * content objects. Use it to locate and re-use image assets.
 *
 * @copyright   Simon Wilkinson 2013+ (https://tuskfish.biz)
 * @license     https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html GNU General Public License (GPL) V2
 * @author      Simon Wilkinson <simon@isengard.biz>
 * @since       1.0
 * @package     admin
 */
// Enable strict type declaration.
declare(strict_types=1);

// 1. Access trust path, DB credentials and preferences. This file must be included in *ALL* pages.
require_once "mainfile.php";

// 2. Main Tuskfish header. This file bootstraps Tuskfish.
require_once TFISH_PATH . "tfHeader.php";

// 3. Content header sets module-specific paths and makes TfContentHandlerFactory available.
require_once TFISH_MODULE_PATH . "content/tf_content_header.php";

// Specify theme, otherwise 'default' will be used.
$tfTemplate->setTheme('default');

// Configure page.
$tfTemplate->pageTitle = TFISH_IMAGE_GALLERY;
$contentHandler = $contentHandlerFactory->getHandler('content');
$index_template = 'gallery';
$targetFileName = 'gallery';
$tfTemplate->targetFileName = $targetFileName;

// Validate input parameters.
$cleanId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$clean_start = isset($_GET['start']) ? (int) $_GET['start'] : 0;
$clean_tag = isset($_GET['tagId']) ? (int) $_GET['tagId'] : 0;
$cleanType = isset($_GET['type']) && !empty($_GET['type']) 
        ? $tfValidator->trimString($_GET['type']) : '';

// Select content objects where the image field is not null or empty.
$criteria = $tfCriteriaFactory->getCriteria();
$criteria->add(new TfCriteriaItem($tfValidator, 'image', '', '<>'));
$criteria->add(new TfCriteriaItem($tfValidator, 'online', 1));

// Optional selection criteria.
if ($clean_tag)
    $criteria->setTag(array($clean_tag));

if ($cleanType) {
    if (array_key_exists($cleanType, $contentHandler->getTypes())) {
        $criteria->add(new TfCriteriaItem($tfValidator, 'type', $cleanType));
    } else {
        trigger_error(TFISH_ERROR_ILLEGAL_VALUE, E_USER_ERROR);
    }
}

// Prepare pagination control.
$tf_pagination = new TfPaginationControl($tfValidator, $tfPreference);          
$tf_pagination->setUrl($targetFileName);
$tf_pagination->setCount($contentHandler->getCount($criteria));
$tf_pagination->setLimit($tfPreference->galleryPagination);
$tf_pagination->setStart($clean_start);
$tf_pagination->setTag($clean_tag);

if (isset($cleanType) && !empty($cleanType)) {
    $tf_pagination->setExtraParams(array(['type'] => $cleanType));
}

$tfTemplate->pagination = $tf_pagination->getPaginationControl();

// Set offset and limit.
if ($clean_start) $criteria->setOffset($clean_start);
$criteria->setLimit($tfPreference->galleryPagination);

// Prepare select filters.
$tag_handler = $contentHandlerFactory->getHandler('tag');
$tagSelectBox = $tag_handler->getTagSelectBox($clean_tag);
$tfTemplate->selectAction = 'gallery.php';
$tfTemplate->tagSelect = $tagSelectBox;
$tfTemplate->selectFiltersForm = $tfTemplate->render('galleryFilters');

// Retrieve content objects and assign to template.
$contentObjects = $contentHandler->getObjects($criteria);
$tfTemplate->contentObjects = $contentObjects;
$tfTemplate->tfMainContent = $tfTemplate->render($index_template);

/**
 * Override page metadata here (otherwise default site metadata will display).
 */
// $tfMetadata->setTitle('');
// $tfMetadata->setDescription('');
// $tfMetadata->setAuthor('');
// $tfMetadata->setCopyright('');
// $tfMetadata->setGenerator('');
// $tfMetadata->setSeo('');
// $tfMetadata->setRobots('');

// Include page template and flush buffer
require_once TFISH_PATH . "tfFooter.php";
