<?php
/**
 * TfishBlockHandler class file.
 * 
 * @copyright   Simon Wilkinson 2013+ (https://tuskfish.biz)
 * @license     https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html GNU General Public License (GPL) V2
 * @author      Simon Wilkinson <simon@isengard.biz>
 * @version     Release: 1.0
 * @since       1.0
 * @package     content
 */

// Enable strict type declaration.
declare(strict_types=1);

if (!defined("TFISH_ROOT_PATH")) die("TFISH_ERROR_ROOT_PATH_NOT_DEFINED");

/**
 * Base handler class for block objects.
 *
 * @copyright   Simon Wilkinson 2013+ (https://tuskfish.biz)
 * @license     https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html GNU General Public License (GPL) V2
 * @author      Simon Wilkinson <simon@isengard.biz>
 * @version     Release: 1.0
 * @since       1.0
 * @package     content
 */
class TfishBlockHandler extends TfishContentHandler
{
    
    public function __construct(TfishValidator $validator, TfishDatabase $db,
            TfishCriteriaFactory $criteria_factory, TfishCriteriaItemFactory $criteria_item_factory,
            TfishFileHandler $file_handler, TfishTaglinkHandler $taglink_handler)
    {
        parent::__construct($validator, $db, $criteria_factory, $criteria_item_factory,
                $file_handler, $taglink_handler);
    }
    
    /**
     * Count TfishBlock objects, optionally matching conditions specified in a TfishCriteria object.
     * 
     * @param object $criteria TfishCriteria object used to build conditional database query.
     * @return int $count Count of TfishBlock objects matching conditions.
     */
    public function getCount(TfishCriteria $criteria = null)
    {
        if (!isset($criteria)) {
            $criteria = $this->criteria_factory->getCriteria();
        }

        // Unset any pre-existing object type criteria.
        $type_key = $this->getTypeIndex($criteria->item);
        
        if (isset($type_key)) {
            $criteria->unsetType($type_key);
        }

        // Set new type criteria specific to this object.
        $criteria->add($this->item_factory->getItem('type', 'TfishBlock'));
        $count = parent::getcount($criteria);

        return $count;
    }
    
    /**
     * Get TfishBlock objects, optionally matching conditions specified with a TfishCriteria object.
     * 
     * Note that the article type is automatically set, so when calling
     * TfishBlockHandler::getObjects($criteria) it is unnecessary to set the object type.
     * However, if you want to use TfishContentHandler::getObjects($criteria) then you do need to
     * specify the object type, otherwise you will get all types of content returned. it is
     * acceptable to use either handler, although probably good practice to use the object-
     * specific one when you know you want a specific kind of object.
     * 
     * @param object $criteria TfishCriteria object used to build conditional database query.
     * @return array $objects content objects.
     */
    public function getObjects(TfishCriteria $criteria = null)
    {
        if (!isset($criteria)) {
            $criteria = $this->criteria_factory->getCriteria();
        }

        // Unset any pre-existing object type criteria.
        $type_key = $this->getTypeIndex($criteria->item);
        
        if (isset($type_key)) {
            $criteria->unsetType($type_key);
        }

        // Set new type criteria specific to this object.
        $criteria->add($this->item_factory->getItem('type', 'TfishBlock'));
        $objects = parent::getObjects($criteria);

        return $objects;
    }

}
