<?php

/**
 * TfContentObject class file.
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
 * Base class for content objects. Represents a single content object.
 *
 * There is only one 'archtype' of content object in Tuskfish; it uses a subset of standard
 * Dublin Core metadata fields plus a few more that are common to most content objects. Why? If you
 * look at most common content types - articles, photos, downloads etc. - you will see that for the
 * most part they all use the same fields (title, teaser, description, etc).
 * 
 * By using a single table for content objects with common field names our queries become very
 * simple and much redundancy is avoided. Content subclasses that don't need particular properties
 * unset() them in their constructor.
 * 
 * @copyright   Simon Wilkinson 2013+ (https://tuskfish.biz)
 * @license     https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html GNU General Public License (GPL) V2
 * @author      Simon Wilkinson <simon@isengard.biz>
 * @version     Release: 1.0
 * @since       1.0
 * @package     content
 * @uses        trait TfLanguage to obtain a list of available translations.
 * @uses        trait TfMagicMethods Prevents direct setting of properties / unlisted properties.
 * @uses        trait TfMimetypes Access a list of known / acceptable file mimetypes.
 * @uses        trait TfRights A list of known / acceptable IP licenses.
 * @properties  TfValidator $validator Instance of the Tuskfish data validator class.
 * @properties  string $type Content object type eg. TfArticle etc. [ALPHA]
 * @properties  string $title The name of this content.
 * @properties  string $teaser A short (one paragraph) summary or abstract for this content. [HTML]
 * @properties  string $description The full article or description of the content. [HTML]
 * @properties  string $media An associated download/audio/video file. [FILEPATH OR URL]
 * @properties  string $format Mimetype
 * @properties  string $fileSize Specify in bytes.
 * @properties  string $creator Author.
 * @properties  string image An associated image file, eg. a screenshot a good way to handle it. [FILEPATH OR URL]
 * @properties  string $caption Caption of the image file.
 * @properties  string $date Date of publication expressed as a string.
 * @properties  int $parent A source work or collection of which this content is part.
 * @properties  string $language Future proofing.
 * @properties  int $rights Intellectual property rights scheme or license under which the work is distributed.
 * @properties  string $publisher The entity responsible for distributing this work.
 * @properties  array $tags Tag IDs associated with this object; not persistent (stored as taglinks in taglinks table).
 */
class TfContentObject extends TfDataObject
{
    
    use TfLanguage;
    use TfMagicMethods;
    use TfMimetypes;
    use TfRights;

    protected $validator;
    protected $type = '';
    protected $title = '';
    protected $teaser = '';
    protected $description = '';
    protected $media = '';
    protected $format = '';
    protected $fileSize = '';
    protected $creator = '';
    protected $image = '';
    protected $caption = '';
    protected $date = '';
    protected $parent = '';
    protected $language = '';
    protected $rights = '';
    protected $publisher = '';
    protected $tags = '';
    
    /**
     * Constructor.
     * 
     * @param TfValidator $validator An instance of the Tuskfish data validator class.
     */
    function __construct(TfValidator $validator)
    {
        if (is_a($validator, 'TfValidator')) {
            $this->validator = $validator;
        } else {
            trigger_error(TFISH_ERROR_NOT_VALIDATOR, E_USER_ERROR);
        }
        
        $this->setId(0);
        $this->setType(get_class($this));
        $this->setHandler($this->type . 'Handler');
        $this->setRights(1);
        $this->setOnline(1);
        $this->setCounter(0);
        $this->setTags(array());
    }
    
    /**
     * Converts a content object to an array suitable for insert/update calls to the database.
     * 
     * Note that the returned array observes the PARENT object's getPropertyWhitelist() as a 
     * restriction on the setting of keys. This whitelist explicitly excludes the handler, 
     * template and module properties as these are part of the class definition and are not stored
     * in the database. Calling the parent's property whitelist ensures that properties that are
     * unset by child classes are zeroed (this is important when an object is changed to a
     * different subclass, as the properties used may differ).
     * 
     * @return array Array of object property/values.
     */
    public function convertObjectToArray()
    {        
        $keyValues = array();
        
        foreach ($this as $key => $value) {
            $keyValues[$key] = $value;
        }
        
        // Unset non-persistanet properties that are not stored in the content table.
        unset(
            $keyValues['tags'],
            $keyValues['icon'],
            $keyValues['handler'],
            $keyValues['module'],
            $keyValues['template']
            );
        
        return $keyValues;
    }

