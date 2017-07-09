<?php

/**
 * TfishBlock class file.
 * 
 * @copyright	Simon Wilkinson 2013-2017 (https://tuskfish.biz)
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html GNU General Public License (GPL) V2
 * @author      Simon Wilkinson <simon@isengard.biz>
 * @version     Release: 1.0
 * @since		1.0
 * @package		content
 */

if (!defined("TFISH_ROOT_PATH")) die("TFISH_ERROR_ROOT_PATH_NOT_DEFINED");

/**
 * Static block object class.
 * 
 * Represents a block of content, use it to create HTML blocks to insert into your site layout.
 *
 * @copyright	Simon Wilkinson 2013-2017 (https://tuskfish.biz)
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html GNU General Public License (GPL) V2
 * @author      Simon Wilkinson <simon@isengard.biz>
 * @version     Release: 1.0
 * @since		1.0
 * @package		content
 * @properties  int $id Auto-increment, set by database.
 * @properties  string $type Content object type eg. TfishArticle etc. [ALPHA]
 * @properties  string $title The name of this content.
 * @properties  string $description The full article or description of the content. [HTML]
 * @properties  array $tags Tag IDs associated with this object; not persistent (stored as taglinks in taglinks table).
 * @properties  int $online Toggle object on or offline.
 * @properties  int $submission_time Timestamp representing submission time.
 * @properties  string $handler Handler for this object (not persistent).
 * @properties  string $template The template that should be used to display this object (not persistent).
 * @properties  string $module The module that handles this content type (not persistent).
 */
class TfishBlock extends TfishContentObject
{

    /** Initialise default properties and values. */
    public function __construct()
    {
        // Must call parent constructor first.
        parent::__construct();

        // Declare the type, template, module and icon for this this class
        $this->__data['type'] = "TfishBlock";
        $this->__data['template'] = "block";
        $this->__data['module'] = "blocks";
        $this->__data['icon'] = '<span class="glyphicon glyphicon-th-large" aria-hidden="true"></span>';

        // Object definition - unset any properties unused in this subclass.
        $zeroedProperties = $this->zeroedProperties();
        
        foreach ($zeroedProperties as $property) {
            unset($this->__properties[$property], $this->__data[$property]);
        }
    }

    /**
     * Returns an array of base object properties that are not used by this subclass.
     * 
     * This list is also used in update calls to the database to ensure that unused columns are
     * cleared and reset with default values.
     * 
     * @return array Array of properties that should be zeroed (unset).
     */
    public function zeroedProperties()
    {
        return array(
            'teaser',
            'media',
            'format',
            'file_size',
            'creator',
            'image',
            'caption',
            'date',
            'parent',
            'language',
            'rights',
            'publisher',
            'counter',
            'meta_title',
            'meta_description',
            'seo'
        );
    }

}
