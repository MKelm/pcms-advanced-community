<?php
/**
 * Advanced community surfer gallery teaser data class to handle all sorts of related data
 *
 * @copyright 2013 by Martin Kelm
 * @link http://idx.shrt.ws
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 * You can redistribute and/or modify this script under the terms of the GNU General Public
 * License (GPL) version 2, provided that the copyright and license notes, including these
 * lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */

/**
 * Advanced community surfer gallery teaser data class to handle all sorts of related data
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunitySurferGalleryTeaserData extends PapayaObject {
  
  /**
   * Owner object
   * @var ACommunitySurferGalleryUpload
   */
  public $owner = NULL;
  
  /**
   * A list of captions to be used
   * @var array
   */
  public $captions = array();
  
  /**
   * A list of messages to be used
   * @var array
   */
  public $messages = array();
  
  /**
   * Current language id
   * @var integer
   */
  public $languageId = 0;

  /**
   * Current comments ressource by type and id
   * @var array
   */
  protected $_ressource = NULL;
  
  /**
   * Ressource parameters
   * @var array
   */
  protected $_ressourceParameters = array();
  
  /**
   * Ressource is active surfer
   * @var boolean
   */
  public $ressourceIsActiveSurfer = FALSE;
  
  /**
   * Surfer galleries database records
   * @var object
   */
  protected $_galleries = NULL;
  
  /**
   * Media db object
   * @var object
   */
  protected $_mediaDB = NULL;
  
  /**
   * Thumbnail amount
   * @var integer
   */
  public $thumbnailAmount = 4;
  
  /**
   * Thumbnail size
   * @var integer
   */
  public $thumbnailSize = 100;
  
  /**
   * Thubmnail resize mode
   * @var string
   */
  public $thumbnailResizeMode = 'mincrop';
  
  /**
   * Set data by plugin object
   * 
   * @param array $data
   * @param array $captionNames
   * @param array $messageNames
   */
  public function setPluginData($data, $captionNames = array(), $messageNames = array()) {
    foreach ($captionNames as $name) {
      if (isset($data[$name])) {
        $newName = str_replace('caption_', '', $name);
        $this->captions[$newName] = $data[$name];
      }
    }
    foreach ($messageNames as $name) {
      if (isset($data[$name])) {
        $newName = str_replace('message_', '', $name);
        $this->messages[$newName] = $data[$name];
      }
    }
    $this->thumbnailAmount = (int)$data['thumbnail_amount'];
    $this->thumbnailSize = (int)$data['thumbnail_size'];
    $this->thumbnailResizeMode = trim($data['thumbnail_resize_mode']);
  }
  
  /**
   * Set/get data of current ressource by type and id
   * 
   * @param string $type
   * @param string $handle
   */
  public function ressource($type = NULL, $handle = NULL) {
    if (isset($type)) {
      if (isset($handle)) {
        $id = $this->owner->communityConnector()->getIdByHandle($handle);
      }
      $currentSurfer = $this->owner->communityConnector()->getCurrentSurfer();
      if (!empty($currentSurfer->surfer['surfer_id']) && $currentSurfer->isValid) {
        if (!isset($id)) {
          $id = $currentSurfer->surfer['surfer_id'];
        }
        $this->ressourceIsActiveSurfer = $currentSurfer->surfer['surfer_id'] == $id;
      }
      if (isset($type) && isset($id)) {
        $this->_ressource = array(
         'type' => $type,
         'id' => $id
        );
      }
    }
    return $this->_ressource;
  }
  
  /**
  * Access to the surfer galleries database records data
  *
  * @param ACommunityContentSurferGalleries $galleries
  * @return ACommunityContentSurferGalleries
  */
  public function galleries(ACommunityContentSurferGalleries $galleries = NULL) {
    if (isset($galleries)) {
      $this->_galleries = $galleries;
    } elseif (is_null($this->_galleries)) {
      include_once(dirname(__FILE__).'/../../../Content/Surfer/Galleries.php');
      $this->_galleries = new ACommunityContentSurferGalleries();
      $this->_galleries->papaya($this->papaya());
    }
    return $this->_galleries;
  }
  
  /**
   * Media DB to get images
   * 
   * @param base_mediadb $mediaDB
   * @return base_mediadb
   */
  public function mediaDB(base_mediadb $mediaDB = NULL) {
    if (isset($mediaDB)) {
      $this->_mediaDB = $mediaDB;
    } elseif (is_null($this->_mediaDB)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_mediadb.php');
      $this->_mediaDB = &base_mediadb::getInstance();
    }
    return $this->_mediaDB;
  }
  
}