    /**
     * Resizes and caches an associated image and returns a URL to the cached copy.
     * 
     * Allows arbitrary sized thumbnails to be produced from the object's image property. These are
     * saved in the cache for future lookups. Image proportions are always preserved, so if both
     * width and height are specified, the larger dimension will take precedence for resizing and
     * the other will be ignored.
     * 
     * Usually, you want to produce an image of a specific width or (less commonly) height to meet
     * a template/presentation requirement.
     * 
     * Requires GD library.
     * 
     * @param int $width Width of the cached image output.
     * @param int $height Height of the cached image output.
     * @return string $url URL to the cached image.
     */
    public function getCachedImage(int $width = 0, int $height = 0)
    {
        // Validate parameters; and at least one must be set.
        $cleanWidth = $this->validator->isInt($width, 1) ? (int) $width : 0;
        $cleanHeight = $this->validator->isInt($height, 1) ? (int) $height : 0;
        
        if (!$cleanWidth && !$cleanHeight) {
            return false;
        }

        // Check if this object actually has an associated image, and that it is readable.
        if (!$this->image || !is_readable(TFISH_IMAGE_PATH . $this->image)) {
            return false;
        }

        // Check if a cached copy of the requested dimensions already exists in the cache and return
        // URL. CONVENTION: Thumbnail name should follow the pattern:
        // imageFileName . '-' . $width . 'x' . $height
        $filename = pathinfo($this->image, PATHINFO_FILENAME);
        $extension = '.' . pathinfo($this->image, PATHINFO_EXTENSION);
        $cachedPath = TFISH_PUBLIC_CACHE_PATH . $filename . '-';
        $cachedUrl = TFISH_CACHE_URL . $filename . '-';
        $originalPath = TFISH_IMAGE_PATH . $filename . $extension;
        
        if ($cleanWidth > $cleanHeight) {
            $cachedPath .= $cleanWidth . 'w' . $extension;
            $cachedUrl .= $cleanWidth . 'w' . $extension;
        } else {
            $cachedPath .= $cleanHeight . 'h' . $extension;
            $cachedUrl .= $cleanHeight . 'h' . $extension;
        }

        // Security check - is the cachedPath actually pointing at the cache directory? Because
        // if it isn't, then we don't want to cooperate by returning anything.
        if (is_readable($cachedPath)) {
            return $cachedUrl;
        }

        // Get the size. Note that:
        // $properties['mime'] holds the mimetype, eg. 'image/jpeg'.
        // $properties[0] = width, [1] = height, [2] = width = "x" height = "y" which is useful
        // for outputting size attribute.
        $properties = getimagesize($originalPath);

        if (!$properties) {
            return false;
        }

        // In order to preserve proportions, need to calculate the size of the other dimension.
        if ($cleanWidth > $cleanHeight) {
            $destinationWidth = $cleanWidth;
            $destinationHeight = (int) (($cleanWidth / $properties[0]) * $properties[1]);
        } else {
            $destinationWidth = (int) (($cleanHeight / $properties[1]) * $properties[0]);
            $destinationHeight = $cleanHeight;
        }
        
        $result = $this->scaleAndCacheImage($properties, $originalPath, $cachedPath, 
            $destinationWidth, $destinationHeight);
        
        if (!$result) {
            return false;
        }
        
        return $cachedUrl;
    }
    
