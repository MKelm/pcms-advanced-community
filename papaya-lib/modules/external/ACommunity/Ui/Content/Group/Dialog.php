<?php
/**
 * Advanced community group dialog
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
 * Advanced image group dialog
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityUiContentGroupDialog
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
   * Name of image field
   * @var string
   */
  protected $_imageFieldName = 'image_upload';

  /**
   * Flag for handle change
   * @var boolean
   */
  protected $_handleChange = NULL;

  /**
   * Get/set image gallery folders data
   *
   * @param ACommunityGroupsData $data
   * @return ACommunityGroupsData
   */
  public function data(ACommunityGroupsData $data = NULL) {
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
    $command = $this->parameters()->get('command', '');
    $buttonCaption = $this->data()->captions['dialog_button_add'];
    if ($command == 'edit_group') {
      $groupHandle = $this->parameters()->get('group_handle', '');
      if (!empty($groupHandle)) {
        $record = clone $this->record();
        $record->load(array('handle' => $groupHandle));
        $dom = new PapayaXmlDocument();
        $dom->appendElement('text');
        $dom->documentElement->appendXml($record->description);
        $xml = '';
        foreach ($dom->documentElement->childNodes as $node) {
          if ($node->tagName == 'text-raw') {
            $xml .= $node->nodeValue;
          }
        }
        $record->description = $xml;
        $buttonCaption = $this->data()->captions['dialog_button_edit'];
        $handle = $this->parameters()->get('handle');
        if (!empty($handle) && strtolower($record->handle) != strtolower($handle)) {
          $this->_handleChange = TRUE;
        } else {
          $this->_handleChange = FALSE;
        }
        $dialog = new PapayaUiDialogDatabaseSave($record);
      }
    } else {
      $this->_handleChange = TRUE;
      $dialog = new PapayaUiDialogDatabaseSave(clone $this->record());
    }

    $dialog->callbacks()->onBeforeSave = array($this, 'callbackBeforeSaveRecord');

    $dialog->papaya($this->papaya());
    $dialog->parameterGroup($this->parameterGroup());
    $dialog->parameters($this->parameters());
    $dialog->hiddenFields()->merge(
      array(
        'command' => $command,
        'group_handle' => $this->data()->owner->parameters()->get('group_handle', NULL)
      )
    );
    $dialog->action($this->data()->reference()->getRelative());
    $dialog->caption = NULL;

    $dialog->fields[] = $field = new PapayaUiDialogFieldSelectRadio(
      $this->data()->captions['dialog_is_public'],
      'public',
      array(
        1 => $this->data()->captions['dialog_is_public_yes'],
        0 => $this->data()->captions['dialog_is_public_no']
      ),
      TRUE
    );
    $field->setId('dialogGroupIsPublic');
    if ($command != 'edit_group' && NULL === $this->parameters()->get('public', NULL)) {
      $dialog->data()->set('public', 1);
    }

    $dialog->fields[] = $field = new PapayaUiDialogFieldInput(
      $this->data()->captions['dialog_handle'],
      'handle',
      200,
      NULL,
      new PapayaFilterLogicalAnd(
        new PapayaFilterText(PapayaFilterText::ALLOW_DIGITS),
        new PapayaFilterNotEmpty()
      )
    );
    $field->setMandatory(TRUE);
    $field->setId('dialogGroupHandle');

    $dialog->fields[] = $field = new PapayaUiDialogFieldInput(
      $this->data()->captions['dialog_title'],
      'title',
      200,
      NULL,
      new PapayaFilterLogicalAnd(
        new PapayaFilterText(PapayaFilterText::ALLOW_SPACES|PapayaFilterText::ALLOW_DIGITS),
        new PapayaFilterNotEmpty()
      )
    );
    $field->setMandatory(TRUE);
    $field->setId('dialogGroupTitle');

    include_once(dirname(__FILE__).'/../../../Filter/Text/Extended.php');
    $dialog->fields[] = $field = new PapayaUiDialogFieldTextarea(
      $this->data()->captions['dialog_description'],
      'description',
      3,
      '',
      new ACommunityFilterTextExtended(
        PapayaFilterText::ALLOW_SPACES|PapayaFilterText::ALLOW_DIGITS|PapayaFilterText::ALLOW_LINES
      )
    );
    $field->setMandatory(TRUE);
    $field->setId('dialogGroupDescription');

    include_once(dirname(__FILE__).'/../../Dialog/Field/Input/File.php');
    $dialog->fields[] = $field = new ACommunityUiDialogFieldInputFile(
      $this->data()->captions['dialog_image'],
      $this->_imageFieldName,
      FALSE
    );
    $field->setId('dialogGroupImage');

    $dialog->buttons[] = new PapayaUiDialogButtonSubmit($buttonCaption);

    $this->callbacks()->onExecuteSuccessful = array($this, 'callbackExecuteSuccessful');
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
    $folderId = $this->data()->groupImagesFolderId;
    $parameterGroup = $this->data()->owner->parameterGroup();

    if (isset($_FILES[$parameterGroup]) &&
        !empty($_FILES[$parameterGroup]['name'][$this->_imageFieldName]) &&
        !empty($_FILES[$parameterGroup]['tmp_name'][$this->_imageFieldName])) {
      if (!empty($folderId)) {

        if (!($_FILES[$parameterGroup]['error'][$this->_imageFieldName] > 0)) {

          $allowedExtensions = array('gif', 'jpeg', 'jpg', 'png');
          $extension = strtolower(
            end(explode('.', $_FILES[$parameterGroup]['name'][$this->_imageFieldName]))
          );
          if (in_array($extension, $allowedExtensions)) {

            $allowedTypes = array(
              "image/gif", "image/jpeg", "image/jpg", "image/pjpeg", "image/x-png", "image/png"
            );
            $type = $_FILES[$parameterGroup]['type'][$this->_imageFieldName];

            if (in_array($type, $allowedTypes)) {
              $mediaDBEdit = $this->data()->mediaDBEdit();
              $mediaId = $mediaDBEdit->addFile(
                $_FILES[$parameterGroup]['tmp_name'][$this->_imageFieldName],
                $_FILES[$parameterGroup]['name'][$this->_imageFieldName],
                $folderId,
                $this->data()->currentSurferId()
              );
              if (empty($mediaId)) {
                $error = 'dialog_error_media_db';
              }
            } else {
              $error = 'dialog_error_file_type';
            }
          } else {
            $error = 'dialog_error_file_extension';
          }
        } else {
          $error = 'dialog_error_upload';
        }
      } else {
        $error = 'dialog_error_no_folder';
      }
    }
    if (empty($error)) {
      if ($this->_handleChange) {
        $recordForHandleCheck = clone $this->record();
        $recordForHandleCheck->load(array('handle' => $record['handle']));
        if (!empty($recordForHandleCheck['id'])) {
          $this->_errorMessage = $this->data()->messages['dialog_error_handle_duplicate'];
        }
      }
      if (empty($this->_errorMessage)) {
        if (empty($record['id'])) {
          $record->assign(
            array(
              'owner' => $this->data()->currentSurferId(),
              'time' => time(),
              'image' => isset($mediaId) ? $mediaId : NULL
            )
          );
          return TRUE;
        } else {
          if (!empty($mediaId)) {
            $record->assign(array('image' => $mediaId));
          }
          return TRUE;
        }
      }
    } else {
      $this->_errorMessage = $this->data()->messages[$error];
    }
    return FALSE;
  }

  /**
  * Actions on execute successful
  *
  * @param object $context
  * @param PapayaUiDialog $dialog
  */
  public function callbackExecuteSuccessful($context, $dialog) {
    $lastChangeTime = time();
    if ($this->data()->showOwnGroups()) {
      $this->data()->setLastChangeTime('groups:surfer_'.$this->data()->currentSurferId());
    }
    $this->data()->setLastChangeTime('groups');
    $this->data()->owner->parameters()->set('remove_dialog', 1);
  }

  /**
  * Show error message
  *
  * @param object $context
  * @param PapayaUiDialog $dialog
  */
  public function callbackShowError($context, $dialog) {
    if (empty($this->_errorMessage)) {
      $this->errorMessage(
        sprintf(
          $this->data()->messages['dialog_input_error'],
          implode(', ', $dialog->errors()->getSourceCaptions())
        )
      );
    }
  }
}