<?php
/**
 * TfContentControllerFactory class file.
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
 * Handles instantiation of controllers for content objects.
 * 
 * @copyright   Simon Wilkinson 2013+ (https://tuskfish.biz)
 * @license     https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html GNU General Public License (GPL) V2
 * @author      Simon Wilkinson <simon@isengard.biz>
 * @version     Release: 1.0
 * @since       1.0
 * @package     content
 * @var         TfValidator $validator Instance of the Tuskfish data validator class.
 * @var         TfDatabase $db Instance of the Tuskfish database class.
 * @var         TfCriteriaFactory $criteriaFactory Instance of the Tuskfish criteria factory class.
 * @var         TfContentFactory $contentFactory Instance of the Tuskfish content factory class.
 * @var         TfTemplate $template Instance of the Tuskfish template object class.
 * @var         TfPreference $preference Instance of the Tuskfish site preferences class.
 * @var         TfCache $cache Instance of the Tuskfish site cache class.
 */
     
class TfContentControllerFactory
{
    
    protected $validator;
    protected $db;
    protected $criteriaFactory;
    protected $contentFactory;
    protected $template;
    protected $preference;
    protected $cache;
    
    /**
     * Constructor.
     * 
     * @param TfValidator $validator Instance of the validator class.
     * @param TfDatabase $db Instance of the database class.
     * @param TfCriteriaFactory $criteriaFactory Instance of the criteria factory class.
     * @param TfContentFactory $contentFactory Instance of the content factory class.
     * @param TfTemplate $template Instance of the template class.
     * @param TfPreference $preference Instance of the site preferences class.
     * @param TfCache $cache Instance of the cache class.
     */
    public function __construct(TfValidator $validator, TfDatabase $db,
            TfCriteriaFactory $criteriaFactory, TfContentFactory $contentFactory,
            TfTemplate $template, TfPreference $preference, TfCache $cache)
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
        
        if (is_a($contentFactory, 'TfContentFactory')) {
            $this->contentFactory = $contentFactory;
        }  else {
            trigger_error(TFISH_ERROR_NOT_CONTENT_FACTORY, E_USER_ERROR);
        }
        
        if (is_a($template, 'TfTemplate')) {
            $this->template = $template;
        }  else {
            trigger_error(TFISH_ERROR_NOT_TEMPLATE_OBJECT, E_USER_ERROR);
        }
        
        if (is_a($preference, 'TfPreference')) {
            $this->preference = $preference;
        }  else {
            trigger_error(TFISH_ERROR_NOT_PREFERENCE, E_USER_ERROR);
        }
        
        if (is_a($cache, 'TfCache')) {
            $this->cache = $cache;
        }  else {
            trigger_error(TFISH_ERROR_NOT_CACHE, E_USER_ERROR);
        }
    }
    
    /**
     * Instantiates an admin controller for content objects (TfContentObject).
     * 
     * @param string $type Type of controller. At present the only option is 'admin'.
     * 
     * @return \TfContentController|boolean Content object controller on success, false on failure.
     */
    public function getController(string $type)
    {
        $cleanType = $this->validator->trimString($type);
        
        if ($cleanType === 'admin') {
            return new TfContentController($this->validator, $this->db, $this->criteriaFactory,
                    $this->contentFactory, $this->template, $this->preference,
                    $this->cache);
        }
        
        return false;
    }
}