    /**
     * Generates thumbnails of content->image property and saves them to the image cache.
     * 
     * @param array $properties Original image size properties as returned by getimagesize().
     * @param string $originalPath Path to the original image file stored on the server.
     * @param string $cachedPath Path to the scaled version of the image, stored in the image cache.
     * @param int $destinationWidth Width to scale image to.
     * @param int $destinationHeight Height to scale image to.
     * @return boolean True on success, false on failure.
     */
    private function scaleAndCacheImage(array $properties, string $originalPath,
            string $cachedPath, int $destinationWidth, int $destinationHeight)
    {
        // Create a blank (black) image RESOURCE of the specified size.
        $thumbnail = imagecreatetruecolor($destinationWidth, $destinationHeight);
        
        $result = false;

        switch ($properties['mime']) {
            case "image/jpeg":
                $original = imagecreatefromjpeg($originalPath);
                imagecopyresampled($thumbnail, $original, 0, 0, 0, 0, $destinationWidth,
                        $destinationHeight, $properties[0], $properties[1]);
                // Optional third quality argument 0-99, higher is better quality.
                $result = imagejpeg($thumbnail, $cachedPath, 80);
                break;

            case "image/png":
            case "image/gif":
                if ($properties['mime'] === "image/gif") {
                    $original = imagecreatefromgif($originalPath);
                } else {
                    $original = imagecreatefrompng($originalPath);
                }

                /**
                 * Handle transparency
                 * 
                 * The following code block (only) is a derivative of
                 * the PHP_image_resize project by Nimrod007, which is a fork of the
                 * smart_resize_image project by Maxim Chernyak. The source code is available
                 * from the link below, and it is distributed under the following license terms:
                 * 
                 * Copyright © 2008 Maxim Chernyak
                 * 
                 * Permission is hereby granted, free of charge, to any person obtaining a copy
                 * of this software and associated documentation files (the "Software"), to deal
                 * in the Software without restriction, including without limitation the rights
                 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell 
                 * copies of the Software, and to permit persons to whom the Software is
                 * furnished to do so, subject to the following conditions:
                 * 
                 * The above copyright notice and this permission notice shall be included in
                 * all copies or substantial portions of the Software.
                 * 
                 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
                 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
                 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
                 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
                 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
                 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
                 * THE SOFTWARE.
                 * 
                 * https://github.com/Nimrod007/PHP_image_resize 
                 */
                // Sets the transparent colour in the given image, using a colour identifier
                // created with imagecolorallocate().
                $transparency = imagecolortransparent($original);
                $numberOfColours = imagecolorstotal($original);

                if ($transparency >= 0 && $transparency < $numberOfColours) {
                    // Get the colours for an index.
                    $transparentColour = imagecolorsforindex($original, $transparency);
                    // Allocate a colour for an image. The first call to imagecolorallocate() 
                    // fills the background colour in palette-based images created using 
                    // imagecreate().
                    $transparency = imagecolorallocate($thumbnail, $transparentColour['red'],
                            $transparentColour['green'], $transparentColour['blue']);
                    // Flood fill with the given colour starting at the given coordinate
                    // (0,0 is top left).
                    imagefill($thumbnail, 0, 0, $transparency);
                    // Define a colour as transparent.
                    imagecolortransparent($thumbnail, $transparency);
                }

                // Bugfix from original: Changed next block to be an independent if, instead of
                // an elseif linked to previous block. Otherwise PNG transparency doesn't work.
                if ($properties['mime'] === "image/png") {
                    // Set the blending mode for an image.
                    imagealphablending($thumbnail, false);
                    // Allocate a colour for an image ($image, $red, $green, $blue, $alpha).
                    $colour = imagecolorallocatealpha($thumbnail, 0, 0, 0, 127);
                    // Flood fill again.
                    imagefill($thumbnail, 0, 0, $colour);
                    // Set the flag to save full alpha channel information (as opposed to single
                    // colour transparency) when saving png images.
                    imagesavealpha($thumbnail, true);
                }
                /**
                 * End code derived from PHP_image_resize project.
                 */

                // Copy and resize part of an image with resampling.
                imagecopyresampled($thumbnail, $original, 0, 0, 0, 0, $destinationWidth,
                        $destinationHeight, $properties[0], $properties[1]);

                // Output a useable png or gif from the image resource.
                if ($properties['mime'] === "image/gif") {
                    $result = imagegif($thumbnail, $cachedPath);
                } else {
                    // Quality is controlled through an optional third argument (0-9, lower is
                    // better).
                    $result = imagepng($thumbnail, $cachedPath, 0);
                }
                break;

            // Anything else, no can do.
            default:
                return false;
        }

        if (!$result) {
            return false;
        }
        
        imagedestroy($thumbnail); // Free memory.
        
        return true;
    }
    
    /**
     * Returns an array of audio mimetypes that are permitted for content objects.
     * 
     * Note that ogg audio files should use the .oga extension, although the legacy .ogg extension
     * is still acceptable, although it must no longer be used for video files.
     * 
     * @return array Array of permitted audio mimetypes in file extension => mimetype format.
     */
    public function getListOfAllowedAudioMimetypes()
    {
        return array(
            "mp3" => "audio/mpeg",
            "oga" => "audio/ogg",
            "ogg" => "audio/ogg",
            "wav" => "audio/x-wav"
        );
    }
    
    /**
     * Returns an array of image mimetypes that are permitted for content objects.
     * 
     * @return array Array of permitted image mimetypes in file extension => mimetype format.
     */
    public function getListOfAllowedImageMimetypes()
    {
        return array(
            "gif" => "image/gif",
            "jpg" => "image/jpeg",
            "png" => "image/png"
        );
    }            

    /**
     * Returns an array of video mimetypes that are permitted for upload.
     * 
     * Note that ogg video files must use the .ogv file extension. Please do not use .ogg for
     * video files as this practice has been deprecated in favour of .ogv. While .ogg is still in
     * wide use it is now presumed to refer to audio files only.
     * 
     * @return array Array of permitted video mimetypes in file extension => mimetype format.
     */
    public function getListOfAllowedVideoMimetypes()
    {
        return array(
            "mp4" => "video/mp4",
            "ogv" => "video/ogg",
            "webm" => "video/webm"
        );
    }
    
