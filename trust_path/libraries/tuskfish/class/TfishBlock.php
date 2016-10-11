<?php

/**
* Tuskfish parent block object class.
* 
* All block classes are descendants of this class. 
*
* @copyright	Simon Wilkinson (Crushdepth) 2013-2016
* @license		http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL) V3 or any higher version
* @since		1.0
* @author		Simon Wilkinson (Crushdepth) <simon@isengard.biz>
* @package		core
*/
class TfishBlock  extends TfishAncestralObject
{
	function __construct()
	{
		parent::__construct();
		
		/**
		 * Whitelist of official properties and datatypes.
		 */
		$this->__properties['id'] = 'int'; // Auto-increment, set by database.
		$this->__properties['title'] = 'string'; // The headline or name of this content.
		$this->__properties['description'] = 'string'; // Content of this block (singular).
		$this->__properties['items'] = 'array'; // Content of this block (array of objects).
		$this->__properties['limit'] = 'int'; // How many items to be dispayed in the block.
		$this->__properties['type'] = 'alpha'; // Class name (alphabetical characters only).
		$this->__properties['online'] = 'int'; // Toggle object on or offline.
		$this->__properties['handler'] = 'alpha'; // Handler for this object.
		$this->__properties['template'] = 'alnum'; // The template that should be used to display this object.

		/**
		 * Set the permitted properties of this object.
		 */
		foreach ($this->__properties as $key => $value) {
			$this->__data[$key] = '';
		}
		
		/**
		 * Set default values of permitted properties.
		 */
		$this->__data['title'] = '';
		$this->__data['description'] = '';
		$this->__data['items'] = array();
		$this->__data['limit'] = 5; // Arbitrary.
		$this->__data['handler'] = get_class($this) . 'Handler';
		$this->__data['online'] = 1;
		$this->__data['template'] = 'default';
	}
	
	/**
	 * Get the value of an object property.
	 * 
	 * Intercepts direct calls to access an object property. This method can be overridden to impose
	 * processing logic to the value before returning it.
	 * 
	 * @param string $property name
	 * @return mixed|null $property value if it is set; otherwise null.
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
	 * Set the value of an object property and will not allow non-whitelisted properties to be set.
	 * 
	 * Intercepts direct calls to set the value of an object property. This method is overriden to
	 * impose data type restrictions and range checks before allowing the property to be set.
	 * 
	 * @param string $property name
	 * @param return void
	 */
	public function __set($property, $value)
	{
		// Validate $value against expected data type and business rules
		if (isset($this->__data[$property])) {
			$type = $this->__properties[$property];
			switch ($property) {
				case "id":
				case "limit":
					if (TfishFilter::isInt($value, 1)) {
						$this->__data[$property] = (int)$value;
					} else {
						trigger_error(TFISH_ERROR_NOT_INT, E_USER_ERROR);
					}
				break;
			
				case "title":
				case "description":
				case "template":
					$this->__data[$property] = TfishFilter::trimString($value);
				break;
			
				case "items":
					if (TfishFilter::isArray($value)) {
						$this->__data[$property] = $value;
					} else {
						trigger_error(TFISH_ERROR_NOT_ARRAY, E_USER_ERROR);
					}
				break;
			
				case "type":
				case "handler":
					$value = TfishFilter::trimString($value);
					if (TfishFilter::isAlpha($value)) {
						$this->__data[$property] = $value;
					} else {
						trigger_error(TFISH_ERROR_NOT_ALPHA, E_USER_ERROR);
					}
				break;
			
				case "online":
					if (TfishFilter::isInt($value, 0, 1)) {
						$this->__data[$property] = (int)$value;
					} else {
						trigger_error(TFISH_ERROR_NOT_INT, E_USER_ERROR);
					}
				break;
			}
		} else {
			trigger_error(TFISH_ERROR_NO_SUCH_PROPERTY, E_USER_ERROR);
			exit;
		}
	}
	
	/**
	 * Check if an object property is set.
	 * 
	 * Intercepts isset() calls to correctly read object properties. Can be modified to add
	 * processing logic for specific properties.
	 * 
	 * @param string $property name
	 * @return bool 
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
	 * @param string $property name
	 * @return bool true on success false on failure 
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
