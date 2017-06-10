<?php

/**
 * Handler class for audio content objects.
 *
 * @copyright	Simon Wilkinson 2013-2017 (https://tuskfish.biz)
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html GNU General Public License (GPL) V2
 * @author      Simon Wilkinson <simon@isengard.biz>
 * @version     Release: 1.0
 * @since		1.0
 * @package		core
 */
if (!defined("TFISH_ROOT_PATH"))
    die("TFISH_ERROR_ROOT_PATH_NOT_DEFINED");

class TfishAudioHandler extends TfishContentHandler
{

    function __construct()
    {
        // Must call parent constructor first.
        parent::__construct();
    }

    /**
     * Count TfishAudio objects, optionally matching conditions specified with a TfishCriteria object.
     * 
     * @param object $criteria TfishCriteria object
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
        $criteria->add(new TfishCriteriaItem('type', 'TfishAudio'));
        $count = parent::getcount($criteria);

        return $count;
    }

    /**
     * Get TfishAudio objects, optionally matching conditions specified with a TfishCriteria object.
     * 
     * Note that the object type is automatically set, so it is unnecessary to set it when calling
     * TfishAudioHandler::getObjects($criteria). However, if you want to use the generic handler
     * TfishContentHandler::getObjects($criteria) then you do need to specify the object type,
     * otherwise you will get all types of content returned. It is acceptable to use either handler,
     * although good practice to use the type-specific one when you know you want a specific kind of
     * object.
     * 
     * @param object $criteria TfishCriteria object
     * @return array $objects TfishAudio objects
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
        $criteria->add(new TfishCriteriaItem('type', 'TfishAudio'));
        $objects = parent::getObjects($criteria);

        return $objects;
    }

}
