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
class ACommunitySurferGalleryUploadBox extends base_actionbox {
  
  /**
   * Parameter prefix name
   * @var string $paramName
   */
  public $paramName = 'acg';
  
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
   * Set ressource data to get surfer
   */
  public function setRessourceData() {
    if (!empty($this->parentObj->moduleObj->paramName)) {
      $parameters = $this->papaya()->request->getParameterGroup(
        $this->parentObj->moduleObj->paramName
      );
      if (isset($parameters['surfer_handle']) && !isset($parameters['img'])) {
        $this->upload()->data()->ressource('surfer', $parameters['surfer_handle']);  
        $this->upload()->data()->ressourceParameters(
          $this->parentObj->moduleObj->paramName,
          array('surfer_handle' => $parameters['surfer_handle'])
        );
      }
    }
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
      $this->_upload->data()->setPluginData($this->data);
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
    $this->setDefaultData();
    $this->initializeParams();
    $this->setRessourceData();
    return $this->upload()->getXml();
  }
}
