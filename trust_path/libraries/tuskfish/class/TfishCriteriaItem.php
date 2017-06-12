<?php

/**
 * TfishCriteriaItem class file.
 * 
 * @copyright	Simon Wilkinson 2013-2017 (https://tuskfish.biz)
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html GNU General Public License (GPL) V2
 * @author      Simon Wilkinson <simon@isengard.biz>
 * @version     Release: 1.0
 * @since		1.0
 * @package		core
 */

if (!defined("TFISH_ROOT_PATH")) die("TFISH_ERROR_ROOT_PATH_NOT_DEFINED");

/**
 * Clause composer class for SQLite database
 * 
 * Represents a single clause in the WHERE component of a database query. Add TfishCriteriaItem to
 * TfishCriteria to build your queries. Please see the Tuskfish Developer Guide for a full
 * explanation and examples.
 *
 * @copyright	Simon Wilkinson 2013-2017 (https://tuskfish.biz)
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html GNU General Public License (GPL) V2
 * @author      Simon Wilkinson <simon@isengard.biz>
 * @version     Release: 1.0
 * @since		1.0
 * @package		core
 * @property    string $column name of column in database table
 * @property    mixed $value value to compare
 * @property    string $operator
 */
class TfishCriteriaItem
{

    /** @var array $__data Array holding values of this object properties, accessed via magic methods. */
    protected $__data = array(
        'column' => false,
        'value' => false,
        'operator' => "=" // Default value.
    );

    /**
     * Constructor
     * 
     * @param string $column Name of column in database table. Alphanumeric and underscore
     * characters only.
     * @param mixed $value Value of the column.
     * @param string $operator See permittedOperators() for a list of acceptable operators.
     */
    function __construct($column, $value, $operator = '=')
    {
        self::__set('column', $column);
        self::__set('value', $value);
        self::__set('operator', $operator);
    }

    /**
     * Get the value of an object property.
     * 
     * Intercepts direct calls to access an object property. This method can be modified to impose
     * processing logic to the value before returning it.
     * 
     * @param string $property Name of property.
     * @return mixed|null $property Value if it is set; otherwise null.
     */
    public function __get($property)
    {
        if (isset($this->__data[$property])) {
            return $this->__data[$property];
        } else {
            return null;
        }
    }

    /**
     * Provides a whitelist of permitted operators for use in database queries.
     * 
     * @return array Array of permitted operators for use in database queries.
     */
    public function permittedOperators()
    {
        return array(
            '=', '==', '<', '<=', '>', '>=', '!=', '<>', 'IN', 'NOT IN', 'BETWEEN', 'IS', 'IS NOT',
            'LIKE');
    }

    /**
     * Set the value of an object property and will not allow non-whitelisted properties to be set.
     * 
     * Intercepts direct calls to set the value of an object property. This method can be modified
     * to impose data type restrictions and range checks before allowing the property
     * to be set. 
     * 
     * @param string $property Name of property.
     * @param mixed $value Value of property.
     */
    public function __set($property, $value)
    {
        if (isset($this->__data[$property])) {
            switch ($property) {

                case "column": // Alphanumeric and underscore characters only
                    $value = TfishFilter::trimString($value);
                    if (TfishFilter::isAlnumUnderscore($value)) {
                        $this->__data['column'] = $value;
                    } else {
                        trigger_error(TFISH_ERROR_NOT_ALNUMUNDER, E_USER_ERROR);
                    }
                    break;

                // Could be any type of value, so it is difficult to validate.
                case "value":
                    $clean_value;
                    $type = gettype($value);
                    switch ($type) {

                        // Strings are valid but should be trimmed of control characters.
                        case "string":
                            $clean_value = TfishFilter::trimString($value);
                            break;


                        // Types that can't be validated further in the current context.
                        case "array":
                        case "boolean":
                        case "integer":
                        case "double":
                            $clean_value = $value;
                            break;

                        case "object":
                        case "resource":
                        case "NULL":
                        case "uknown type":
                            trigger_error(TFISH_ERROR_ILLEGAL_TYPE, E_USER_ERROR);
                            break;
                    }
                    $this->__data['value'] = $clean_value;
                    break;

                // The default operator is "=" and this will be used unless something else is set.
                case "operator":
                    $value = TfishFilter::trimString($value);
                    if (in_array($value, self::permittedOperators())) {
                        $this->__data['operator'] = $value;
                    } else {
                        trigger_error(TFISH_ERROR_ILLEGAL_VALUE, E_USER_ERROR);
                    }
                    break;

                default:
                    trigger_error(TFISH_ERROR_ILLEGAL_TYPE, E_USER_ERROR);
                    break;
            }
            return true;
        } else {
            trigger_error(TFISH_ERROR_NO_SUCH_PROPERTY, E_USER_ERROR);
        }
    }

    /**
     * Check if an object property is set.
     * 
     * Intercepts isset() calls to correctly read object properties. Can be modified to add
     * processing logic for specific properties.
     * 
     * @param string $property Name of property.
     * @return bool True if set otherwise false.
     */
    public function __isset($property)
    {
        if (isset($this->__data[$property])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Unsets an object property.
     * 
     * Intercepts unset() calls to correctly unset object properties. Can be modified to add
     * processing logic for specific properties.
     * 
     * @param string $property Name of property.
     * @return bool True on success false on failure.
     */
    public function __unset($property)
    {
        if (isset($this->__data[$property])) {
            unset($this->__data[$property]);
            return true;
        } else {
            return false;
        }
    }

}