    /**
     * Returns an array of base object properties that are not used by this subclass.
     * 
     * This list is also used in update calls to the database to ensure that unused columns are
     * cleared and reset with default values.
     * 
     * @return array
     */
    public function getListOfZeroedProperties()
    {
        return array();
    }
    
    /**
     * Returns a whitelist of object properties whose values are allowed be set.
     * 
     * This function is used to build a list of $allowedVars for a content object. Child classes
     * use this list to unset properties they do not use. Properties that are not resident in the
     * database are also unset here (handler, template, module and icon).
     * 
     * @return array Array of object properties as keys.
     */
    public function getPropertyWhitelist()
    {        
        $properties = array();
        
        foreach ($this as $key => $value) {
            $properties[$key] = '';
        }
        
        unset($properties['handler'], $properties['template'], $properties['module'],
                $properties['icon']);
        
        return $properties;
    }
    
    /**
     * Determine if the media file (mime) type is valid for this content type.
     * 
     * Used in templates to determine whether a media file should be displayed or not.
     * For example, if you attach a video file to an audio content object, the
     * inline player will not be displayed (because it will not work).
     * 
     * @return boolean True if media mimetype is valid for this content type, otherwise false.
     */
    public function isValidMedia()
    {
        if (!$this->media) {
            return false;
        }
        
        $allowedMimetypes = array();

        switch($this->type) {
            case "TfAudio":
                $allowedMimetypes = $this->getListOfAllowedAudioMimetypes();
                break;
            case "TfImage":
                $allowedMimetypes = $this->getListOfAllowedImageMimetypes();
                break;
            case "TfVideo":
                $allowedMimetypes = $this->getListOfAllowedVideoMimetypes();
                break;
            default:
                $allowedMimetypes = $this->getListOfPermittedUploadMimetypes();
        }

        if (in_array($this->format, $allowedMimetypes, true)) {
            return true;
        }
        
        return false;
    }

    /**
     * Populates the properties of the object from external (untrusted) data source.
     * 
     * Note that the supplied data is internally validated by __set().
     * 
     * @param array $dirtyInput Usually raw form $_REQUEST data.
     * @param bool $convertUrlTOConstant Convert base url to TFISH_LINK (true) or TFISH_LINK to base url (false).
     */
    public function loadPropertiesFromArray(array $dirtyInput, $convertUrlToConstant = true)
    {
        $deleteImage = (isset($dirtyInput['deleteImage']) && !empty($dirtyInput['deleteImage']))
                ? true : false;
        $deleteMedia = (isset($dirtyInput['deleteMedia']) && !empty($dirtyInput['deleteMedia']))
                ? true : false;
        
        $this->loadProperties($dirtyInput);
                       
        // If date is empty default to today.
        if (isset($this->date) && empty($dirtyInput['date'])) {
            $this->setDate(date(DATE_RSS, time()));
        }

        // Convert URLs back to TFISH_LINK for insertion or update, to aid portability.        
        if (isset($this->teaser) && !empty($dirtyInput['teaser'])) {
            $teaser = $this->convertBaseUrlToConstant($dirtyInput['teaser'], $convertUrlToConstant);            
            $this->setTeaser($teaser);
        }

        if (isset($this->description) && !empty($dirtyInput['description'])) {
            $description = $this->convertBaseUrlToConstant($dirtyInput['description'], $convertUrlToConstant);            
            $this->setDescription($description);
        }
    }
    
