<?php
/**
 * Advanced community surfer gallery folder dialog
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
 * Advanced surfer gallery folder dialog
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityUiContentSurferGalleryFolderDialog 
  extends PapayaUiControlCommandDialogDatabaseRecord {

  /**
  * Comments data
  * @var ACommunitySurferGalleryUploadData
  */
  protected $_data = NULL;
 
  /**
  * Current error message.
  * @var string
  */
  protected $_errorMessage = NULL;
  
  /**
   * New folder id on save
   * @var integer
   */
  protected $_newFolderId = NULL;

  /**
   * Get/set surfer gallery data
   *
   * @param ACommunitySurferGalleryUploadData $data
   * @return ACommunitySurferGalleryUploadData
   */
  public function data(ACommunitySurferGalleryUploadData $data = NULL) {
    if (isset($data)) {
      $this->_data = $data;
    }
    return $this->_data;
  }
  
  /**
  * Get/set error message
  * @var string $errorMessage
  */
  public function errorMessage($errorMessage = NULL) {
    if (isset($errorMessage)) {
      PapayaUtilConstraints::assertString($errorMessage);
      $this->_errorMessage = $errorMessage;
    }
    return $this->_errorMessage;
  }
  
  /**
  * Create dialog
  *
  * @see PapayaUiControlCommandDialog::createDialog()
  * @return PapayaUiDialog
  */
  public function createDialog() {
    $buttonCaption = $this->data()->captions['dialog_button'];

    $dialog = new PapayaUiDialogDatabaseSave($this->record());
    $dialog->callbacks()->onBeforeSave = array($this, 'callbackBeforeSaveRecord');

    $dialog->papaya($this->papaya());
    $dialog->parameterGroup($this->parameterGroup());
    $dialog->parameters($this->parameters());
    $dialog->action($this->data()->reference()->getRelative());
    $dialog->hiddenFields()->merge(
      array('command' => 'add_folder')
    );
    $dialog->caption = NULL;
    
    $dialog->fields[] = $field = new PapayaUiDialogFieldInput(
      $this->data()->captions['dialog_folder_name'],
      'folder_name'
    );
    $field->setMandatory(TRUE);
    $field->setId('dialogGalleryFolderName');
    $dialog->buttons[] = new PapayaUiDialogButtonSubmit($buttonCaption);
    
    $this->callbacks()->onExecuteFailed = array($this, 'callbackShowError');
    return $dialog;
  }
  
  /**
  * Callback before save record in PapayaUiDialogDatabaseSave
  * 
  * @param object $context
  * @param object $record
  */
  public function callbackBeforeSaveRecord($context, $record) {
    $ressource = $this->data()->ressource();
    $parentFolderId = $this->data()->getBaseFolderId();
    $languageId = $this->data()->languageId;
    $parentFolder = $this->data()->mediaDBEdit()->getFolder($parentFolderId);
    $folderName = $this->parameters()->get('folder_name', NULL);
    if (!empty($folderName) && !empty($parentFolder[$languageId])) {
      $newFolderId = $this->data()->mediaDBEdit()->addFolder(
        $parentFolder[$languageId]['folder_id'], 
        $parentFolder[$languageId]['parent_path'].$parentFolder[$languageId]['folder_id'].';', 
        $parentFolder[$languageId]['permission_mode']
      );
      if (!empty($newFolderId)) {
        $this->data()->mediaDBEdit()->addFolderTranslation(
          $newFolderId, $languageId, $folderName
        );
        $this->_newFolderId = $newFolderId;
        $record->assign(
          array(
            'surfer_id' => $ressource['id'],
            'parent_folder_id' => $parentFolderId,
            'folder_id' => $newFolderId
          )
        );
        return TRUE;
      } else {
        $this->errorMessage($this->data()->messages['dialog_error_add_folder']);
      }
    } else {
      $this->errorMessage($this->data()->messages['dialog_error_folder_data']);
    }
    return FALSE;
  }
  
  /**
  * Show error message
  *
  * @param object $context
  * @param PapayaUiDialog $dialog
  */
  public function callbackShowError($context, $dialog) {
    if ($this->_newFolderId > 0) {
      $this->data()->mediaDBEdit()->deleteFolder($this->_newFolderId);
    }
    $this->errorMessage(
      sprintf(
        $this->data()->messages['dialog_input_error'],
        implode(', ', $dialog->errors()->getSourceCaptions())
      )
    );
  }
}
