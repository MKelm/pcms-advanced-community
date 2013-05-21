<?php
/**
 * Advanced community surfer gallery upload box
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
 * Basic box class
 */
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');

/**
 * Advanced community surfer gallery upload box
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunitySurferGalleryUploadBox extends base_actionbox implements PapayaPluginCacheable {

  /**
   * Parameter prefix name
   * @var string $paramName
   */
  public $paramName = 'acsg';

  /**
   * Edit fields
   * @var array $editFields
   */
  public $editFields = array(
    'Captions',
    'caption_dialog_image' => array(
      'Dialog Image', 'isNoHTML', TRUE, 'input', 200, '', 'Image'
    ),
    'caption_dialog_button' => array(
      'Dialog Button', 'isNoHTML', TRUE, 'input', 200, '', 'Add'
    ),
    'Messages',
    'message_dialog_input_error' => array(
      'Dialog Input Error', 'isNoHTML', TRUE, 'input', 200, '',
      'Invalid input. Please check the field(s) "%s".'
    ),
    'message_dialog_error_no_folder' => array(
      'Dialog No Folder Error', 'isNoHTML', TRUE, 'input', 200, '',
      'Could not find images folder.'
    ),
    'message_dialog_error_no_upload_file' => array(
      'Dialog No Upload File Error', 'isNoHTML', TRUE, 'input', 200, '',
      'Could not find upload file.'
    ),
    'message_dialog_error_upload' => array(
      'Dialog Upload Error', 'isNoHTML', TRUE, 'input', 200, '',
      'Upload error.'
    ),
    'message_dialog_error_file_extension' => array(
      'Dialog File Extension Error', 'isNoHTML', TRUE, 'input', 200, '',
      'Wrong file extension.'
    ),
    'message_dialog_error_file_type' => array(
      'Dialog File Type Error', 'isNoHTML', TRUE, 'input', 200, '',
      'Wrong file type.'
    ),
    'message_dialog_error_media_db' => array(
      'Dialog Media DB Error', 'isNoHTML', TRUE, 'input', 200, '',
      'Could not comple upload process in Media DB.'
    )
  );

  /**
   * Gallery upload object
   * @var ACommunitySurferGalleryUpload
   */
  protected $_upload = NULL;

  /**
   * Cache definition
   * @var PapayaCacheIdentifierDefinition
   */
  protected $_cacheDefiniton = NULL;

  /**
   * Define the cache definition for output.
   *
   * @see PapayaPluginCacheable::cacheable()
   * @param PapayaCacheIdentifierDefinition $definition
   * @return PapayaCacheIdentifierDefinition
   */
  public function cacheable(PapayaCacheIdentifierDefinition $definition = NULL) {
    if (isset($definition)) {
      $this->_cacheDefiniton = $definition;
    } elseif (NULL == $this->_cacheDefiniton) {
      $currentSurferId = !empty($this->papaya()->surfer->surfer['surfer_id']) ?
          $this->papaya()->surfer->surfer['surfer_id'] : NULL;
      $imageSelected = $this->upload()->parameters()->get('enlarge', NULL);
      if (!empty($currentSurferId) && !isset($imageSelected)) {
        $this->_cacheDefiniton = new PapayaCacheIdentifierDefinitionBoolean(FALSE);
      } else {
        $this->_cacheDefiniton = new PapayaCacheIdentifierDefinitionValues(
          array('acommunity_surfer_gallery_upload')
        );
      }
    }
    return $this->_cacheDefiniton;
  }

  /**
   * Set ressource data to get surfer
   */
  public function setRessourceData() {
    return $this->upload()->data()->ressource(
      'surfer',
      $this,
      array('surfer' => 'surfer_handle'),
      array('surfer' => array('surfer_handle', 'folder_id')),
      array('surfer' => 'enlarge')
    );
  }

  /**
  * Get (and, if necessary, initialize) the ACommunitySurferGalleryUpload object
  *
  * @return ACommunitySurferGalleryUpload $upload
  */
  public function upload(ACommunitySurferGalleryUpload $upload = NULL) {
    if (isset($upload)) {
      $this->_upload = $upload;
    } elseif (is_null($this->_upload)) {
      include_once(dirname(__FILE__).'/../Upload.php');
      $this->_upload = new ACommunitySurferGalleryUpload();
      $this->_upload->parameterGroup($this->paramName);
      $this->_upload->data()->languageId = $this->papaya()->request->languageId;
    }
    return $this->_upload;
  }

  /**
   * Get parsed data
   *
   * @return string $result XML
   */
  public function getParsedData() {
    $this->initializeParams();
    $this->setRessourceData();
    $this->setDefaultData();
    $captionNames = array('caption_dialog_image', 'caption_dialog_button');
    $messageNames = array(
      'message_dialog_input_error', 'message_dialog_error_no_folder',
      'message_dialog_error_no_upload_file', 'message_dialog_error_upload',
      'message_dialog_error_file_extension', 'message_dialog_error_file_type',
      'message_dialog_error_media_db'
    );
    $this->upload()->data()->setPluginData(
      $this->data, $captionNames, $messageNames
    );
    return $this->upload()->getXml();
  }
}