    /**
     * Assign form data to content object.
     * 
     * Note that data validation is carried out internally via the setters. This is a helper method
     * for loadPropertiesFromArray().
     * 
     * @param array $dirtyInput Array of untrusted form input.
     */
    private function loadProperties(array $dirtyInput)
    {
        if (isset($this->id) && isset($dirtyInput['id']))
            $this->setId((int) $dirtyInput['id']);
        if (isset($this->type) && isset($dirtyInput['type']))
            $this->setType((string) $dirtyInput['type']);
        if (isset($this->title) && isset($dirtyInput['title']))
            $this->setTitle((string) $dirtyInput['title']);
        if (isset($this->teaser) && isset($dirtyInput['teaser']))
            $this->setTeaser((string) $dirtyInput['teaser']);
        if (isset($this->description) && isset($dirtyInput['description']))
            $this->setDescription((string) $dirtyInput['description']);
        if (isset($this->media) && isset($dirtyInput['media']))
            $this->setMedia((string) $dirtyInput['media']);
        if (isset($this->format) && isset($dirtyInput['format']))
            $this->setFormat((string) $dirtyInput['format']);
        if (isset($this->fileSize) && isset($dirtyInput['fileSize']))
            $this->setFileSize((int) $dirtyInput['fileSize']);
        if (isset($this->creator) && isset($dirtyInput['creator']))
            $this->setCreator((string) $dirtyInput['creator']);
        if (isset($this->image) && isset($dirtyInput['image']))
            $this->setImage((string) $dirtyInput['image']);
        if (isset($this->caption) && isset($dirtyInput['caption']))
            $this->setCaption((string) $dirtyInput['caption']);
        if (isset($this->date) && isset($dirtyInput['date']))
            $this->setDate((string) $dirtyInput['date']);
        if (isset($this->parent) && isset($dirtyInput['parent']))
            $this->setParent((int)$dirtyInput['parent']);
        if (isset($this->language) && isset($dirtyInput['language']))
            $this->setLanguage((string) $dirtyInput['language']);
        if (isset($this->rights) && isset($dirtyInput['rights']))
            $this->setRights((int) $dirtyInput['rights']);
        if (isset($this->publisher) && isset($dirtyInput['publisher']))
            $this->setPublisher((string) $dirtyInput['publisher']);
        if (isset($this->tags) && isset($dirtyInput['tags']))
            $this->setTags((array) $dirtyInput['tags']);
        if (isset($this->online) && isset($dirtyInput['online']))
            $this->setOnline((int) $dirtyInput['online']);
        if (isset($this->submissionTime) && isset($dirtyInput['submissionTime']))
            $this->setSubmissionTime((int) $dirtyInput['submissionTime']);
        if (isset($this->lastUpdated) && isset($dirtyInput['lastUpdated']))
            $this->setLastUpdated((int) $dirtyInput['lastUpdated']);
        if (isset($this->expiresOn) && isset($dirtyInput['expiresOn']))
            $this->setExpiresOn((int) $dirtyInput['expiresOn']);
        if (isset($this->counter) && isset($dirtyInput['counter']))
            $this->setCounter((int) $dirtyInput['counter']);
        if (isset($this->metaTitle) && isset($dirtyInput['metaTitle']))
            $this->setMetaTitle((string) $dirtyInput['metaTitle']);
        if (isset($this->metaDescription) && isset($dirtyInput['metaDescription']))
            $this->setMetaDescription((string) $dirtyInput['metaDescription']);
        if (isset($this->seo) && isset($dirtyInput['seo']))
            $this->setSeo((string) $dirtyInput['seo']);
    }
    
    /**
     * Sets the image property from untrusted form data.
     * 
     * This method should be called after loadPropertiesFromArray(), but only when form data is
     * being sent (submit or update operations). Do not use it when retrieving an object from the
     * database.
     * 
     * @param array $propertyWhitelist List of permitted object properties.
     */
    public function loadImage(array $propertyWhitelist)
    {
        if (array_key_exists('image', $propertyWhitelist) && !empty($_FILES['image']['name'])) {
            $cleanImageFilename = $this->validator->trimString($_FILES['image']['name']);
            
            if ($cleanImageFilename) {
                $this->setImage($cleanImageFilename);
            }
        }
    }
    
    /**
     * Sets the media property from untrusted form data reading from $_FILES.
     * 
     * This method should be called after loadPropertiesFromArray(), but only when form data is
     * being sent (submit or update operations). Do not use it when retrieving an object from the
     * database.
     * 
     * @param array $propertyWhitelist List of permitted object properties.
     */
    public function loadMedia(array $propertyWhitelist)
    {
        if (array_key_exists('media', $propertyWhitelist) && !empty($_FILES['media']['name'])) {
            $cleanMediaFilename = $this->validator->trimString($_FILES['media']['name']);
            
            if ($cleanMediaFilename) {
                $mimetypeWhitelist = $this->getListOfPermittedUploadMimetypes();
                $extension = mb_strtolower(pathinfo($cleanMediaFilename, PATHINFO_EXTENSION), 'UTF-8');
                
                $this->setMedia($cleanMediaFilename);
                $this->setFormat($mimetypeWhitelist[$extension]);
                $this->setFileSize($_FILES['media']['size']);
            }
        }
    }
    
