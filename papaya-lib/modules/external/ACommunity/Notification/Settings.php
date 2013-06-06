<?php
/**
 * Advanced community notification settings
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
 * Base ui content object
 */
require_once(dirname(__FILE__).'/../Ui/Content.php');

/**
 * Advanced community notification settings
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityNotificationSettings extends ACommunityUiContent {

  /**
   * Ui content message dialog
   * @var ACommunityUiContentNotificationSettingsDialog
   */
  protected $_uiContentSettingsDialog = NULL;

  /**
   * Get/set messages data
   *
   * @param ACommunityMessagesData $data
   * @return ACommunityMessagesData
   */
  public function data(ACommunityMessagesData $data = NULL) {
    if (isset($data)) {
      $this->_data = $data;
    } elseif (is_null($this->_data)) {
      include_once(dirname(__FILE__).'/Settings/Data.php');
      $this->_data = new ACommunityNotificationSettingsData();
      $this->_data->papaya($this->papaya());
      $this->_data->owner = $this;
    }
    return $this->_data;
  }

  /**
  * Create dom node structure of the given object and append it to the given xml
  * element node.
  *
  * @param PapayaXmlElement $parent
  */
  public function appendTo(PapayaXmlElement $parent) {
    $parent->appendElement('title', array(), $this->data()->pageTitle);
    if (isset($this->ressource()->id)) {
      $settings = $parent->appendElement('notification-settings');
      $this->data()->initializeSettings();
      $this->uiContentSettingsDialog()->appendTo($settings);
      $errorMessage = $this->uiContentSettingsDialog()->errorMessage();
      if (!empty($errorMessage)) {
        $settings->appendElement(
          'dialog-message', array('type' => 'error'), $errorMessage
        );
      }
    } else {
      $parent->appendElement(
        'message', array('type' => 'no-login'), $this->data()->messages['no_login']
      );
    }
  }

  /**
  * Access to the ui content notification settings dialog control
  *
  * @param ACommunityUiContentNotificationSettingsDialog $uiContentSettingsDialog
  * @return ACommunityUiContentNotificationSettingsDialog
  */
  public function uiContentSettingsDialog(
           ACommunityUiContentMessageDialog $uiContentSettingsDialog = NULL
         ) {
    if (isset($uiContentSettingsDialog)) {
      $this->_uiContentSettingsDialog = $uiContentSettingsDialog;
    } elseif (is_null($this->_uiContentSettingsDialog)) {
      include_once(dirname(__FILE__).'/../Ui/Content/Notification/Settings/Dialog.php');
      $this->_uiContentSettingsDialog = new ACommunityUiContentNotificationSettingsDialog();
      $this->_uiContentSettingsDialog->data($this->data());
      $this->_uiContentSettingsDialog->parameters($this->parameters());
      $this->_uiContentSettingsDialog->parameterGroup($this->parameterGroup());
    }
    return $this->_uiContentSettingsDialog;
  }

}