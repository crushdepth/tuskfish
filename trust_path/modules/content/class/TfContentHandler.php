<?php

/**
 * TfContentHandler class file.
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
 * Base content handler class. Manipulates content objects (TfContentObject and subclasses).
 * 
 * Provides base content handler methods that are inherited or overridden by subclass-specific
 * content handlers. You can use it as a generic handler when you want to retrieve mixed content
 * types. If you want to retrieve a specific content type it would be better to use the specific
 * content handler for that type, as it may contain additional functionality for processing or
 * displaying it.
 *
 * @copyright   Simon Wilkinson 2013+ (https://tuskfish.biz)
 * @license     https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html GNU General Public License (GPL) V2
 * @author      Simon Wilkinson <simon@isengard.biz>
 * @version     Release: 1.0
 * @since       1.0
 * @package     content
 * @uses        TfContentTypes Whitelist of sanctioned TfishContentObject subclasses.
 * @var         TfValidator $validator Instance of the Tuskfish data validator class.
 * @var         TfDatabase $db Instance of the Tuskfish database class.
 * @var         TfCriteriaFactory $criteriaFactory Instance of the Tuskfish criteria factory class.
 * @var         TfFileHandler $fileHandler Instance of the Tuskfish file handler class.
 * @var         TfTaglinkHandler $taglinkHandler Instance of the Tuskfish taglink handler class.
 */
class TfContentHandler
{
    use TfContentTypes;
    
    protected $validator;
    protected $db;
    protected $criteriaFactory;
    protected $fileHandler;
    protected $taglinkHandler;
    
    /**
     * Constructor.
     * 
     * @param TfValidator $validator An instance of the Tuskfish data validator class.
     * @param TfDatabase $db An instance of the database class.
     * @param TfCriteriaFactory $criteriaFactory an instance of the Tuskfish criteria factory class.
     * @param TfFileHandler $fileHandler An instance of the Tuskfish file handler class.
     * @param TfTaglinkHandler $taglinkHandler An instance of the Tuskfish taglink handler class.
     */
    public function __construct(TfValidator $validator, TfDatabase $db,
            TfCriteriaFactory $criteriaFactory, TfFileHandler $fileHandler,
            TfTaglinkHandler $taglinkHandler)
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
        
        if (is_a($fileHandler, 'TfFileHandler')) {
            $this->fileHandler = $fileHandler; 
        } else {
            trigger_error(TFISH_ERROR_NOT_FILE_HANDLER, E_USER_ERROR);
        }
        
