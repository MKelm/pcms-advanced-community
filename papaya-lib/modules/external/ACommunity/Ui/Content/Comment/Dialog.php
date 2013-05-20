<?php
/**
 * Advanced community comment dialog
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
 * Advanced community comment dialog
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityUiContentCommentDialog 
  extends PapayaUiControlCommandDialogDatabaseRecord {

  /**
  * Comments data
  * @var ACommunityCommentsData
  */
  protected $_data = NULL;
 
  /**
  * Current error message.
  * @var string
  */
  protected $_errorMessage = NULL;

  /**
   * Get/set comments data
   *
   * @param ACommunityCommentsData $data
   * @return ACommunityCommentsData
   */
  public function data(ACommunityCommentsData $data = NULL) {
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
      array(
        'command' => 'reply',
        'comment_id' => $this->parameters()->get('comment_id', 0)
      )
    );
    $dialog->caption = NULL;

    $dialog->fields[] = $field = new PapayaUiDialogFieldTextarea(
      $this->data()->captions['dialog_text'],
      'text',
      3,
      '',
      new PapayaFilterText(
        PapayaFilterText::ALLOW_SPACES|PapayaFilterText::ALLOW_DIGITS|PapayaFilterText::ALLOW_LINES
      )
    );
    $field->setMandatory(TRUE);
    $field->setId('dialogCommentText');
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
    $commentId = (int)$this->parameters()->get('comment_id', 0);
    $command = $this->parameters()->get('command', '');
    $ressourceData = $this->data()->ressource();
    $record->assign(
      array(
        'language_id' => $this->data()->languageId,
        'parent_id' => $commentId,
        'surfer_id' => $this->data()->surferId(),
        'ressource_id' => $ressourceData['id'],
        'ressource_type' => $ressourceData['type'],
        'time' => time(),
        'votes_score' => 0,
        'deleted_surfer' => 0
      )
    );
    if ($command == 'reply' && $commentId > 0) {
      $this->parameters()->set('comment_id', 0);
      $this->parameters()->set('reset_dialog', 1);
    }
    return TRUE;
  }
  
  /**
  * Show error message
  *
  * @param object $context
  * @param PapayaUiDialog $dialog
  */
  public function callbackShowError($context, $dialog) {
    $this->errorMessage(
      sprintf(
        $this->data()->messages['message_dialog_input_error'],
        implode(', ', $dialog->errors()->getSourceCaptions())
      )
    );
  }
}
