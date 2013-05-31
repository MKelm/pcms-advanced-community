<?php
/**
 * Advanced community surfer status data class to handle all sorts of related data
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
 * Advanced community surfer status data class to handle all sorts of related data
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunitySurferStatusData extends ACommunityUiContentData {

  /**
   * Surfer data
   * @var array
   */
  public $surfer = NULL;


  /**
   * Page Id to redirect after logout
   * @var integer
   */
  protected $_logoutRedirectionPageId = NULL;

  /**
   * Set data by plugin object
   *
   * @param array $data
   * @param array $captionNames
   * @param array $messageNames
   */
  public function setPluginData($data, $captionNames = array(), $messageNames = array()) {
    $this->_surferAvatarSize = (int)$data['avatar_size'];
    $this->_surferAvatarResizeMode = $data['avatar_resize_mode'];
    $this->_logoutRedirectionPageId = $data['logout_redirection_page_id'];
    parent::setPluginData($data, $captionNames, $messageNames);
  }

  /**
   * Intitialize surfer data
   */
  public function initialize() {
    $ressource = $this->ressource('ressource');
    if (!empty($ressource->id)) {
      $baseSurfer = new base_surfer();
      $logoutParameters = array($baseSurfer->logformVar.'_logout' => 1);
      if ($this->_logoutRedirectionPageId > 0) {
        $baseObject = new base_object();
        $logoutParameters[$baseSurfer->logformVar.'_redirection'] =
          $baseObject->getWebLink($this->_logoutRedirectionPageId);
      }
      $reference = clone $this->reference();
      $reference->setParameters(array($logoutParameters), NULL);
      $logoutLink = $reference->getRelative();

      $surfer = $this->getSurfer($ressource->id);
      $this->surfer = array(
        'name' => $surfer['name'],
        'avatar' => $surfer['avatar'],
        'page_link' => $surfer['page_link'],
        'edit_link' => $this->owner->acommunityConnector()->getSurferEditorPageLink($ressource->id),
        'messages_link' => $this->owner->acommunityConnector()->getMessagesPageLink(
          $ressource->id, 'overview'
        ),
        'groups_link' => $this->owner->acommunityConnector()->getSurferGroupsPageLink($this->languageId),
        'notifications_link' => $this->owner->acommunityConnector()->getMessagesPageLink(
          $ressource->id, 'notifications'
        ),
        'notification_settings_link' => $this->owner->acommunityConnector()->getNotificationSettingsPageLink(
          $ressource->id
        ),
        'logout_link' => $logoutLink
      );
      unset($surfer);

      $contactsCount = $this->owner->communityConnector()->getContactNumber($ressource->id);
      if ($contactsCount > 0) {
        $handle = ($contactsCount == 1) ? 'contact_link' : 'contacts_link';
        $this->captions['contacts_link'] = sprintf($this->captions[$handle], $contactsCount);
        $this->surfer['contacts_link'] = $this->owner->acommunityConnector()->getSurferContactsPageLink(
          $ressource->id, 'contacts'
        );
      }
      $requestsReceivedCount = $this->owner->communityConnector()->getContactRequestsReceivedNumber(
        $ressource->id
      );
      if ($requestsReceivedCount > 0) {
        $handle = ($requestsReceivedCount == 1) ? 'contact_request_link' : 'contact_requests_link';
        $this->captions['contact_requests_link'] = sprintf($this->captions[$handle], $requestsReceivedCount);
        $this->surfer['contact_requests_link'] = $this->owner->acommunityConnector()->getSurferContactsPageLink(
          $ressource->id, 'contact_requests'
        );
      }
      $ownRequestsCount = $this->owner->communityConnector()->getContactRequestsSentNumber(
        $ressource->id
      );
      if ($ownRequestsCount > 0) {
        $handle = ($ownRequestsCount == 1) ? 'contact_own_request_link' : 'contact_own_requests_link';
        $this->captions['contact_own_requests_link'] = sprintf($this->captions[$handle], $ownRequestsCount);
        $this->surfer['contact_own_requests_link'] = $this->owner->acommunityConnector()->getSurferContactsPageLink(
          $ressource->id, 'own_contact_requests'
        );
      }

    } else {
      $loginLink = $this->owner->acommunityConnector()->getSurferLoginPageLink($this->languageId);
      $registrationLink = $this->owner->acommunityConnector()->getSurferRegistrationPageLink($this->languageId);
      $simpleTemplate = new base_simpletemplate();

      $this->messages['no_login'] = $simpleTemplate->parse(
        $this->messages['no_login'],
        array(
          'login_link' => sprintf(
            '<a href="%s">%s</a>', $loginLink, $this->captions['login_link']
          ),
          'registration_link' => sprintf(
            '<a href="%s">%s</a>', $registrationLink, $this->captions['registration_link']
          )
        )
      );
    }
  }
}