        if (is_a($taglinkHandler, 'TfTaglinkHandler')) {
            $this->taglinkHandler = $taglinkHandler; 
        } else {
            trigger_error(TFISH_ERROR_NOT_TAGLINK_HANDLER, E_USER_ERROR);
        }
    }
    
    /**
     * Convert a database content row to a corresponding content object.
     * 
     * Only use this function to convert single objects, as it does a separate query to look up
     * the associated taglinks. Running it through a loop will therefore consume a lot of resources.
     * To convert multiple objects, load them directly into the relevant class files, prepare a
     * buffer of tags using getTags() and loop through the objects referring to the buffer rather
     * than hitting the database every time.
     * 
     * @param array $row Array of result set from database.
     * @return object|bool Content object on success, false on failure.
     */
    public function convertRowToObject(array $row)
    {
        if (empty($row) || !$this->validator->isArray($row)) {
            return false;
        }

        // Check the content type is whitelisted.
        $typeWhitelist = $this->getTypes();
        
        if (!empty($row['type']) && array_key_exists($row['type'], $typeWhitelist)) {
            $contentObject = new $row['type']($this->validator);
        } else {
            trigger_error(TFISH_ERROR_ILLEGAL_VALUE, E_USER_ERROR);
        }
        
        // Populate the object from the $row using whitelisted properties.
        if ($contentObject) {
            $contentObject->loadPropertiesFromArray($row, false);

            // Populate the tag property.
            if (isset($contentObject->tags) && !empty($contentObject->id)) {
                $contentObject->setTags($this->loadTagsForObject($contentObject->id));
            }

            return $contentObject;
        }

        return false;
    }
    
    /**
     * Returns an array of tag IDs for a given content object.
     * 
     * @param int $id ID of content object.
     * @return array Array of tag IDs.
     */
    protected function loadTagsForObject(int $id)
    {
        $cleanId = (int) $id;      
        $tags = array();
        
        $criteria = $this->criteriaFactory->getCriteria();
        $criteria->add($this->criteriaFactory->getItem('contentId', $cleanId));
        $criteria->add($this->criteriaFactory->getItem('module', 'content'));
        $statement = $this->db->select('taglink', $criteria, array('tagId'));

        if ($statement) {
            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $tags[] = $row['tagId'];
            }
            
            return $tags;
        } else {
            trigger_error(TFISH_ERROR_NO_RESULT, E_USER_ERROR);
        }
    }
    
    /**
     * Delete a single object from the content table.
     * 
     * @param int $id ID of content object to delete.
     * @return bool True on success, false on failure.
     */
    public function delete(int $id)
    {
        $cleanId = (int) $id;
        
        if (!$this->validator->isInt($cleanId, 1)) {
            trigger_error(TFISH_ERROR_NOT_INT, E_USER_ERROR);
            return false;
        }

        // Delete files associated with the image and media properties.
        $obj = $this->getObject($cleanId);
        
        if (!is_object($obj)) {
            trigger_error(TFISH_ERROR_NOT_OBJECT, E_USER_ERROR);
            return false;
        }
        
        if (!empty($obj->image)) {
            $this->_deleteImage($obj->image);
        }

        if (!empty($obj->media)) {
            $this->_deleteMedia($obj->media);
        }

        // Delete associated taglinks. If this object is a tag, delete taglinks referring to it.
        $result = $this->taglinkHandler->deleteTaglinks($obj);
        
        if (!$result) {
            return false;
        }

        // If object is a collection delete related parent references in child objects.
        if ($obj->type === 'TfCollection') {
            $this->deleteParentalReferences($cleanId);
        }

        // Finally, delete the object.
        $result = $this->db->delete('content', $cleanId);
        
        if (!$result) {
            return false;
        }

        return true;
    }
    
    /**
     * Deletes an uploaded image file associated with a content object.
     * 
     * @param string $filename Name of file.
     * @return bool True on success, false on failure.
     */
    private function _deleteImage(string $filename)
    {
        if ($filename) {
            return $this->fileHandler->deleteFile('image/' . $filename);
        }
    }

    /**
     * Deletes an uploaded media file associated with a content object.
     * 
     * @param string $filename Name of file.
     * @return bool True on success, false on failure.
     */
    private function _deleteMedia(string $filename)
    {
        if ($filename) {
            return $this->fileHandler->deleteFile('media/' . $filename);
        }
    }
    
    /**
     * Removes references to a collection when it is deleted or changed to another type.
     * 
     * @param int $id ID of the parent collection.
     * @return boolean True on success, false on failure.
     */
    public function deleteParentalReferences(int $id)
    {
        $cleanId = $this->validator->isInt($id, 1) ? (int) $id : null;
        
        if (!$cleanId) {
            trigger_error(TFISH_ERROR_NOT_INT, E_USER_ERROR);
        }
        
        $criteria = $this->criteriaFactory->getCriteria();
        $criteria->add($this->criteriaFactory->getItem('parent', $cleanId));
        $result = $this->db->updateAll('content', array('parent' => 0), $criteria);

        if (!$result) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Inserts a content object into the database.
     * 
     * Note that content child content classes that have unset unused properties from the parent
     * should reset them to null before insertion or update. This is to guard against the case
     * where the admin reassigns the type of a content object - it makes sure that unused properties
     * are zeroed in the database. 
     * 
     * @param TfContentObject $obj A content object subclass.
     * @return bool True on success, false on failure.
     */
    public function insert(TfContentObject $obj)
    {
        if (!is_a($obj, 'TfContentObject')) {
            trigger_error(TFISH_ERROR_NOT_CONTENT_OBJECT, E_USER_ERROR);
        }
        
        // Convert object to array for insertion in database.
        $keyValues = $obj->convertObjectToArray();
        $keyValues['submissionTime'] = time(); // Automatically set submission time.
        $keyValues['lastUpdated'] = 0; // Initiate lastUpdated at 0.
        $keyValues['expiresOn'] = 0; // Initate expiresOn at 0;
        unset($keyValues['id']); // ID is auto-incremented by the database on insert operations.
        unset($keyValues['tags']);
        unset($keyValues['validator']);

        // Process image and media files before inserting the object, as properties must be determined.
        $propertyWhitelist = $obj->getPropertyWhitelist();
        $keyValues['image'] = $this->uploadImage($propertyWhitelist);
        
        $mimetypeWhitelist = $obj->getListOfPermittedUploadMimetypes();
        $mediaProperties = $this->uploadMedia($propertyWhitelist, $mimetypeWhitelist);
        $keyValues['media'] = $mediaProperties['media'];
        $keyValues['format'] = $mediaProperties['format'];
        $keyValues['fileSize'] = $mediaProperties['fileSize'];

        // Insert the object into the database.
        $result = $this->db->insert('content', $keyValues);
        
        if (!$result) {
            trigger_error(TFISH_ERROR_INSERTION_FAILED, E_USER_ERROR);
            return false;
        } else {
            $contentId = $this->db->lastInsertId();
        }
        
        unset($keyValues, $result);
        
        // Insert the tags associated with this object.
        $this->insertTagsForObject($contentId, $obj);
        
        return true;
    }
    
    /**
     * Moves an uploaded image to the /uploads/image directory and returns the filename.
     * 
     * This is a helper function for insert().
     * 
     * @param array $propertyWhitelist List of permitted object properties.
     * @return string Filename.
     */
    private function uploadImage(array $propertyWhitelist)
    {
        if (array_key_exists('image', $propertyWhitelist) && !empty($_FILES['image']['name'])) {
            $filename = $this->validator->trimString($_FILES['image']['name']);
            $cleanFilename = $this->fileHandler->uploadFile($filename, 'image');
            
            if ($cleanFilename) {
                return $cleanFilename;
            }
        }
        
        return '';
    }
    
    /**
     * Moves an uploaded media file to the /uploads/media directory and returns its properties.
     * 
     * This is a helper function for insert().
     * 
     * @param array $propertyWhitelist List of permitted properties for the content object.
     * @param array $mimetypeWhitelist List of permitted mimetypes.
     * @return array Array containing the filename, format and file size of the uploaded media file.
     */
    private function uploadMedia(array $propertyWhitelist, array $mimetypeWhitelist)
    {
        $keyValues = array('media' => '', 'format' => '', 'fileSize' => '');
        
        if (array_key_exists('media', $propertyWhitelist) && !empty($_FILES['media']['name'])) {
            $filename = $this->validator->trimString($_FILES['media']['name']);
            $cleanFilename = $this->fileHandler->uploadFile($filename, 'media');

            if ($cleanFilename) {
                $keyValues['media'] = $cleanFilename;
                $extension = pathinfo($cleanFilename, PATHINFO_EXTENSION);
                $keyValues['format'] = $mimetypeWhitelist[$extension];
                $keyValues['fileSize'] = $_FILES['media']['size'];
            }
        }
        
        return $keyValues;
    }
    
    /**
     * Insert the tags associated with a content object.
     * 
     * This is a helper function for insert().
     * 
     * Tags are stored separately in the taglinks table. Tags are assembled in one batch before
     * proceeding to insertion; so if one fails a range check all should fail. If the
     * lastInsertId could not be retrieved, then halt execution because this data
     * is necessary in order to correctly assign taglinks to content objects.
     * 
     * @return boolean
     */
    private function insertTagsForObject(int $contentId, TfContentObject $obj)
    {
        if (isset($obj->tags) and $this->validator->isArray($obj->tags)) {
            if (!$contentId) {
                trigger_error(TFISH_ERROR_NO_LAST_INSERT_ID, E_USER_ERROR);
                exit;
            }

            $result = $this->taglinkHandler->insertTaglinks($contentId, $obj->type, $obj->module,
                    $obj->tags);
            if (!$result) {
                return false;
            }
        }
    }

    /**
     * Checks if a class name is a sanctioned subclass of TfContentObject.
     * 
     * Basically this just checks if the class name is whitelisted.
     * 
     * @param string $type Type of content object.
     * @return bool True if sanctioned type otherwise false.
     */
    public function isSanctionedType(string $type)
    {
        $type = $this->validator->trimString($type);
        $sanctionedTypes = $this->getTypes();
        
        if (array_key_exists($type, $sanctionedTypes)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get a list of tags actually in use by other content objects, optionally filtered by module
     * and / or type.
     * 
     * Used primarily to build select box controls. Use $onlineOnly to select only those tags that
     * are marked as online (true), or all tags (false).
     * 
     * @param string $module Restrict to tags from a certain module.
     * @param string $type Type of content object (subclass name).
     * @param bool $onlineOnly True if marked as online, false if marked as offline.
     * @return array|bool List of tags if available, false if empty.
     */
    public function getActiveTagList(string $module = null, string $type = null, 
            bool $onlineOnly = true)
    {
        $tags = $distinctTags = array();

        $cleanOnlineOnly = $this->validator->isBool($onlineOnly) ? (bool) $onlineOnly : true;
        $tags = $this->getTagList($cleanOnlineOnly);
        
        if (empty($tags)) {
            return false;
        }

        // Restrict tag list to those actually in use.
        $cleanType = (isset($type) && $this->isSanctionedType($type))
                ? $this->validator->trimString($type) : null;

        $criteria = $this->criteriaFactory->getCriteria();
        
        // Filter tags by module.
        if (isset($module) && !empty($module)) {
            $cleanModule = $this->validator->trimString($module);
            $criteria->add($this->criteriaFactory->getItem('module', $cleanModule));
        }

        // Filter tags by type.
        if (isset($cleanType)) {
            $criteria->add($this->criteriaFactory->getItem('contentType', $cleanType));
        }

        // Put a check for online status in here.
        $statement = $this->db->selectDistinct('taglink', $criteria, array('tagId'));
        
        if ($statement) {
            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                if (isset($tags[$row['tagId']])) {
                    $distinctTags[$row['tagId']] = $tags[$row['tagId']];
                }
            }
        }

        // Sort the tags into alphabetical order.
        asort($distinctTags);

        return $distinctTags;
    }

    /**
     * Count content objects optionally matching conditions specified with a TfCriteria object.
     * 
     * @param TfCriteria $criteria Query composer object used to build conditional database query.
     * @return int $count Number of objects matching conditions.
     */
    public function getCount(TfCriteria $criteria = null)
    {
        if (isset($criteria) && !is_a($criteria, 'TfCriteria')) {
            trigger_error(TFISH_ERROR_NOT_CRITERIA_OBJECT, E_USER_ERROR);
        }
        
        if (!isset($criteria)) {
            $criteria = $this->criteriaFactory->getCriteria();
        }
        
        $count = $this->db->selectCount('content', $criteria);

        return $count;
    }
    
    /**
     * Return a list of mimetypes.
     * 
     * This list is not exhaustive, but it does cover most things that a sane person would want.
     * Feel free to add more if you wish, but do NOT use this as a whitelist of permitted mimetypes,
     * it is just a reference.
     * 
     * @return array Array of mimetypes with extension as key.
     * @copyright	The ImpressCMS Project http://www.impresscms.org/
     * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
     * @author		marcan <marcan@impresscms.org>
     */
    public function getListOfMimetypes()
    {
        return array(
            "hqx" => "application/mac-binhex40",
            "doc" => "application/msword",
            "docx" => "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
            "dot" => "application/msword",
            "bin" => "application/octet-stream",
            "lha" => "application/octet-stream",
            "lzh" => "application/octet-stream",
            "exe" => "application/octet-stream",
            "class" => "application/octet-stream",
            "so" => "application/octet-stream",
            "dll" => "application/octet-stream",
            "pdf" => "application/pdf",
            "ai" => "application/postscript",
            "eps" => "application/postscript",
            "ps" => "application/postscript",
            "smi" => "application/smil",
            "smil" => "application/smil",
            "wbxml" => "application/vnd.wap.wbxml",
            "wmlc" => "application/vnd.wap.wmlc",
            "wmlsc" => "application/vnd.wap.wmlscriptc",
            "odt" => "application/vnd.oasis.opendocument.text",
            "xla" => "application/vnd.ms-excel",
            "xls" => "application/vnd.ms-excel",
            "xlt" => "application/vnd.ms-excel",
            "ppt" => "application/vnd.ms-powerpoint",
            "csh" => "application/x-csh",
            "dcr" => "application/x-director",
            "dir" => "application/x-director",
            "dxr" => "application/x-director",
            "spl" => "application/x-futuresplash",
            "gtar" => "application/x-gtar",
            "php" => "application/x-httpd-php",
            "php3" => "application/x-httpd-php",
            "php4" => "application/x-httpd-php",
            "php5" => "application/x-httpd-php",
            "phtml" => "application/x-httpd-php",
            "js" => "application/x-javascript",
            "sh" => "application/x-sh",
            "swf" => "application/x-shockwave-flash",
            "sit" => "application/x-stuffit",
            "tar" => "application/x-tar",
            "tcl" => "application/x-tcl",
            "xhtml" => "application/xhtml+xml",
            "xht" => "application/xhtml+xml",
            "xhtml" => "application/xml",
            "ent" => "application/xml-external-parsed-entity",
            "dtd" => "application/xml-dtd",
            "mod" => "application/xml-dtd",
            "gz" => "application/x-gzip",
            "zip" => "application/zip",
            "au" => "audio/basic",
            "snd" => "audio/basic",
            "mid" => "audio/midi",
            "midi" => "audio/midi",
            "kar" => "audio/midi",
            "mp1" => "audio/mpeg",
            "mp2" => "audio/mpeg",
            "mp3" => "audio/mpeg",
            "aif" => "audio/x-aiff",
            "aiff" => "audio/x-aiff",
            "m3u" => "audio/x-mpegurl",
            "ram" => "audio/x-pn-realaudio",
            "rm" => "audio/x-pn-realaudio",
            "rpm" => "audio/x-pn-realaudio-plugin",
            "ra" => "audio/x-realaudio",
            "wav" => "audio/x-wav",
            "bmp" => "image/bmp",
            "gif" => "image/gif",
            "jpeg" => "image/jpeg",
            "jpg" => "image/jpeg",
            "jpe" => "image/jpeg",
            "png" => "image/png",
            "tiff" => "image/tiff",
            "tif" => "image/tif",
            "wbmp" => "image/vnd.wap.wbmp",
            "pnm" => "image/x-portable-anymap",
            "pbm" => "image/x-portable-bitmap",
            "pgm" => "image/x-portable-graymap",
            "ppm" => "image/x-portable-pixmap",
            "xbm" => "image/x-xbitmap",
            "xpm" => "image/x-xpixmap",
            "ics" => "text/calendar",
            "ifb" => "text/calendar",
            "css" => "text/css",
            "html" => "text/html",
            "htm" => "text/html",
            "asc" => "text/plain",
            "txt" => "text/plain",
            "rtf" => "text/rtf",
            "sgml" => "text/x-sgml",
            "sgm" => "text/x-sgml",
            "tsv" => "text/tab-seperated-values",
            "wml" => "text/vnd.wap.wml",
            "wmls" => "text/vnd.wap.wmlscript",
            "xsl" => "text/xml",
            "mpeg" => "video/mpeg",
            "mpg" => "video/mpeg",
            "mpe" => "video/mpeg",
            "mp4" => "video/mp4",
            "qt" => "video/quicktime",
            "mov" => "video/quicktime",
            "avi" => "video/x-msvideo",
        );
    }

    /**
     * Returns a list of content object titles with ID as key.
     * 
     * @param TfCriteria $criteria Query composer object used to build conditional database query.
     * @return array Array as id => title of content objects.
     */
    public function getListOfTitles(TfCriteria $criteria = null)
    {
        $contentList = array();
        $columns = array('id', 'title');

        if (isset($criteria) && !is_a($criteria, 'TfCriteria')) {
            trigger_error(TFISH_ERROR_NOT_CRITERIA_OBJECT, E_USER_ERROR);
        }
        
        if (!isset($criteria)) {
            $criteria = $this->criteriaFactory->getCriteria();
        }
        
        // Set default sorting order by submission time descending.
        if (!$criteria->order) {
            $criteria->setOrder('date');
            $criteria->setSecondaryOrder('submissionTime');
            $criteria->setSecondaryOrderType('DESC');
        }

        $statement = $this->db->select('content', $criteria, $columns);
        
        if ($statement) {
            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $contentList[$row['id']] = $row['title'];
            }
            unset($statement);
        } else {
            trigger_error(TFISH_ERROR_NO_RESULT, E_USER_ERROR);
        }

        return $contentList;
    }

    /**
     * Retrieves a single content object based on its ID.
     * 
     * @param int $id ID of content object.
     * @return TfContentObject|bool $object Content object on success, false on failure.
     */
    public function getObject(int $id)
    {
        $cleanId = (int) $id;
        $row = $object = '';
        
        if ($this->validator->isInt($cleanId, 1)) {
            $criteria = $this->criteriaFactory->getCriteria();
            $criteria->add($this->criteriaFactory->getItem('id', $cleanId));
            $statement = $this->db->select('content', $criteria);
            
            if ($statement) {
                $row = $statement->fetch(PDO::FETCH_ASSOC);
            }
            
            if ($row) {
                $object = $this->convertRowToObject($row);
                return $object;
            }
        }
        
        return false;
    }

    /**
     * Get content objects, optionally matching conditions specified with a TfCriteria object.
     * 
     * @param TfCriteria $criteria Query composer object used to build conditional database query.
     * @return array Array of content objects.
     */
    public function getObjects(TfCriteria $criteria = null)
    {
        $objects = array();
        
        if (isset($criteria) && !is_a($criteria, 'TfCriteria')) {
            trigger_error(TFISH_ERROR_NOT_CRITERIA_OBJECT, E_USER_ERROR);
        }
        
        if (!isset($criteria)) {
            $criteria = $this->criteriaFactory->getCriteria();
        }

        // Set default sorting order by submission time descending.        
        if (!$criteria->order) {
            $criteria->setOrder('date');
            $criteria->setSecondaryOrder('submissionTime');
            $criteria->setSecondaryOrderType('DESC');
        }

        $statement = $this->db->select('content', $criteria);
        
        if ($statement) {
            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $object = new $row['type']($this->validator);
                $object->loadPropertiesFromArray($row, false);
                $objects[$object->id] = $object;
                unset($object);
            }
            unset($statement);
        } else {
            trigger_error(TFISH_ERROR_NO_RESULT, E_USER_ERROR);
        }

        // Get the tags for these objects.
        $this->getTagsForObjects($objects);

        return $objects;
    }
    
    /**
     * Looks up and assigns tag IDs for an array of content objects in a query-efficient manner.
     * 
     * This is a helper function for getObjects().
     * 
     * @param array $objects Array of content objects.
     */
    private function getTagsForObjects(array $objects)
    {
        if (!$this->validator->isArray($objects)) {
            trigger_error(TFISH_ERROR_NOT_ARRAY, E_USER_ERROR);
        }
        
        if (!empty($objects)) {
            $taglinks = array();
            $objectIds = array_keys($objects);

            $criteria = $this->criteriaFactory->getCriteria();
            
            foreach ($objectIds as $id) {
                $criteria->add($this->criteriaFactory->getItem('contentId', (int) $id), "OR");
                unset($id);
            }

            $statement = $this->db->select('taglink', $criteria);

            if ($statement) {
                // Sort tag into multi-dimensional array indexed by contentId, filtering by content module.
                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    if ($row['contentId'] === 'content') {
                        $taglinks[$row['contentId']][] = $row['tagId'];
                    }
                }

                // Assign the sorted tags to correct content objects.
                foreach ($taglinks as $contentId => $tags) {
                    $objects[$contentId]->setTags($tags);
                    unset($tags);
                }
            } else {
                trigger_error(TFISH_ERROR_NO_RESULT, E_USER_ERROR);
            }
        }
    }

    /**
     * Generates an online/offline select box.
     * 
     * @param int $selected The currently option.
     * @param string $zeroOption The text to display in the zero option of the select box.
     * @return string HTML select box.
     */
    public function getOnlineSelectBox(int $selected = null, string $zeroOption = TFISH_ONLINE_STATUS)
    {
        $cleanSelected = (isset($selected) && $this->validator->isInt($selected, 0, 1)) 
                ? (int) $selected : null; // Offline (0) or online (1)
        $cleanZeroOption = $this->validator->escapeForXss($this->validator->trimString($zeroOption));
        $options = array(2 => TFISH_SELECT_STATUS, 1 => TFISH_ONLINE, 0 => TFISH_OFFLINE);
        $selectBox = '<select class="form-control custom-select" name="online" id="online" '
                . 'onchange="this.form.submit()">';
        
        if (isset($cleanSelected)) {
            foreach ($options as $key => $value) {
                $selectBox .= ($key === $cleanSelected) ? '<option value="' . $key . '" selected>' 
                        . $value . '</option>' : '<option value="' . $key . '">' . $value 
                        . '</option>';
            }
        } else { // Nothing selected
            $selectBox .= '<option value="2" selected>' . TFISH_SELECT_STATUS . '</option>';
            $selectBox .= '<option value="1">' . TFISH_ONLINE . '</option>';
            $selectBox .= '<option value="0">' . TFISH_OFFLINE . '</option>';
        }

        $selectBox .= '</select>';

        return $selectBox;
    }

    /**
     * Get an array of all tag objects in $id => $title format.
     * 
     * @param bool Get tags marked online only.
     * @return array Array of tag IDs and titles.
     */
    public function getTagList(bool $onlineOnly = true)
    {
        $tags = array();
        $statement = false;
        $cleanOnlineOnly = $this->validator->isBool($onlineOnly) ? (bool) $onlineOnly : true;
        $columns = array('id', 'title');
        $criteria = $this->criteriaFactory->getCriteria();
        $criteria->add($this->criteriaFactory->getItem('type', 'TfTag'));
        
        if ($cleanOnlineOnly) {
            $criteria->add($this->criteriaFactory->getItem('online', true));
        }

        $statement = $this->db->select('content', $criteria, $columns);
        
        if ($statement) {
            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $tags[$row['id']] = $row['title'];
            }
            unset($statement);
        } else {
            trigger_error(TFISH_ERROR_NO_RESULT, E_USER_ERROR);
        }
        
        asort($tags);

        return $tags;
    }

    /**
     * Get an array of all tag objects.
     * 
     * Use this function when you need to build a buffer of tags to reduce database queries, for
     * example when looping through a result set.
     * 
     * @return array Array of TfTag objects.
     */
    public function getTags()
    {
        $tags = array();
        $criteria = $this->criteriaFactory->getCriteria();
        $criteria->add($this->criteriaFactory->getItem('type', 'TfTag'));
        $tags = $this->getObjects($criteria);
        
        return $tags;
    }
    
    /**
     * Search the filtering criteria ($criteria->items) to see if object type has been set and
     * return the key.
     * 
     * @param array $criteriaItems Array of TfCriteriaItem objects.
     * @return int|null Key of relevant TfCriteriaItem or null.
     */
    protected function getTypeIndex(array $criteriaItems)
    {
        if (!$this->validator->isArray($criteriaItems)) {
            trigger_error(TFISH_ERROR_NOT_ARRAY, E_USER_NOTICE);
        }
        
        foreach ($criteriaItems as $key => $item) {
            if ($item->column === 'type') {
                return $key;
            }
        }
        
        return null;
    }

    /**
     * Get a content type select box.
     * 
     * @param string $selected Currently selected option.
     * @param string $zeroOption The default text to show at top of select box.
     * @return string HTML select box.
     */
    public function getTypeSelectBox(string $selected = '', string $zeroOption = null)
    {
        if (isset($zeroOption)) {
            $cleanZeroOption = $this->validator->escapeForXss($this->validator->trimString($zeroOption));
        } else {
            $cleanZeroOption = TFISH_TYPE;
        }
        
        $cleanSelected = '';
        $typeList = $this->getTypes();

        if ($selected && $this->validator->isAlnumUnderscore($selected)) {
            if (array_key_exists($selected, $typeList)) {
                $cleanSelected = $this->validator->trimString($selected);
            }
        }

        $options = array(0 => TFISH_SELECT_TYPE) + $typeList;
        $selectBox = '<select class="form-control custom-select" name="type" id="type" '
                . 'onchange="this.form.submit()">';
        
        foreach ($options as $key => $value) {
            $selectBox .= ($key === $cleanSelected) ? '<option value="' . $this->validator->escapeForXss($key)
                    . '" selected>' . $this->validator->escapeForXss($value) . '</option>' : '<option value="'
                . $this->validator->escapeForXss($key) . '">' . $this->validator->escapeForXss($value) . '</option>';
        }
        
        $selectBox .= '</select>';

        return $selectBox;
    }

    /**
     * Converts an array of tagIds into an array of tag links with an arbitrary local target file.
     * 
     * Note that the filename may only consist of alphanumeric characters and underscores. Do not
     * include the file extension (eg. use 'article' instead of 'article.php'. The base URL of the
     * site will be prepended and .php plus the tagId will be appended.
     * 
     * @param array $tags Array of tag IDs.
     * @param string $targetFilename Name of file for tag links to point at.
     * @return array Array of HTML tag links.
     */
    public function makeTagLinks(array $tags, string $targetFilename = '')
    {
        if (!$this->validator->isArray($tags)) {
            trigger_error(TFISH_ERROR_NOT_ARRAY, E_USER_ERROR);
        }
        
        if (empty($targetFilename)) {
            $cleanFilename = TFISH_URL . '?tagId=';
        } else {
            if (!$this->validator->isAlnumUnderscore($targetFilename)) {
                trigger_error(TFISH_ERROR_NOT_ALNUMUNDER, E_USER_ERROR);
            } else {
                $targetFilename = $this->validator->trimString($targetFilename);
                $cleanFilename = TFISH_URL . $this->validator->escapeForXss($targetFilename)
                        . '.php?tagId=';
            }
        }

        $tagList = $this->getTagList(false);
        $tagLinks = array();
        
        foreach ($tags as $tag) {
            if ($this->validator->isInt($tag, 1) && array_key_exists($tag, $tagList)) {
                $tagLinks[$tag] = '<a href="' . $this->validator->escapeForXss($cleanFilename . $tag) . '">'
                        . $this->validator->escapeForXss($tagList[$tag]) . '</a>';
            }
            
            unset($tag);
        }

        return $tagLinks;
    }
    
    /**
     * Initiate streaming of a downloadable media file associated with a content object.
     * 
     * DOES NOT WORK WITH COMPRESSION ENABLED IN OUTPUT BUFFER. This method acts as an intermediary
     * to provide access to uploaded file resources that reside outside of the web root, while
     * concealing the real file path and name. Use this method to provide safe user access to
     * uploaded files. If anything nasty gets uploaded nobody will be able to execute it directly
     * through the browser.
     * 
     * @param int $id ID of the associated content object.
     * @param string $filename An alternative name (rename) for the file you wish to transfer,
     * excluding extension.
     * @return bool True on success, false on failure. 
     */
    public function streamDownloadToBrowser(int $id, string $filename = '')
    {
        $cleanId = $this->validator->isInt($id, 1) ? (int) $id : false;
        $cleanFilename = !empty($filename) ? $this->validator->trimString($filename) : '';
        
        if ($cleanId) {
            $result = $this->_streamDownloadToBrowser($cleanId, $cleanFilename);
            if ($result === false) {
                return false;
            }
            return true;
        } else {
            trigger_error(TFISH_ERROR_NOT_INT, E_USER_NOTICE);
        }
    }

    /** @internal */
    private function _streamDownloadToBrowser(int $id, string $filename)
    {
        $criteria = $this->criteriaFactory->getCriteria();
        $criteria->add($this->criteriaFactory->getItem('id', $id));
        $statement = $this->db->select('content', $criteria);
        
        if (!$statement) {
            trigger_error(TFISH_ERROR_NO_STATEMENT, E_USER_NOTICE);
            return false;
        }
        
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        $content = $this->convertRowToObject($row);
        
        if ($content && $content->online) {
            $media = isset($content->media) ? $content->media : false;
            
            if ($media && is_readable(TFISH_MEDIA_PATH . $content->media)) {
                ob_start();
                $filepath = TFISH_MEDIA_PATH . $content->media;
                $filename = empty($filename) ? pathinfo($filepath, PATHINFO_FILENAME) : $filename;
                $fileExtension = pathinfo($filepath, PATHINFO_EXTENSION);
                $fileSize = filesize(TFISH_MEDIA_PATH . $content->media);
                $mimetypeList = $this->getListOfMimetypes();
                $mimetype = $mimetypeList[$fileExtension];

                // Must call session_write_close() first otherwise the script gets locked.
                session_write_close();
                
                // Output the header.
                $this->_outputHeader($filename, $fileExtension, $mimetype, $fileSize, $filepath);
                
            } else {
                return false;
            }
        } else {
            trigger_error(TFISH_ERROR_NO_SUCH_OBJECT, E_USER_WARNING);
            return false;
        }
    }
    
    /** @internal */
    private function _outputHeader($filename, $fileExtension, $mimetype, $fileSize, $filepath)
    {
        // Prevent caching
        header("Pragma: public");
        header("Expires: -1");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

        // Set file-specific headers.
        header('Content-Disposition: attachment; filename="' . $filename . '.'
                . $fileExtension . '"');
        header("Content-Type: " . $mimetype);
        header("Content-Length: " . $fileSize);
        ob_clean();
        flush();
        readfile($filepath);
    }

    /**
     * Toggle the online status of a content object.
     * 
     * @param int $id ID of content object.
     * @return boolean True on success, false on failure.
     */
    public function toggleOnlineStatus(int $id)
    {
        $cleanId = (int) $id;
        return $this->db->toggleBoolean($cleanId, 'content', 'online');
    }

    /**
     * Updates a content object in the database.
     * 
     * @param TfContentObject $obj A content object subclass.
     * @return bool True on success, false on failure.
     */
    public function update(TfContentObject $obj)
    {
        if (!is_a($obj, 'TfContentObject')) {
            trigger_error(TFISH_ERROR_NOT_CONTENT_OBJECT, E_USER_ERROR);
        }
        
        $cleanId = $this->validator->isInt($obj->id, 1) ? (int) $obj->id : 0;

        $obj->updateLastUpdated();
        $keyValues = $obj->convertObjectToArray();
        unset($keyValues['submissionTime']); // Submission time should not be overwritten.
        $zeroedProperties = $obj->getListOfZeroedProperties();

        foreach ($zeroedProperties as $property) {
            $keyValues[$property] = null;
        }

        $propertyWhitelist = $obj->getPropertyWhitelist();

        // Unset properties that are not resident in the content table.
        unset($keyValues['tags']);
        unset($keyValues['validator']);

        // Load the saved object from the database. This will be used to make comparisons with the
        // current object state and facilitate clean up of redundant tags, parent references, and
        // image/media files.
        $savedObject = $this->getObject($cleanId);
        
        // Update image / media files for existing objects.
        if (!empty($savedObject)) {
            $keyValues = $this->updateImage($propertyWhitelist, $keyValues, $savedObject);
            $keyValues = $this->updateMedia($propertyWhitelist, $keyValues, $savedObject);
        }

        // Update taglinks (but not for tag objects unless their type has changed).
        if ($obj->type !== 'TfTag' && $savedObject->type !== 'TfTag') {
            $result = $this->taglinkHandler->updateTaglinks($cleanId, $obj->type, $obj->module,
                $obj->tags);
        } else {
            $result = true;
        }
        
        if (!$result) {
            trigger_error(TFISH_ERROR_TAGLINK_UPDATE_FAILED, E_USER_NOTICE);
            return false;
        }
        
        // Check if this object used to be a collection and clean up parental references.
        $this->checkExCollection($keyValues, $savedObject);

        // Update the content object.
        $result = $this->db->update('content', $cleanId, $keyValues);
        
        if (!$result) {
            trigger_error(TFISH_ERROR_INSERTION_FAILED, E_USER_ERROR);
        }
        
        unset($result);

        return true;
    }
    
    /**
     * Update the image property for an existing content object.
     * 
     * This is a helper method for update().
     * 
     * @param array $propertyWhitelist Whitelist of permitted object properties.
     * @param array $keyValues Updated values of object properties from form data.
     * @param TfContentObject $savedObject The existing (not updated) object as presently saved in
     * the database.
     * @return array $keyValues Object properties with updated image property.
     */
    private function updateImage(array $propertyWhitelist, array $keyValues,
            TfContentObject $savedObject)
    {
        // 1. Check if there is an existing image file associated with this content object.
        $existingImage = $this->checkImage($savedObject);

        // 2. Is this content type allowed to have an image property?
        if (!array_key_exists('image', $propertyWhitelist)) {
            $keyValues['image'] = '';
            if ($existingImage) {
                $this->_deleteImage($existingImage);
                $existingImage = '';
            }
        }

        // 3. Has an existing image file been flagged for deletion?
        if ($existingImage) {
            if (isset($_POST['deleteImage']) && !empty($_POST['deleteImage'])) {
                $keyValues['image'] = '';
                $this->_deleteImage($existingImage);
                $existingImage = '';
            }
        }

        // 4. Check if a new image file has been uploaded by looking in $_FILES.
        if (array_key_exists('image', $propertyWhitelist)) {

            if (isset($_FILES['image']['name']) && !empty($_FILES['image']['name'])) {
                $filename = $this->validator->trimString($_FILES['image']['name']);
                $cleanFilename = $this->fileHandler->uploadFile($filename, 'image');

                if ($cleanFilename) {
                    $keyValues['image'] = $cleanFilename;

                    // Delete old image file, if any.
                    if ($existingImage) {
                        $this->_deleteImage($existingImage);
                    }
                } else {
                    $keyValues['image'] = '';
                }

            } else { // No new image, use the existing file name.
                $keyValues['image'] = $existingImage;
            }
        }
        
        // If the updated object has no image attached, or has been instructed to delete
        // attached image, delete any old image files.
        if ($existingImage &&
                ((!isset($keyValues['image']) || empty($keyValues['image']))
                || (isset($_POST['deleteImage']) && !empty($_POST['deleteImage'])))) {
            $this->_deleteImage($existingImage);
        }
        
        return $keyValues;
    }
    
    /**
     * Update the media property for an existing content object.
     * 
     * This is a helper method for update().
     * 
     * @param array $propertyWhitelist
     * @param array $keyValues
     * @param TfContentObject $savedObject
     * @return array $keyValues Object properties with updated media/format/file size properties.
     */
    private function updateMedia(array $propertyWhitelist, array $keyValues,
            TfContentObject $savedObject)
    {
        // 1. Check if there is an existing media file associated with this content object.
        $existingMedia = $this->checkMedia($savedObject);

        // 2. Is this content type allowed to have a media property?
        if (!array_key_exists('media', $propertyWhitelist)) {
            $keyValues['media'] = '';
            $keyValues['format'] = '';
            $keyValues['fileSize'] = '';
            if ($existingMedia) {
                $this->_deleteMedia($existingMedia);
                $existingMedia = '';
            }
        }

        // 3. Has existing media been flagged for deletion?
        if ($existingMedia) {
            if (isset($_POST['deleteMedia']) && !empty($_POST['deleteMedia'])) {
                $keyValues['media'] = '';
                $keyValues['format'] = '';
                $keyValues['fileSize'] = '';
                $this->_deleteMedia($existingMedia);
                $existingMedia = '';
            }
        }

        // 4. Process media file.
        if (array_key_exists('media', $propertyWhitelist)) {
            $cleanFilename = '';

            // Get a whitelist of permitted mimetypes.
            $mimetypeWhitelist = $savedObject->getListOfPermittedUploadMimetypes();

            // Get name of newly uploaded file (overwrites old one).
            if (isset($_FILES['media']['name']) && !empty($_FILES['media']['name'])) {
                $filename = $this->validator->trimString($_FILES['media']['name']);
                $cleanFilename = $this->fileHandler->uploadFile($filename, 'media'); 
            } else {
                $cleanFilename = $existingMedia;
            }

            if ($cleanFilename) {
                if (isset($_FILES['media']['name']) && !empty($_FILES['media']['name'])) {
                    $extension = mb_strtolower(pathinfo($cleanFilename, PATHINFO_EXTENSION), 'UTF-8');

                    // Set values of new media file.
                    $keyValues['media'] = $cleanFilename;
                    $keyValues['format'] = $mimetypeWhitelist[$extension];
                    $keyValues['fileSize'] = $_FILES['media']['size'];

                    // Delete any old media file.
                    if ($existingMedia) {
                        $this->_deleteMedia($existingMedia);
                        $existingMedia = '';
                    }
                // No new media, use the existing file name.
                } else {
                    if ($existingMedia) {
                        $keyValues['media'] = $existingMedia;
                    }
                }           
            } else {
                $keyValues['media'] = '';
                $keyValues['format'] = '';
                $keyValues['fileSize'] = '';

                // Delete any old media file.
                if ($existingMedia) {
                    $this->_deleteMedia($existingMedia);
                    $existingMedia = '';
                }
            }
        }
        
        return $keyValues;
    }
    
    /**
     * Check if the object used to be a TfCollection and delete parental references if necessary.
     * 
     * When updating an object, this method is used to check if it used to be a collection. If so,
     * other content objects referring to it as parent will need to be updated. Note that you must
     * pass in the SAVED copy of the object from the database, rather than the 'current' version, 
     * as the purpose of the method is to determine if the object *used to be* a collection.
     * 
     * @param array $keyValues An array of the updated content object data as key value pairs.
     * @param TfContentObject $obj The old version of the object as currently stored in the database.
     */
    private function checkExCollection(array $keyValues, TfContentObject $obj)
    {
        if ($obj->type === 'TfCollection' && $keyValues['type'] !== 'TfCollection') {
           $result = $this->deleteParentalReferences((int) $keyValues['id']);
                
            if (!$result) {
                trigger_error(TFISH_ERROR_PARENT_UPDATE_FAILED, E_USER_NOTICE);
            }
        }
    }

    /**
     * Check if an existing object has an associated image file upload.
     * 
     * @param TfContentObject $obj The content object to be tested.
     * @return string Filename of associated image property.
     */
    private function checkImage(TfContentObject $obj)
    {        
        if (!empty($obj->image)) {
            return $obj->image;
        }

        return '';
    }

    /**
     * Check if an existing object has an associated media file upload.
     * 
     * @param TfContentObject $obj The content object to be tested.
     * @return string Filename of associated media property.
     */
    private function checkMedia(TfContentObject $obj)
    {
        if (!empty($obj->media)) {
            return $obj->media;
        }
        
        return '';
    }

    /**
     * Increment a given content object counter field by one.
     * 
     * @param int $id ID of content object.
     */
    public function updateCounter(int $id)
    {
        $cleanId = (int) $id;
        return $this->db->updateCounter($cleanId, 'content', 'counter');
    }

}
