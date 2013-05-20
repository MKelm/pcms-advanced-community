<?php
/**
 * Advanced community surfer gallery upload data class to handle all sorts of related data
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
 * Advanced community surfer gallery upload data class to handle all sorts of related data
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunitySurferGalleryUploadData extends PapayaObject {
  
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
  * Reference object to create urls
  * @var PapayaUiReference
  */
  protected $_reference = NULL;
  
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
   * Community connector
   * @var connector_surfers
   */
  protected $_communityConnector = NULL;
  
  /**
   * Surfer galleries database records
   * @var object
   */
  protected $_galleries = NULL;
  
  /**
   * Media db edit object
   * @var object
   */
  protected $_mediaDBEdit = NULL;
  
  /**
   * Set data by plugin object
   * 
   * @param array $data
   */
  public function setPluginData($data) {
    $mergeCaptions = array('caption_dialog_image', 'caption_dialog_button');
    foreach ($mergeCaptions as $mergeCaption) {
      if (isset($data[$mergeCaption])) {
        $this->captions[$mergeCaption] = $data[$mergeCaption];
      }
    }
    $mergeMessages = array(
      'message_dialog_input_error', 'message_dialog_error_no_folder',
      'message_dialog_error_no_upload_file', 'message_dialog_error_upload',
      'message_dialog_error_file_extension', 'message_dialog_error_file_type',
      'message_dialog_error_media_db'
    );
    foreach ($mergeMessages as $mergeMessage) {
      if (isset($data[$mergeMessage])) {
        $this->messages[$mergeMessage] = $data[$mergeMessage];
      }
    }
  }
  
  /**
   * Set/get data of current ressource by type and id
   * 
   * @param string $type
   * @param integer|string $id
   */
  public function ressource($type = NULL, $id = NULL) {
    if (isset($type) && isset($id)) {
      $id = $this->communityConnector()->getIdByHandle($id);
      $currentSurfer = $this->communityConnector()->getCurrentSurfer();
      if ($id == $currentSurfer->surfer['surfer_id'] && $currentSurfer->isValid) {
        $this->_ressource['type'] = $type;
        $this->_ressource['id'] = $id;
      }
      
    }
    return $this->_ressource;
  }
  
  /**
   * Set ressource parameters for use in reference object
   * 
   * @param string $parameterGroup
   * @param array $parameters
   * @return array
   */
  public function ressourceParameters($parameterGroup = NULL, $parameters = NULL) {
    if (isset($parameterGroup) && isset($parameters)) {
      $this->_ressourceParameters[$parameterGroup] = $parameters;
    }
    return $this->_ressourceParameters;
  }
  
  /**
  * The basic reference object used by the subobjects to create urls.
  *
  * @param PapayaUiReference $reference
  * @return PapayaUiReference
  */
  public function reference(PapayaUiReference $reference = NULL) {
    if (isset($reference)) {
      $this->_reference = $reference;
    } elseif (is_null($this->_reference)) {
      $this->_reference = new PapayaUiReference();
      $this->_reference->papaya($this->papaya());
      foreach ($this->ressourceParameters() as $parameterGroup => $parameters) {
        $this->_reference->setParameters(
          $parameters, $parameterGroup
        );
      }
    }
    return $this->_reference;
  }
  
  /**
   * Get/set community connector
   * 
   * @param object $connector
   * @return object
   */
  public function communityConnector(connector_surfers $connector = NULL) {
    if (isset($connector)) {
      $this->_communityConnector = $connector;
    } elseif (is_null($this->_communityConnector)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
      $this->_communityConnector = base_pluginloader::getPluginInstance(
        '06648c9c955e1a0e06a7bd381748c4e4', $this
      );
    }
    return $this->_communityConnector;
  }
  
  /**
  * Access to the surfer galleries database records data
  *
  * @param ACommunityContentSurferGalleries $comments
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
   * Media DB Edit to save image uploads
   * 
   * @param base_mediadb_edit $mediaDBEdit
   * @return base_mediadb_edit
   */
  public function mediaDBEdit(base_mediadb_edit $mediaDBEdit = NULL) {
    if (isset($mediaDBEdit)) {
      $this->_mediaDBEdit = $mediaDBEdit;
    } elseif (is_null($this->_mediaDBEdit)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_mediadb_edit.php');
      $this->_mediaDBEdit = new base_mediadb_edit();
    }
    return $this->_mediaDBEdit;
  }
  
}
