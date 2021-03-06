<?php

/**
 * TfTaglinkHandler class file.
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
 * Manipulates taglink objects (TfTaglink).
 *
 * @copyright   Simon Wilkinson 2013+ (https://tuskfish.biz)
 * @license     https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html GNU General Public License (GPL) V2
 * @author      Simon Wilkinson <simon@isengard.biz>
 * @version     Release: 1.0
 * @since       1.0
 * @package     content
 * @uses        TfContentTypes Whitelist of sanctioned content subclasses.
 * @var         TfValidator $validator Instance of the Tuskfish data validator class.
 * @var         TfDatabase $db Instance of the Tuskfish database class.
 * @var         TfCriteriaFactory $criteriaFactory Instance of the Tuskfish criteria factory class.
 * factory class.
 */
class TfTaglinkHandler
{
    
    use TfContentTypes;
    
    protected $validator;
    protected $db;
    protected $criteriaFactory;
    
    /**
     * Constructor.
     * 
     * @param TfValidator $validator An instance of the Tuskfish data validator class.
     * @param TfDatabase $db An instance of the database class.
     * @param TfCriteriaFactory $criteriaFactory an instance of the Tuskfish criteria factory class.
     */
    public function __construct(TfValidator $validator, TfDatabase $db,
            TfCriteriaFactory $criteriaFactory)
    {
        if (is_a($validator, 'TfValidator')) {
            $this->validator = $validator; 
        } else {
            trigger_error(TFISH_ERROR_NOT_VALIDATOR, E_USER_ERROR);
        }
        
        if (is_a($db, 'TfDatabase')) {
            $this->db = $db; 
        } else {
            trigger_error(TFISH_ERROR_NOT_DATABASE, E_USER_ERROR);
        }
        
        if (is_a($criteriaFactory, 'TfCriteriaFactory')) {
            $this->criteriaFactory = $criteriaFactory; 
        } else {
            trigger_error(TFISH_ERROR_NOT_CRITERIA_FACTORY, E_USER_ERROR);
        }     
    }

    /**
     * Delete taglinks associated with a particular content object.
     * 
     * @param object $obj A content object that makes use of the taglinks table.
     * @return bool True for success, false on failure.
     */
    public function deleteTaglinks(object $obj)
    {
        if (!$this->validator->isObject($obj)) {
            trigger_error(TFISH_ERROR_NOT_OBJECT, E_USER_ERROR);
        }
        
        if ($this->validator->isInt($obj->id, 1)) {
            $cleanContentId = (int) $obj->id;
        } else {
            trigger_error(TFISH_ERROR_NOT_INT, E_USER_ERROR);
        }
        
        $cleanType = $this->validator->trimString($obj->type);
        
        $cleanModule = $this->validator->trimString($obj->module);
        
        if (!$cleanModule || !$this->validator->isAlnumUnderscore($cleanModule)) {
            trigger_error(TFISH_ERROR_NOT_ALNUMUNDER, E_USER_ERROR);
        }
        
        return $this->_deleteTaglinks($cleanContentId, $cleanType, $cleanModule);
        
    }
    
