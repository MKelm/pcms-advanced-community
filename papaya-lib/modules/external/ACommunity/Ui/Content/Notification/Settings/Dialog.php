<?php
/**
 * Advanced community notification settings dialog
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
 * Advanced community notification settings dialog
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityUiContentNotificationSettingsDialog extends PapayaUiControlCommandDialog {

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
   * Get/set surfer gallery data
   *
   * @param ACommunityNotificationSettingsData $data
   * @return ACommunityNotificationSettingsData
   */
  public function data(ACommunityNotificationSettingsData $data = NULL) {
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

    $dialog = new PapayaUiDialog();

    $dialog->papaya($this->papaya());
    $dialog->parameterGroup($this->parameterGroup());

    $confirmation = $this->parameters()->get('confirmation', NULL);
    if (empty($confirmation)) {
      foreach ($this->data()->settingFields as $name => $field) {
        $selectedData = array();
        foreach ($field['data'] as $dataName => $dataValue) {
          if ($dataName !== 'notification_id' && $dataValue == 1) {
            $selectedData[] = $dataName;
          }
        }
        $this->parameters()->set('checkbox_'.$name, $selectedData);
      }
    }

    $dialog->parameters($this->parameters());
    $dialog->action($this->data()->reference()->getRelative());
    $dialog->hiddenFields()->merge(
      array('command' => 'change')
    );
    $dialog->caption = NULL;

    foreach ($this->data()->settingFields as $name => $field) {
      $dialog->fields[] = $currentField = new PapayaUiDialogFieldSelectCheckboxes(
        $field['caption'], 'checkbox_'.$name, $field['values'], FALSE
      );
      $currentField->setId($field['id']);
    }

    $dialog->buttons[] = new PapayaUiDialogButtonSubmit($buttonCaption);

    $this->callbacks()->onExecuteSuccessful = array($this, 'callbackExecuteSuccessful');
    $this->callbacks()->onExecuteFailed = array($this, 'callbackShowError');
    return $dialog;
  }


  /**
  * Actions on execute successful
  *
  * @param object $context
  * @param PapayaUiDialog $dialog
  */
  public function callbackExecuteSuccessful($context, $dialog) {
    foreach ($dialog->data() as $name => $value) {
      if (strpos($name, 'checkbox_') === 0)  {
        $name = str_replace('checkbox_', '', $name);
        foreach ($this->data()->settingFields[$name]['data'] as $dataName => $dataValue) {
          if ($dataName !== 'notification_id') {
            if (in_array($dataName, $value)) {
              $this->data()->settingFields[$name]['data'][$dataName] = 1;
            } else {
              $this->data()->settingFields[$name]['data'][$dataName] = 0;
            }
          }
        }
      }
    }
    $this->data()->saveSettingFields();
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