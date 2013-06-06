<?php
/**
 * Advanced community notification settings data class to handle all sorts of related data
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
 * Base ui content data object
 */
require_once(dirname(__FILE__).'/../../Ui/Content/Data.php');

/**
 * Advanced community notification settings data class to handle all sorts of related data
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityNotificationSettingsData extends ACommunityUiContentData {

  /**
   * Page title
   * @var string
   */
  public $pageTitle = NULL;

  /**
   * Setting database record
   * @var ACommunityContentNotificationSetting
   */
  protected $_setting = NULL;

  /**
   * Setting database record
   * @var ACommunityContentNotificationSettings
   */
  protected $_settings = NULL;

  /**
   * Current setting fields with data for dialog
   * @var array
   */
  public $settingFields = array();

  /**
   * Set data by plugin object
   *
   * @param array $data
   * @param array $captionNames
   * @param array $messageNames
   */
  public function setPluginData($data, $captionNames = array(), $messageNames = array()) {
    $this->pageTitle = $data['page_title'];
    parent::setPluginData($data, $captionNames, $messageNames);
  }

  /**
   * Initialize settings and setting fields with data
   */
  public function initializeSettings() {
    $ressource = $this->owner->ressource();
    $this->settings()->load(
      array('surfer_id' => $ressource->id, 'language_id' => $this->languageId)
    );
    $settings = $this->settings()->toArray();
    $defaultSetting = $this->owner->acommunityConnector()->getNotificationDefaultSetting();
    $changes = FALSE;
    foreach ($settings as $notificationId => $notificationSettings) {
      if (empty($notificationSettings['surfer_id'])) {
        $setting = clone $this->setting();
        $setting->assign(
          array(
            'surfer_id' => $ressource->id,
            'notification_id' => $notificationSettings['notification_id'],
            'by_message' => $defaultSetting['by_message'],
            'by_email' => $defaultSetting['by_email']
          )
        );
        $setting->save();
        $changes = TRUE;
      }
      $name = str_replace('-', '_', $notificationSettings['notification_name']);
      $this->settingFields[$name] =
        array(
          'id' => $this->_getNotificationFieldIdByName($name),
          'caption' => $notificationSettings['notification_title'],
          'data' => array(
            'notification_id' => $notificationSettings['notification_id'],
            'by_message' => $notificationSettings['by_message'],
            'by_email' => $notificationSettings['by_email']
          ),
          'values' => array(
            'by_message' => $this->captions['dialog_checkbox_by_message'],
            'by_email' => $this->captions['dialog_checkbox_by_email']
          )
        );
    }
    if ($changes) {
      $this->settings()->load(
        array('surfer_id' => $ressource->id, 'language_id' => $this->languageId)
      );
    }
  }

  /**
   * Save data in setting fields after dialog execute successful
   */
  public function saveSettingFields() {
    $ressource = $this->owner->ressource();
    foreach ($this->settingFields as $name => $field) {
      $setting = clone $this->setting();
      $setting->assign(
        array(
          'surfer_id' => $ressource->id,
          'notification_id' => (int)$field['data']['notification_id'],
          'by_message' => (int)$field['data']['by_message'],
          'by_email' => (int)$field['data']['by_email']
        )
      );
      $setting->save();
    }
  }

  /**
   * Get a notification field id by name
   *
   * @param string $name
   * @return string $id
   */
  protected function _getNotificationFieldIdByName($name) {
    $id = 'dialogNotificationSetting';
    for ($i = 0; $i < strlen($name); $i++) {
      if (substr($name, $i, 1) != '_') {
        if ($i == 0 || substr($name, $i - 1, 1) == '_') {
          $id .= strtoupper(substr($name, $i, 1));
        } else {
          $id .= substr($name, $i, 1);
        }
      }
    }
    return $id;
  }

  /**
  * Access to setting database record data
  *
  * @param ACommunityContentNotificationSetting $setting
  * @return ACommunityContentNotificationSetting
  */
  public function setting(ACommunityContentNotificationSetting $setting = NULL) {
    if (isset($setting)) {
      $this->_setting = $setting;
    } elseif (is_null($this->_setting)) {
      include_once(dirname(__FILE__).'/../../Content/Notification/Setting.php');
      $this->_setting = new ACommunityContentNotificationSetting();
      $this->_setting->papaya($this->papaya());
    }
    return $this->_setting;
  }

  /**
  * Access to settings database records data
  *
  * @param ACommunityContentNotificationSettings $settings
  * @return ACommunityContentNotificationSettings
  */
  public function settings(ACommunityContentNotificationSettings $settings = NULL) {
    if (isset($settings)) {
      $this->_settings = $settings;
    } elseif (is_null($this->_settings)) {
      include_once(dirname(__FILE__).'/../../Content/Notification/Settings.php');
      $this->_settings = new ACommunityContentNotificationSettings();
      $this->_settings->papaya($this->papaya());
    }
    return $this->_settings;
  }

}