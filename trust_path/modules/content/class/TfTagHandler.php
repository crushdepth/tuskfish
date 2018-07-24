<?php

/**
 * TfTagHandler class file.
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
 * Handler class for tag content objects.
 *
 * @copyright   Simon Wilkinson 2013+ (https://tuskfish.biz)
 * @license     https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html GNU General Public License (GPL) V2
 * @author      Simon Wilkinson <simon@isengard.biz>
 * @version     Release: 1.0
 * @since       1.0
 * @package     content
 */
class TfTagHandler extends TfContentHandler
{
    
    public function __construct(TfValidator $validator, TfDatabase $db,
            TfCriteriaFactory $criteriaFactory, TfCriteriaItemFactory $criteriaItemFactory,
            TfFileHandler $fileHandler, TfTaglinkHandler $taglinkHandler)
    {
        parent::__construct($validator, $db, $criteriaFactory, $criteriaItemFactory,
                $fileHandler, $taglinkHandler);
    }
    
    /**
     * Build a select box from an arbitrary array of tags.
     * 
     * Use this when you need to customise a tag select box. Pass in an array of the tags you want
     * to use as $tagList as key => value pairs. If you have multiple select boxes on one page then
     * you need to assign them different keys and listen for matching input parameters. If you have
     * organised tags into collections, you can run a query to retrieve that subset using the 
     * parental ID as a selection criteria.
     * 
     * @param int $selected ID of selected option.
     * @param array $tagList Array of options in tagId => title format.
     * @param string $keyName The input parameter name you want to use as key for this select box.
     * Defaults to 'tagId'.
     * @param string $zeroOption The string that will be displayed for the 'zero' or no selection
     * option.
     * @return string HTML select box.
     */
    public function getArbitraryTagSelectBox($selected = null, $tagList = array(),
            $keyName = null, $zeroOption = TFISH_SELECT_TAGS)
    {
        // Initialise variables.
        $selectBox = '';
        $cleanKeyName = '';
        $cleanTagList = array();

        // Validate input.
        // ID of a previously selected tag, if any.
        $cleanSelected = (isset($selected) && $this->validator->isInt($selected, 1))
                ? (int) $selected : null;
        
        if ($this->validator->isArray($tagList) && !empty($tagList)) {
            asort($tagList);
            
            foreach ($tagList as $key => $value) {
                $cleanKey = (int) $key;
                $cleanValue = $this->validator->escapeForXss($this->validator->trimString($value));
                $cleanTagList[$cleanKey] = $cleanValue;
                unset($key, $cleanKey, $value, $cleanValue);
            }
        }
        
        // The text to display in the zero option of the select box.
        $cleanZeroOption = $this->validator->escapeForXss($this->validator->trimString($zeroOption));
        $cleanKeyName = isset($keyName)
                ? $this->validator->escapeForXss($this->validator->trimString($keyName)) : 'tagId';

        // Build the select box.
        $cleanTagList = array(0 => $cleanZeroOption) + $cleanTagList;
        $selectBox = '<select class="form-control custom-select" name="' . $cleanKeyName . '" id="'
                . $cleanKeyName . '" onchange="this.form.submit()">';
        
        foreach ($cleanTagList as $key => $value) {
            $selectBox .= ($key === $selected) ? '<option value="' . $key . '" selected>' . $value
                    . '</option>' : '<option value="' . $key . '">' . $value . '</option>';
        }
        
        $selectBox .= '</select>';

        return $selectBox;
    }

    /**
     * Count TfTag objects, optionally matching conditions specified with a TfCriteria object.
     * 
     * @param object $criteria TfCriteria object used to build conditional database query.
     * @return int $count Number of TfTagObjects that match the criteria.
     */
    public function getCount(TfCriteria $criteria = null)
    {
        if (!isset($criteria)) {
            $criteria = $this->criteriaFactory->getCriteria();
        }

        // Unset any pre-existing object type criteria.
        $typeKey = $this->getTypeIndex($criteria->item);
        
        if (isset($typeKey)) {
            $criteria->unsetType($typeKey);
        }

        // Set new type criteria specific to this object.
        $criteria->add($this->itemFactory->getItem('type', 'TfTag'));
        $count = parent::getcount($criteria);

        return $count;
    }

    /**
     * Get TfTag objects, optionally matching conditions specified with a TfCriteria object.
     * 
     * Note that the object type is automatically set, so it is unnecessary to set it when calling
     * TfTagHandler::getObjects($criteria). However, if you want to use the generic handler
     * TfContentHandler::getObjects($criteria) then you do need to specify the object type,
     * otherwise you will get all types of content returned. It is acceptable to use either handler,
     * although good practice to use the type-specific one when you know you want a specific kind of
     * object.
     * 
     * @param object $criteria TfCriteria object used to build conditional database query.
     * @return array $objects Array of TfTag objects.
     */
    public function getObjects(TfCriteria $criteria = null)
    {
        if (!isset($criteria)) {
            $criteria = $this->criteriaFactory->getCriteria();
        }

        // Unset any pre-existing object type criteria.
        $typeKey = $this->getTypeIndex($criteria->item);
        
        if (isset($typeKey)) {
            $criteria->unsetType($typeKey);
        }

        // Set new type criteria specific to this object.
        $criteria->add($this->itemFactory->getItem('type', 'TfTag'));
        $objects = parent::getObjects($criteria);

        return $objects;
    }

    /**
     * Generates a tag select box control.
     * 
     * Use the $onlineOnly parameter to control whether you retrieve all tags, or just those marked
     * as online. Essentially this provides a way to keep your select box uncluttered; mark tags
     * that are not important enough to serve as navigation elements as 'offline'. They are still
     * available, but they won't appear in the select box list.
     * 
     * @param int $selected ID of selected option.
     * @param string $type Type of content object, eg. TfArticle.
     * @param string $zeroOption The string that will be displayed for the 'zero' or no selection
     * option.
     * @param bool $onlineOnly Get all tags or just those marked online.
     * @return bool|string False if no tags or a HTML select box if there are.
     */
    public function getTagSelectBox(int $selected = null, string $type = '',
            string $zeroOption = TFISH_SELECT_TAGS, bool $onlineOnly = true)
    {
        $selectBox = '';
        $tagList = array();

        $cleanSelected = (isset($selected) && $this->validator->isInt($selected, 1))
                ? (int) $selected : null;
        $cleanZeroOption = $this->validator->escapeForXss($this->validator->trimString($zeroOption));
        $cleanType = $this->isSanctionedType($type)
                ? $this->validator->trimString($type) : null;
        $cleanOnlineOnly = $this->validator->isBool($onlineOnly) ? (bool) $onlineOnly : true;
        $tagList = $this->getActiveTagList($cleanType, $cleanOnlineOnly);
        
        if ($tagList) {
            $selectBox = $this->getArbitraryTagSelectBox($cleanSelected, $tagList, 'tagId', $cleanZeroOption);
        } else {
            $selectBox = false;
        }
        
        return $selectBox;
    }

}