    /**
     * Converts bytes to a human readable units (KB, MB, GB etc).
     * 
     * @param int $bytes File size in bytes.
     * @return string Bytes expressed as convenient human readable units.
     */
    public function convertBytesToHumanReadable(int $bytes)
    {
        $cleanBytes = (int) $bytes;
        $unit = $val = '';

        if ($cleanBytes === 0 || $cleanBytes < ONE_KILOBYTE) {
            $unit = ' bytes';
            $val = $cleanBytes;
        } elseif ($cleanBytes >= ONE_KILOBYTE && $cleanBytes < ONE_MEGABYTE) {
            $unit = ' KB';
            $val = ($cleanBytes / ONE_KILOBYTE);
        } elseif ($cleanBytes >= ONE_MEGABYTE && $cleanBytes < ONE_GIGABYTE) {
            $unit = ' MB';
            $val = ($cleanBytes / ONE_MEGABYTE);
        } else {
            $unit = ' GB';
            $val = ($cleanBytes / ONE_GIGABYTE);
        }

        $val = round($val, 2);

        return $val . ' ' . $unit;
    }
    
    /**
     * Set the caption that will accompany the image property.
     * 
     * @param string $caption Caption describing image.
     */
    public function setCaption(string $caption)
    {
        $cleanCaption = (string) $this->validator->trimString($caption);
        $this->caption = $cleanCaption;
    }
    
    /**
     * Return the caption XSS escaped for display.
     * 
     * @return string
     */
    public function getCaption()
    {
        return $this->validator->escapeForXss($this->caption);
    }
    
    /**
     * Set the view/download counter for this object.
     * 
     * @param int $counter Counter value.
     */
    public function setCounter(int $counter)
    {
        if ($this->validator->isInt($counter, 0)) {
            $this->counter = $counter;
        } else {
            trigger_error(TFISH_ERROR_NOT_INT, E_USER_ERROR);
        }
    }
    
    /**
     * Return the counter value, XSS safe.
     * 
     * @return int Counter.
     */
    public function getCounter()
    {
        return (int) $this->counter;
    }
    
    /**
     * Set the creator of this object.
     * 
     * @param string $creator Name of the creator.
     */
    public function setCreator(string $creator)
    {
        $cleanCreator = (string) $this->validator->trimString($creator);
        $this->creator = $cleanCreator;
    }
    
    /**
     * Return the creator XSS escaped for display.
     * 
     * @return string Creator.
     */
    public function getCreator()
    {
        return $this->validator->escapeForXss($this->creator);
    }
    
    /**
     * Set the publication date of this object expressed as a string.
     * 
     * @param string $date Publication date.
     */
    public function setDate(string $date)
    {
        $date = (string) $this->validator->trimString($date);

        // Ensure format complies with DATE_RSS
        $checkDate = date_parse_from_format('Y-m-d', $date);

        if (!$checkDate || $checkDate['warning_count'] > 0
                || $checkDate['error_count'] > 0) {
            // Bad date supplied, default to today.
            $date = date(DATE_RSS, time());
            trigger_error(TFISH_ERROR_BAD_DATE_DEFAULTING_TO_TODAY, E_USER_WARNING);
        }
        
        $this->date = $date;
    }
    
    /**
     * Return the date XSS escaped for display.
     * 
     * @return string Date.
     */
    public function getDate()
    {
        $date = date_create($this->date);
        $formatted_date = date_format($date, 'j F Y');
        return $this->validator->escapeForXss($formatted_date);
    }
    
    /**
     * Set the description of this object (HTML field).
     * 
     * @param string $description Description in HTML.
     */
    public function setDescription(string $description)
    {
        $description = (string) $this->validator->trimString($description);
        $this->description = $this->validator->filterHtml($description);
    }
    
    /**
     * Return the description of this object (prevalidated HTML).
     * 
     * Do not escape HTML for front end display, as HTML properties are input validated with
     * HTMLPurifier. However, you must escape HTML properties when editing content; this is
     * because TinyMCE requires entities to be double escaped for storage (this is a specification
     * requirement) or they will not display property.
     * 
     * @param bool $escapeHtml True to escape HTML, false to return unescaped HTML.
     * @return string Description of this machine as HTML.
     */
    public function getDescription($escapeHtml = false)
    {
        // Output HTML for display: Do not escape as it has been input filtered with HTMLPurifier.        
        if ($escapeHtml === false) {
            return $this->description;
        }
        
        // Output for display in the TinyMCE editor (editing only).
        if ($escapeHtml === true) {    
            return htmlspecialchars($this->description, ENT_NOQUOTES, 'UTF-8', true);
        }
    }
    
    /**
     * Set the file size for the media attachment to this object.
     * 
     * @param int $fileSize Filesize in bytes.
     */
    public function setFileSize(int $fileSize)
    {
        if ($this->validator->isInt($fileSize, 0)) {
            $this->fileSize = $fileSize;
        } else {
            trigger_error(TFISH_ERROR_NOT_INT, E_USER_ERROR);
        }
    }
    
    /**
     * Returns the file size in human readable format, XSS escaped for display.
     * 
     * @return string File size.
     */
    public function getFileSize()
    {
        return $this->convertBytesToHumanReadable($this->fileSize);
    }
    
