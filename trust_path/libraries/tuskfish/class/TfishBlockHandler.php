<?php

/**
 * Tuskfish block object handler.
 * 
 * Provides block-specific handler methods.
 *
 * @copyright	Simon Wilkinson (Crushdepth) 2016
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html GNU General Public License (GPL) V2
 * @since		1.0
 * @author		Simon Wilkinson (Crushdepth) <simon@isengard.biz>
 * @package		core
 */
if (!defined("TFISH_ROOT_PATH"))
    die("TFISH_ERROR_ROOT_PATH_NOT_DEFINED");

class TfishArticleHandler extends TfishContentHandler
{

    function __construct()
    {
        // Must call parent constructor first.
        parent::__construct();
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
     * @param TfishCriteria $criteria query composer object
     * @return array $objects TfishArticle objects
     */
    public static function getObjects($criteria = false)
    {
        if (!$criteria) {
            $criteria = new TfishCriteria();
        }

        // Unset any pre-existing object type criteria.
        $type_key = self::getTypeIndex($criteria->item);
        if (isset($type_key)) {
            $criteria->killType($type_key);
        }

        // Set new type criteria specific to this object.
        $criteria->add(new TfishCriteriaItem('type', 'TfishBlock'));
        $objects = parent::getObjects($criteria);

        return $objects;
    }

    /**
     * Count TfishArticle objects, optionally matching conditions specified with a TfishCriteria object.
     * 
     * @param TfishCriteria $criteria
     * @return int $count
     */
    public static function getCount($criteria = false)
    {
        if (!$criteria) {
            $criteria = new TfishCriteria();
        }

        // Unset any pre-existing object type criteria.
        $type_key = self::getTypeIndex($criteria->item);
        if (isset($type_key)) {
            $criteria->killType($type_key);
        }

        // Set new type criteria specific to this object.
        $criteria->add(new TfishCriteriaItem('type', 'TfishBlock'));
        $count = parent::getcount($criteria);

        return $count;
    }

}