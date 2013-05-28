<?php
/**
 * Advanced community image gallery folders box
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
 * Advanced community image gallery folders box
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityImageGalleryFoldersBox extends base_actionbox implements PapayaPluginCacheable {

  /**
   * Parameter prefix name
   * @var string $paramName
   */
  public $paramName = 'acig';

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
   * Cache definition
   * @var PapayaCacheIdentifierDefinition
   */
  protected $_cacheDefinition = NULL;

  /**
   * Define the cache definition for output.
   *
   * @see PapayaPluginCacheable::cacheable()
   * @param PapayaCacheIdentifierDefinition $definition
   * @return PapayaCacheIdentifierDefinition
   */
  public function cacheable(PapayaCacheIdentifierDefinition $definition = NULL) {
    if (isset($definition)) {
      $this->_cacheDefinition = $definition;
    } elseif (NULL == $this->_cacheDefinition) {
      $currentSurferId = !empty($this->papaya()->surfer->surfer['surfer_id']) ?
          $this->papaya()->surfer->surfer['surfer_id'] : NULL;
      $command = $this->folders()->parameters()->get('command', NULL);
      if (!empty($currentSurferId) && $command == 'add_folder') {
        $this->_cacheDefinition = new PapayaCacheIdentifierDefinitionBoolean(FALSE);
      } else {
        $ressource = $this->setRessourceData();
        $definitionValues = array('acommunity_image_gallery_folders', $currentSurferId);
        if (!empty($ressource)) {
          include_once(dirname(__FILE__).'/../../../Cache/Identifier/Values.php');
          $values = new ACommunityCacheIdentifierValues();
          $definitionValues[] = $ressource['type'];
          $definitionValues[] = $ressource['id'];
          if ($ressource['type'] == 'group') {
            $lastChangeRessource = 'group_gallery_folders:group_'.$ressource['id'];
          } else {
            $lastChangeRessource = 'surfer_gallery_folders:surfer_'.$ressource['id'];
          }
          $definitionValues[] = $values->lastChangeTime($lastChangeRessource);
        }
        $this->_cacheDefinition = new PapayaCacheIdentifierDefinitionGroup(
          new PapayaCacheIdentifierDefinitionValues($definitionValues),
          new PapayaCacheIdentifierDefinitionParameters(
            array('command', 'folder_id'), $this->paramName
          )
        );
      }
    }
    return $this->_cacheDefinition;
  }

  /**
   * Set ressource data to get surfer
   */
  public function setRessourceData() {
    $ressourceType = !empty($this->parentObj->moduleObj->params['group_id']) ? 'group' : 'surfer';
    return $this->folders()->data()->ressource(
      $ressourceType,
      $this,
      array('surfer' => 'surfer_handle', 'group' => 'group_id'),
      array('surfer' => 'surfer_handle', 'group' => 'group_id')
    );
  }

  /**
  * Get (and, if necessary, initialize) the ACommunityImageGalleryFolders object
  *
  * @return ACommunityImageGalleryFolders $folders
  */
  public function folders(ACommunityImageGalleryFolders $folders = NULL) {
    if (isset($folders)) {
      $this->_folders = $folders;
    } elseif (is_null($this->_folders)) {
      include_once(dirname(__FILE__).'/../Folders.php');
      $this->_folders = new ACommunityImageGalleryFolders();
      $this->_folders->parameterGroup($this->paramName);
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
    $this->initializeParams();
    $ressource = $this->setRessourceData();
    $this->setDefaultData();
    $captionNames = array(
      'caption_base_folder', 'caption_add_folder', 'caption_delete_folder',
      'caption_dialog_button', 'caption_dialog_folder_name'
    );
    $messageNames = array(
      'message_dialog_input_error',
      'message_dialog_error_folder_data',
      'message_dialog_error_add_folder'
    );
    $this->folders()->data()->setPluginData($this->data, $captionNames, $messageNames);
    return $this->folders()->getXml();
  }
}