    /**
     * Set the format (mimetype) for the media attachment to this object.
     * 
     * Mimetypes must be official/correct as they are used in headers to initiate streaming of
     * media files.
     * 
     * @param string $format Mimetype.
     */
    public function setFormat(string $format)
    {
        $format = (string) $this->validator->trimString($format);

        $mimetypeWhitelist = $this->getListOfPermittedUploadMimetypes();
        if (!empty($format) && !in_array($format, $mimetypeWhitelist, true)) {
            trigger_error(TFISH_ERROR_ILLEGAL_MIMETYPE, E_USER_ERROR);
        }
        
        $this->format = $format;
    }
    
    /**
     * Returns the file extension associated with the media mimetype, XSS escaped for display.
     * 
     * @return string File extension.
     */
    public function getFormat()
    {
        $mimetypeWhitelist = $this->getListOfPermittedUploadMimetypes();
        return $this->validator->escapeForXss(array_search($this->format, $mimetypeWhitelist));
    }
    
    /**
     * Set the image for this content object.
     * 
     * @param string $image Filename of the image.
     */
    public function setImage(string $image)
    {
        $image = (string) $this->validator->trimString($image);
        
        // Check image/media paths for directory traversals and null byte injection.
        if ($this->validator->hasTraversalorNullByte($image)) {
            trigger_error(TFISH_ERROR_TRAVERSAL_OR_NULL_BYTE, E_USER_ERROR);
            exit; // Hard stop due to high probability of abuse.
        }

        // Check image file is a permitted mimetype.
        $mimetypeWhitelist = $this->getListOfAllowedImageMimetypes();
        $extension = mb_strtolower(pathinfo($image, PATHINFO_EXTENSION), 'UTF-8');
        
        if (!empty($extension) && !array_key_exists($extension, $mimetypeWhitelist)) {
            $this->image = '';
            trigger_error(TFISH_ERROR_ILLEGAL_MIMETYPE, E_USER_ERROR);
        } else {
            $this->image = $image;
        }        
    }
    
    /**
     * Return the image file name XSS escaped for display.
     * 
     * @return string Image file name.
     */
    public function getImage()
    {
        return $this->validator->escapeForXss($this->image);
    }
    
    /**
     * Set the language of this content object.
     * 
     * @param string $language ISO_639-1 two-letter language code.
     */
    public function setLanguage(string $language)
    {        
        $language = (string) $this->validator->trimString($language);
        $languageWhitelist = $this->getListOfLanguages();

        if (!array_key_exists($language, $languageWhitelist)) {
            trigger_error(TFISH_ERROR_ILLEGAL_VALUE, E_USER_ERROR);
        }
        
        $this->language = $language;
    }
    
    /**
     * Returns the language of this object XSS escaped for display.
     * 
     * @return string Language.
     */
    public function getLanguage()
    {
        $langageWhitelist = $this->getListOfLanguages();
        
        return $this->validator->escapeForXss($languageWhitelist[$this->language]);
    }
    
    /**
     * Set the media attachment for this content object.
     * 
     * @param string $media Filename of the media attachment.
     */
    public function setMedia(string $media)
    {
        $media = (string) $this->validator->trimString($media);

        // Check image/media paths for directory traversals and null byte injection.
        if ($this->validator->hasTraversalorNullByte($media)) {
            trigger_error(TFISH_ERROR_TRAVERSAL_OR_NULL_BYTE, E_USER_ERROR);
            exit; // Hard stop due to high probability of abuse.
        }

        // Check media file is a permitted mimetype.
        $mimetypeWhitelist = $this->getListOfPermittedUploadMimetypes();
        $extension = mb_strtolower(pathinfo($media, PATHINFO_EXTENSION), 'UTF-8');

        if (empty($extension) 
                || (!empty($extension) && !array_key_exists($extension, $mimetypeWhitelist))) {
            $this->media = '';
            $this->format = '';
            $this->fileSize = '';
        } else {
            $this->media = $media;
        }        
    }
    
    /**
     * Return the media file name XSS escaped for display.
     * 
     * @return string Media file name.
     */
    public function getMedia()
    {
        return $this->validator->escapeForXss($this->media);
    }
    
    /**
     * Set the ID of the parent for this object (must be a collection).
     * 
     * Parent ID must be different to content ID (cannot declare self as parent).
     * 
     * @param int $parent ID of parent object.
     */
    public function setParent(int $parent)
    {        
        if (!$this->validator->isInt($parent, 0)) {
                trigger_error(TFISH_ERROR_NOT_INT, E_USER_ERROR);
        }

        if ($parent === $this->id && $parent > 0) {
            trigger_error(TFISH_ERROR_CIRCULAR_PARENT_REFERENCE);
        } else {
            $this->parent = $parent;
        }
    }
    
