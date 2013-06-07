<?php
/**
 * Advanced community message dialog
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
 * Advanced community message dialog
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityUiContentMessageDialog
  extends PapayaUiControlCommandDialogDatabaseRecord {

  /**
  * Messages data
  * @var ACommunityMessagesData
  */
  protected $_data = NULL;

  /**
  * Current error message.
  * @var string
  */
  protected $_errorMessage = NULL;

  /**
   * Get/set messages data
   *
   * @param ACommunityMessagesData $data
   * @return ACommunityMessagesData
   */
  public function data(ACommunityMessagesData $data = NULL) {
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
    $ressource = $this->data()->owner->ressource();
    $dialog->hiddenFields()->merge(
      array(
        'command' => 'reply',
        'image_handler_url' => $this->data()->owner->acommunityConnector()->getCommentsPageLink(
          $this->data()->languageId, $ressource->type, $ressource->id,
          array('request' => 'thumbnail_link', 'url' => '{URL}')
        )
      )
    );
    $dialog->caption = NULL;

    $ressource = $this->data()->owner->ressource();
    include_once(dirname(__FILE__).'/../../../Filter/Text/Extended.php');
    $dialog->fields[] = $field = new PapayaUiDialogFieldTextarea(
      $this->data()->captions['dialog_text'],
      'text',
      3,
      '',
      new ACommunityFilterTextExtended(
        PapayaFilterText::ALLOW_SPACES|PapayaFilterText::ALLOW_DIGITS|PapayaFilterText::ALLOW_LINES,
        'messages:surfer_'.$this->data()->currentSurferId().':surfer_'.$ressource->id,
        $this->papaya()->session
      )
    );
    $field->setMandatory(TRUE);
    $field->setId('dialogMessageText');
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
    $ressource = $this->data()->owner->ressource();
    // save message surfer bi-directional to detect message conversation correctly
    $messageSurfer = clone $this->data()->messageSurfer();
    $messageSurfer->assign(
      array('surfer_id' => $this->data()->currentSurferId(), 'contact_surfer_id' => $ressource->id)
    );
    $messageSurfer->save();
    $messageSurfer = clone $this->data()->messageSurfer();
    $messageSurfer->assign(
      array('surfer_id' => $ressource->id, 'contact_surfer_id' => $this->data()->currentSurferId())
    );
    $messageSurfer->save();
    // assign missing message data
    $record->assign(
      array(
        'sender' => $this->data()->currentSurferId(),
        'recipient' => $ressource->id,
        'time' => time()
      )
    );
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
        $this->data()->messages['dialog_input_error'],
        implode(', ', $dialog->errors()->getSourceCaptions())
      )
    );
  }
}