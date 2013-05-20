<?php
/**
 * Advanced community notification handler
 *
 * This class offers methods to delete and modify community data on surfer deletion
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
 * Advanced community notification handler
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityNotificationHandler extends PapayaObject {

  /**
   * Notification setting database record
   * @var ACommunityContentNotificationSetting
   */
  protected $_setting = NULL;

  /**
   * Notification translation database record
   * @var ACommunityContentNotificationTranslation
   */
  protected $_translation = NULL;

  /**
   * Message database record
   * @var ACommunityContentMessages
   */
  protected $_message = NULL;

  /**
   * Message surfer database record
   * @var ACommunityContentMessageSurfer
   */
  protected $_messageSurfer = NULL;

  /**
   * Community connector
   * @var connector_surfers
   */
  protected $_communityConnector = NULL;

  /**
   * Advanced Community connector
   * @var ACommunityConnector
   */
  protected $_acommunityConnector = NULL;

  /**
   * Notify surfer by notifcation name with additional parameters
   *
   * Use surfer parameters to set surfer names in simple template placeholders:
   * - 'recipient_surfer' => Surfer ID of recipient surfer, example "John you have new messages."
   * - 'context_surfer' => Surfer ID of context surfer, example "Sebastian moved your image to trash."
   *
   * Use additional parameters to set more data in simple template placeholders.
   *
   * @param string $notificationName
   * @param string $recipientId Surfer ID of recipient surfer
   * @param array $parameters
   */
  public function notify($notificationName, $recipientId, $parameters = array()) {
    $simpleTemplate = new base_simpletemplate();

    $translation = clone $this->translation();
    $translation->load(array('name' => $notificationName));
    if ($translation['notification_id'] > 0) {

      $setting = clone $this->setting();
      $setting->load(array('surfer_id' => $recipientId, 'notification_id' => $translation['notification_id']));
      if (!($setting['notification_id'] > 0)) {
        $defaultSetting = $this->acommunityConnector()->getNotificationDefaultSetting();
        $setting = clone $this->setting();
        $setting->assign(
          array(
            'notification_id' => $translation['notification_id'],
            'surfer_id' => $recipientId,
            'by_message' => $defaultSetting['by_message'],
            'by_email' => $defaultSetting['by_email']
          )
        );
        $setting->save();
      }
      if ($setting['notification_id'] > 0) {
        if (isset($parameters['recipient_surfer'])) {
          $parameters['recipient_surfer'] =
            $this->_getSurferName($this->communityConnector()->getNameById($parameters['recipient_surfer']));
        }
        if (isset($parameters['context_surfer'])) {
          $parameters['context_surfer'] =
            $this->_getSurferName($this->communityConnector()->getNameById($parameters['context_surfer']));
        }
        $text = $simpleTemplate->parse(
          $translation['text'], $parameters
        );
        $title = $translation['title'];
        if ($setting['by_email'] == 1) {
          $this->_sendEmail($recipientId, $title, $text);
        }
        if ($setting['by_message'] == 1) {
          $this->_sendMessage($recipientId, $title, $text);
        }
      }
    }
  }

  /**
   * Send a system message to surfer by id
   *
   * @param string $surferId
   * @param string $title
   * @param string $text
   */
  protected function _sendMessage($surferId, $title, $text) {
    include_once(dirname(__FILE__).'/../Filter/Text/Extended.php');
    $filter = new ACommunityFilterTextExtended(
      PapayaFilterText::ALLOW_SPACES|PapayaFilterText::ALLOW_DIGITS|PapayaFilterText::ALLOW_LINES,
      'surfer_'.$surferId
    );
    $text = $filter->filter($text);
    $text = $title.': '.$text;
    // assign / save message data
    $message = $this->message();
    $message->assign(
      array(
        'sender' => 'system', 'recipient' => $surferId, 'time' => time(), 'text' => $text
      )
    );
    $message->save();
  }

  /**
   * Send a system email to surfer by id
   *
   * @param string $surferId
   * @param string $title
   * @param string $text
   */
  protected function _sendEmail($surferId, $title, $text) {
    $surfer = $this->communityConnector()->getNameById($surferId);
    $surferEmail = $this->communityConnector()->getMailById($surferId);
    if (!empty($surfer) && !empty($surferEmail)) {
      $sender = $this->acommunityConnector()->getNotificationSender();
      include_once(PAPAYA_INCLUDE_PATH.'system/sys_email.php');
      $email = new email();
      $email->setSender($sender['email'], $sender['name']);
      $email->addAddress($surferEmail, $this->_getSurferName($surfer));
      $email->setSubject($title);
      $email->setBody($text);
      $email->send();
    }
  }

  /**
   * Get surfer name by surfer data and display mode option
   *
   * @param array $surfer
   * @return string
   */
  protected function _getSurferName($surfer) {
    $surferName = NULL;
    $displayModeSurferName = $this->acommunityConnector()->getDisplayModeSurferName();
    switch ($displayModeSurferName) {
      case 'all':
        $surferName = sprintf(
          "%s '%s' %s",
          $surfer['surfer_givenname'], $surfer['surfer_handle'], $surfer['surfer_surname']
        );
        break;
      case 'names':
        $surferName = sprintf("%s %s", $surfer['surfer_givenname'], $surfer['surfer_surname']);
        break;
      case 'handle':
        $surferName = $surfer['surfer_handle'];
        break;
      case 'givenname':
        $surferName = $surfer['surfer_givenname'];
        break;
      case 'surname':
        $surferName = $surfer['surfer_surname'];
        break;
    }
    return $surferName;
  }

  /**
  * Access to notification setting database record data
  *
  * @param ACommunityContentNotificationSetting $setting
  * @return ACommunityContentNotificationSetting
  */
  public function setting(ACommunityContentNotificationSetting $setting = NULL) {
    if (isset($setting)) {
      $this->_setting = $setting;
    } elseif (is_null($this->_setting)) {
      include_once(dirname(__FILE__).'/../Content/Notification/Setting.php');
      $this->_setting = new ACommunityContentNotificationSetting();
      $this->_setting->papaya($this->papaya());
    }
    return $this->_setting;
  }

  /**
  * Access to notification translation database record data
  *
  * @param ACommunityContentNotificationTranslation $translation
  * @return ACommunityContentNotificationTranslation
  */
  public function translation(ACommunityContentNotificationTranslation $translation = NULL) {
    if (isset($translation)) {
      $this->_translation = $translation;
    } elseif (is_null($this->_translation)) {
      include_once(dirname(__FILE__).'/../Content/Notification/Translation.php');
      $this->_translation = new ACommunityContentNotificationTranslation();
      $this->_translation->papaya($this->papaya());
    }
    return $this->_translation;
  }

  /**
  * Access to message database record data
  *
  * @param ACommunityContentMessage $message
  * @return ACommunityContentMessage
  */
  public function message(ACommunityContentMessage $message = NULL) {
    if (isset($message)) {
      $this->_message = $message;
    } elseif (is_null($this->_message)) {
      include_once(dirname(__FILE__).'/../Content/Message.php');
      $this->_message = new ACommunityContentMessage();
      $this->_message->papaya($this->papaya());
    }
    return $this->_message;
  }

  /**
   * Get/set community connector
   *
   * @param object $connector
   * @return object
   */
  public function communityConnector(connector_surfers $connector = NULL) {
    if (isset($connector)) {
      $this->_communityConnector = $connector;
    } elseif (is_null($this->_communityConnector)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
      $this->_communityConnector = base_pluginloader::getPluginInstance(
        '06648c9c955e1a0e06a7bd381748c4e4', $this
      );
    }
    return $this->_communityConnector;
  }

  /**
   * Get/set advanced community connector
   *
   * @param object $connector
   * @return object
   */
  public function acommunityConnector(ACommunityConnector $connector = NULL) {
    if (isset($connector)) {
      $this->_acommunityConnector = $connector;
    } elseif (is_null($this->_acommunityConnector)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
      $this->_acommunityConnector = base_pluginloader::getPluginInstance(
        '0badeb14ea2d41d5bcfd289e9d190534', $this
      );
    }
    return $this->_acommunityConnector;
  }

}