    /**
     * Returns the ID of the parent object, XSS safe.
     * 
     * @return int ID of parent.
     */
    public function getParent()
    {
        return (int) $this->parent;
    }
    
    /**
     * Set the publisher of this content object.
     * 
     * @param string $publisher Name of the publisher.
     */
    public function setPublisher(string $publisher)
    {
        $cleanPublisher = (string) $this->validator->trimString($publisher);
        $this->publisher = $cleanPublisher;
    }
    
    /**
     * Returns the publisher, XSS escaped for display.
     * 
     * @return string Publisher.
     */
    public function getPublisher()
    {
        return $this->validator->escapeForXss($this->publisher);
    }
    
    /**
     * Set the intellectual property rights for this content object.
     * 
     * See getListOfRights() for the available licenses, which you can customise to suit yourself.
     * 
     * @param int $rights ID of a copyright license.
     */
    public function setRights(int $rights)
    {
        if ($this->validator->isInt($rights, 1)) {
            $this->rights = $rights;
        } else {
            trigger_error(TFISH_ERROR_NOT_INT, E_USER_ERROR);
        }
    }
    
    /**
     * Returns the intellectual property rights license for this content object XSS escaped for display.
     * 
     * @return string Name of intellectual property rights license for this content object.
     */
    public function getRights()
    {
        $rightsList = $this->getListOfRights();
        
        return $this->validator->escapeForXss($rightsList[$this->rights]);
    }
    
    /**
     * Set the tags associated with this content object.
     * 
     * @param array $tags IDs of associated tags.
     */
    public function setTags(array $tags)
    {
        if ($this->validator->isArray($tags)) {
            $cleanTags = array();

            foreach ($tags as $tag) {
                $cleanTag = (int) $tag;

                if ($this->validator->isInt($cleanTag, 1)) {
                    $cleanTags[] = $cleanTag;
                } else {
                    trigger_error(TFISH_ERROR_NOT_INT, E_USER_ERROR);
                }
                unset($cleanTag);
            }

            $this->tags = $cleanTags;
        } else {
            trigger_error(TFISH_ERROR_NOT_ARRAY, E_USER_ERROR);
        }
    }
    
    /**
     * Set the teaser (short form description) for this content object.(HTML).
     * 
     * @param string $teaser Teaser (in HTML).
     */
    public function setTeaser(string $teaser)
    {
        $teaser = (string) $this->validator->trimString($teaser);
        $this->teaser = $this->validator->filterHtml($teaser);
    }
    
    /**
     * Return the teaser of this content (prevalidated HTML).
     * 
     * Do not escape HTML for front end display, as HTML properties are input validated with
     * HTMLPurifier. However, you must escape HTML properties when editing content; this is
     * because TinyMCE requires entities to be double escaped for storage (this is a specification
     * requirement) or they will not display property.
     * 
     * @param bool $escapeHtml True to escape HTML, false to return unescaped HTML.
     * @return string Teaser (short form description) of this machine as HTML.
     */
    public function getTeaser($escapeHtml = false)
    {
        // Output HTML for display: Do not escape as it has been input filtered with HTMLPurifier.        
        if ($escapeHtml === false) {
            return $this->teaser;
        }
        
        // Output for display in the TinyMCE editor (editing only).
        if ($escapeHtml === true) {    
            return htmlspecialchars($this->teaser, ENT_NOQUOTES, 'UTF-8', true);
        }
    }
    
    /**
     * Set the title of this content object.
     * 
     * @param string $title Title of this object.
     */
    public function setTitle(string $title)
    {
        $cleanTitle = (string) $this->validator->trimString($title);
        $this->title = $cleanTitle;
    }
    
    /**
     * Returns the title of this content XSS escaped for display.
     * 
     * @return string Title.
     */
    public function getTitle()
    {
        return $this->validator->escapeForXss($this->title);
    }
    
    /**
     * Set the content type for this object.
     * 
     * Type must be the name of a content subclass.
     * 
     * @param string $type Class name for this content object.
     */
    public function setType(string $type)
    {
        $cleanType = (string) $this->validator->trimString($type);

        if ($this->validator->isAlpha($cleanType)) {
            $this->type = $cleanType;
        } else {
            trigger_error(TFISH_ERROR_NOT_ALPHA, E_USER_ERROR);
        }
    }
    
    /**
     * Returns the type (class name) of this content XSS escaped for display.
     * 
     * @return string Type (class name).
     */
    public function getType()
    {
        return $this->validator->escapeForXss($this->type);
    }

}