    /** @internal */
    private function _deleteTaglinks(int $id, string $type, string $module)
    {
        $criteria = $this->criteriaFactory->getCriteria();
        
        if ($type === 'TfTag') {
            $criteria->add($this->criteriaFactory->getItem('tagId', $id));
        } else {
            $criteria->add($this->criteriaFactory->getItem('contentId', $id));
            $criteria->add($this->criteriaFactory->getItem('module', $module));
        }
        
        $result = $this->db->deleteAll('taglink', $criteria);
        
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * Insert taglinks to the taglink table.
     * 
     * Taglinks represent relationships between tags and content objects.
     * 
     * @param int $contentId ID of object.
     * @param string $type Type of object.
     * @param string $module Name of the module the object is associated with.
     * @param array $tagIds IDs of tags as integers.
     * @return bool True on success false on failure.
     */
    public function insertTaglinks(int $contentId, string $type, string $module, array $tagIds)
    {
        if ($this->validator->isInt($contentId, 1)) {
            $cleanContentId = (int) $contentId;
        } else {
            trigger_error(TFISH_ERROR_NOT_INT, E_USER_ERROR);
            exit;
        }
        
        if ($this->validator->isAlpha($type)) {
            $cleanType = $this->validator->trimString($type);
        } else {
            trigger_error(TFISH_ERROR_NOT_ALPHA, E_USER_ERROR);
            exit;
        }
        
        $cleanModule = $this->validator->trimString($module);
        
        if (!$cleanModule || !$this->validator->isAlnumUnderscore($cleanModule)) {
            trigger_error(TFISH_ERROR_NOT_ALNUMUNDER, E_USER_ERROR);
        }
        
        if (!is_array($tagIds)) {
            trigger_error(TFISH_ERROR_NOT_ARRAY_OR_EMPTY, E_USER_ERROR);
        }
        
        $cleanTags = $this->_validateTagIds($tagIds);
        
        return $this->_insertTaglinks($cleanContentId, $cleanType, $cleanModule, $cleanTags);
    }
    
    /** @internal */
    private function _validateTagIds(array $tagIds)
    {
        $cleanTags = array();
        
        foreach ($tagIds as $tagId) {
            $tag = array();
            
            if ($this->validator->isInt($tagId, 1)) {
                $tag['tagId'] = (int) $tagId;
            } else {
                trigger_error(TFISH_ERROR_NOT_INT, E_USER_ERROR);
            }
            
            $cleanTags[] = $tag;
            unset($tag);
        }
        
        return $cleanTags;
    }
    
    /** @internal */
    private function _insertTaglinks(int $contentId, string $type, string $module, array $tags)
    {
        $cleanTags = array();
        
        foreach ($tags as $tag) {
            $tag['contentId'] = $contentId;
            $tag['contentType'] = $type;
            $tag['module'] = $module;
            $cleanTags[] = $tag;
            unset($tag);
        }
        foreach ($cleanTags as $cleanTag) {
            $result = $this->db->insert('taglink', $cleanTag);
            
            if (!$result) {
                return false;
            }
        }

        return true;
    }

    /**
     * Updates taglinks for a particular content object.
     * 
     * Old taglinks are deleted, newly designated set of taglinks are inserted. Objects that have
     * had their type converted to TfTag lose all taglinks (tags are not allowed to reference
     * tags).
     * 
     * @param int $id ID of target content object.
     * @param string $type Type of content object as whitelisted in TfTaglinkHandler::getType().
     * @param string $module Name of the module the content object is associated with.
     * @param array $tagIds IDs of tags as integers.
     * @return bool True on success false on failure.
     */
    public function updateTaglinks(int $id, string $type, string $module, array $tagIds = null)
    {
        if ($this->validator->isInt($id, 1)) {
            $cleanId = (int) $id;
        } else {
            trigger_error(TFISH_ERROR_NOT_INT, E_USER_ERROR);
        }
        
        if ($this->validator->isAlpha($type)) {
            $cleanType = $this->validator->trimString($type);
        } else {
            trigger_error(TFISH_ERROR_NOT_ALPHA, E_USER_ERROR);
            exit;
        }
        
        $cleanModule = $this->validator->trimString($module);
        
        if (!$cleanModule || !$this->validator->isAlnumUnderscore($cleanModule)) {
            trigger_error(TFISH_ERROR_NOT_ALNUMUNDER, E_USER_ERROR);
        }
        
        $cleanTagId = array();
        
        if ($this->validator->isArray($tagIds)) {
            foreach ($tagIds as $tag) {
                if ($this->validator->isInt($tag, 1)) {
                    $cleanTagId[] = (int) $tag;
                } else {
                    trigger_error(TFISH_ERROR_NOT_INT, E_USER_ERROR);
                }
                unset($tag);
            }
        }

        // Delete existing taglinks.
        $result = $this->_deleteTaglinks($cleanId, $cleanType, $cleanModule);
 
        if (!$result) {
            return false;
        }
        
        unset($result);

        // Tags are not allowed to have taglinks, so do not need to proceed if this is the case.
        if ($cleanType === 'TfTag') {
            return true;
        }
        
        // Insert new taglinks.
        $cleanTags = $this->_validateTagIds($tagIds);
        
        return $this->_insertTaglinks($cleanId, $cleanType, $cleanModule, $cleanTags);
    }

}
