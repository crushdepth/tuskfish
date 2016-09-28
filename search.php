<?php

/**
* Tuskfish search script
*
* @copyright	Simon Wilkinson (Crushdepth) 2013-2016
* @license		http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL) V3 or any higher version
* @since		1.0
* @author		Simon Wilkinson (Crushdepth) <simon@isengard.biz>
* @package		core
*/

// Access trust path, DB credentials and preferences. This file must be included in *ALL* pages.
// Site preferences can be accessed via $tfish_preference->key;
require_once "mainfile.php";
require_once TFISH_PATH . "tfish_header.php";

// Validate data and separate the search terms.
$clean_op = isset($_REQUEST['op']) ? TfishFilter::trimString($_REQUEST['op']) : false;
$terms = isset($_REQUEST['search_terms']) ? TfishFilter::trimString($_REQUEST['search_terms']) : false;
$type = isset($_REQUEST['search_type']) ? TfishFilter::trimString($_REQUEST['search_type']) : false;
$start = isset($_REQUEST['start']) ? (int)$_REQUEST['start'] : 0;

// Proceed to search. Note that detailed validation of parameters is conducted by searchContent()
if ($clean_op && $terms && $type) {
	$content_handler = new TfishContentHandler();
	$search_results = $content_handler->searchContent($terms, $type, $tfish_preference->search_pagination, $start);
	if ($search_results && $search_results[0] > 0) {
		// Get a count of search results; this is used to build the pagination control.
		$results_count = (int)array_shift($search_results);
		$tfish_template->results_count = $results_count;
		$tfish_template->search_results = $search_results;
		$tfish_template->pagination = $tfish_metadata->getPaginationControl($results_count, 
				$tfish_preference->search_pagination, 'search', $start);
	} else {
		$tfish_template->search_results = false;
	}
}

// Assign template variables.
$tfish_template->page_title = TFISH_SEARCH;
$tfish_template->form = TFISH_FORM_PATH . 'search.html';
$tfish_template->tfish_main_content = $tfish_template->render('form');

/**
 * Override page template and metadata here (otherwise default site metadata will display).
 */
$tfish_metadata->title = TFISH_SEARCH;
$tfish_metadata->description = TFISH_SEARCH_DESCRIPTION;
// $tfish_metadata->author = '';
// $tfish_metadata->copyright = '';
// $tfish_metadata->generator = '';
// $tfish_metadata->seo = '';
// $tfish_metadata->robots = '';
// $tfish_metadata->template = '';

// Include page template and flush buffer
require_once TFISH_PATH . "tfish_footer.php";