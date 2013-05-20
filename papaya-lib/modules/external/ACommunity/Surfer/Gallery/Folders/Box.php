<?php
/**
 * Advanced community surfer gallery folders box
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
 * Advanced community surfer folders upload box
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunitySurferGalleryFoldersBox extends base_actionbox {
  
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
    'caption_base_folder' => array(
      'Base Folder', 'isNoHTML', TRUE, 'input', 200, '', 'Base'
    ),
    'caption_add_folder' => array(
      'Add Folder', 'isNoHTML', TRUE, 'input', 200, '', 'Add'
    ),
    'caption_delete_folder' => array(
      'Delete Folder', 'isNoHTML', TRUE, 'input', 200, '', 'Delete'
    ),
    'Dialog Captions',
    'caption_dialog_button' => array(
      'Button', 'isNoHTML', TRUE, 'input', 200, '', 'Add'
    ),
    'caption_dialog_folder_name' => array(
      'Folder Name', 'isNoHTML', TRUE, 'input', 200, '', 'Folder name'
    ),
    'Messages',
    'message_dialog_input_error' => array(
      'Dialog Input Error', 'isNoHTML', TRUE, 'input', 200, '', 
      'Invalid input. Please check the field(s) "%s".'
    ),
    'message_dialog_error_folder_data' => array(
      'Dialog Error Folder Data', 'isNoHTML', TRUE, 'input', 200, '', 
      'Missing some folder data to proceed.'
    ),
    'message_dialog_error_add_folder' => array(
      'Dialog Error Add Folder', 'isNoHTML', TRUE, 'input', 200, '', 
      'Could not add folder.'
    )
  );
  
  /**
   * Gallery folders object
   * @var ACommunitySurferGalleryFolders
   */
  protected $_folders = NULL;
  
  /**
   * Set ressource data to get surfer
   */
  public function setRessourceData() {
    if (!empty($this->parentObj->moduleObj->paramName)) {
      $parameters = $this->papaya()->request->getParameterGroup(
        $this->parentObj->moduleObj->paramName
      );
      if (isset($parameters['surfer_handle'])) {
        $this->folders()->data()->ressource('surfer', $parameters['surfer_handle']);  
        $this->folders()->data()->ressourceParameters(
          $this->parentObj->moduleObj->paramName,
          array('surfer_handle' => $parameters['surfer_handle'])
        );
      }
    }
  }
  
  /**
  * Get (and, if necessary, initialize) the ACommunitySurferGalleryFolders object 
  * 
  * @return ACommunitySurferGalleryFolders $folders
  */
  public function folders(ACommunitySurferGalleryFolders $folders = NULL) {
    if (isset($folders)) {
      $this->_folders = $folders;
    } elseif (is_null($this->_folders)) {
      include_once(dirname(__FILE__).'/../Folders.php');
      $this->_folders = new ACommunitySurferGalleryFolders();
      $this->_folders->parameterGroup($this->paramName);
      $captionNames = array(
        'caption_base_folder', 'caption_add_folder', 'caption_delete_folder',
        'caption_dialog_button', 'caption_dialog_folder_name'
      );
      $messageNames = array(
        'message_dialog_input_error',
        'message_dialog_error_folder_data',
        'message_dialog_error_add_folder'
      );
      $this->_folders->data()->setPluginData(
        $this->data, $captionNames, $messageNames
      );
      $this->_folders->data()->languageId = $this->papaya()->request->languageId;
    }
    return $this->_folders;
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
    return $this->folders()->getXml();
  }
  
}
