<?php

/**
 * TfishCollectionHandler class file.
 * 
 * @copyright   Simon Wilkinson 2013-2017 (https://tuskfish.biz)
 * @license     https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html GNU General Public License (GPL) V2
 * @author      Simon Wilkinson <simon@isengard.biz>
 * @version     Release: 1.0
 * @since       1.0
 * @package     content
 */

if (!defined("TFISH_ROOT_PATH")) die("TFISH_ERROR_ROOT_PATH_NOT_DEFINED");

/**
 * Handler class for collection content objects.
 *
 * @copyright   Simon Wilkinson 2013-2017 (https://tuskfish.biz)
 * @license     https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html GNU General Public License (GPL) V2
 * @author      Simon Wilkinson <simon@isengard.biz>
 * @version     Release: 1.0
 * @since       1.0
 * @package     content
 */
class TfishCollectionHandler extends TfishContentHandler
{

    /**
     * Count TfishCollection objects, optionally matching conditions specified with a TfishCriteria\
     * object.
     * 
     * @param object $criteria TfishCriteria object used to build conditional database query.
     * @return int $count Number of collection objects matching the criteria.
     */
    public static function getCount($criteria = false)
    {
        if (!$criteria) {
            $criteria = new TfishCriteria();
        }
        
        if (!is_a($criteria, 'TfishCriteria')) {
            trigger_error(TFISH_ERROR_NOT_CRITERIA_OBJECT, E_USER_ERROR);
        }

        // Unset any pre-existing object type criteria.
        $type_key = self::getTypeIndex($criteria->item);
        
        if (isset($type_key)) {
            $criteria->killType($type_key);
        }

        // Set new type criteria specific to this object.
        $criteria->add(new TfishCriteriaItem('type', 'TfishCollection'));
        $count = parent::getcount($criteria);

        return $count;
    }

    /**
     * Get TfishCollection objects, optionally matching conditions specified with a TfishCriteria
     * object.
     * 
     * Note that the object type is automatically set, so it is unnecessary to set it when calling
     * TfishCollectionHandler::getObjects($criteria). However, if you want to use the generic
     * handler TfishContentHandler::getObjects($criteria) then you do need to specify the object
     * type, otherwise you will get all types of content returned. It is acceptable to use either
     * handler, although good practice to use the type-specific one when you know you want a
     * specific kind of object.
     * 
     * @param object $criteria TfishCriteria object used to build conditional database query.
     * @return array $objects TfishCollection objects.
     */
    public static function getObjects($criteria = false)
    {
        if (!$criteria) {
            $criteria = new TfishCriteria();
        }
        
        if (!is_a($criteria, 'TfishCriteria')) {
            trigger_error(TFISH_ERROR_NOT_CRITERIA_OBJECT, E_USER_ERROR);
        }

        // Unset any pre-existing object type criteria.
        $type_key = self::getTypeIndex($criteria->item);
        
        if (isset($type_key)) {
            $criteria->killType($type_key);
        }

        // Set new type criteria specific to this object.
        $criteria->add(new TfishCriteriaItem('type', 'TfishCollection'));
        $objects = parent::getObjects($criteria);

        return $objects;
    }

    /**
     * Get a select box listing a tree of parent (TfishCollection) objects.
     * 
     * @param int $selected Currently selected option.
     * @return string HTML select box.
     */
    public static function getParentSelectBox($selected = 0)
    {
        $selected = (int) $selected;
        $clean_selected = TfishFilter::isInt($selected, 1) ? $selected : 0;
        $options = array(0 => TFISH_SELECT_PARENT);
        $select_box = '';

        $criteria = new TfishCriteria();
        $criteria->add(new TfishCriteriaItem('type', 'TfishCollection'));
        $criteria->order = 'title';
        $criteria->ordertype = 'ASC';
        $options = $options + self::getList($criteria);

        $select_box = '<select id="parent" name="parent" class="form-control">';
        
        if (!empty($options)) {
            foreach ($options as $key => $value) {
                
                if ($key == $clean_selected) {
                    $select_box .= '<option value="' . $key . '" selected>' . $value . '</option>';
                } else {
                    $select_box .= '<option value="' . $key . '">' . $value . '</option>';
                }
            }
        }
        
        $select_box .= '</select>';

        return $select_box;
    }

}
