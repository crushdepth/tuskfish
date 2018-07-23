<?php

/**
 * TfishContentHandlerFactory class file.
 * 
 * Instantiates TfishContentHandler objects and handles dependency injection.
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

class TfishContentHandlerFactory
{
    protected $validator;
    protected $db;
    protected $criteria_factory;
    protected $file_handler;
    protected $taglink_handler;
    
    public function __construct(TfishValidator $validator, TfishDatabase $db,
            TfishCriteriaFactory $criteria_factory, TfishFileHandler $file_handler)
    {
        $this->validator = $validator;
        $this->db = $db;
        $this->criteria_factory = $criteria_factory;
        $this->file_handler = $file_handler;
        $this->taglink_handler = new TfishTaglinkHandler($validator, $db, $criteria_factory);
    }
    
    public function getHandler(string $type)
    {        
        $clean_type = $this->validator->trimString($type);
        
        if ($clean_type === 'content') {
            return new TfishContentHandler($this->validator, $this->db, $this->criteria_factory,
                    $this->file_handler, $this->taglink_handler);
        }
        
        if ($clean_type === 'collection') {
            return new TfishCollectionHandler($this->validator, $this->db, $this->criteria_factory,
                    $this->file_handler, $this->taglink_handler);
        }
        
        if ($clean_type === 'tag') {
            return new TfishTagHandler($this->validator, $this->db, $this->criteria_factory,
                    $this->file_handler, $this->taglink_handler);
        }
        
        trigger_error(TFISH_ERROR_NO_SUCH_HANDLER, E_USER_ERROR);
    }
}