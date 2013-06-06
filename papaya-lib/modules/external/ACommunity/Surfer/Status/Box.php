<?php
/**
 * Advanced community surfer status box
 *
 * Offers status information of logged in user and links to certain surfer pages
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
 * Advanced community surfer status box
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunitySurferStatusBox extends base_actionbox implements PapayaPluginCacheable {

  /**
   * Parameter prefix name
   * @var string $paramName
   */
  public $paramName = 'acs';

  /**
   * Edit fields
   * @var array $editFields
   */
  public $editFields = array(
    'logout_redirection_page_id' => array(
      'Logout Redirection Page', 'isNum', TRUE, 'pageid', 30, '', 0
    ),
    'avatar_size' => array(
      'Avatar Size', 'isNum', TRUE, 'input', 30, '', 40
    ),
    'avatar_resize_mode' => array(
      'Avatar Resize Mode', 'isAlpha', TRUE, 'translatedcombo',
       array(
         'abs' => 'Absolute', 'max' => 'Maximum', 'min' => 'Minimum', 'mincrop' => 'Minimum cropped'
       ), '', 'mincrop'
    ),
    'Captions',
    'caption_contact_link' => array(
      'Contact Link', 'isNoHTML', TRUE, 'input', 200, '', '%d contact'
    ),
    'caption_contacts_link' => array(
      'Contacts Link', 'isNoHTML', TRUE, 'input', 200, '', '%d contacts'
    ),
    'caption_contact_request_link' => array(
      'Received Contact Request Link', 'isNoHTML', TRUE, 'input', 200, '', '%d contact request received'
    ),
    'caption_contact_requests_link' => array(
      'Received Contact Requests Link', 'isNoHTML', TRUE, 'input', 200, '', '%d contact requests received'
    ),
    'caption_contact_own_request_link' => array(
      'Sent Contact Request Link', 'isNoHTML', TRUE, 'input', 200, '', '%d contact request sent'
    ),
    'caption_contact_own_requests_link' => array(
      'Sent Contact Requests Link', 'isNoHTML', TRUE, 'input', 200, '', '%d contact request sent'
    ),
    'caption_edit_link' => array(
      'Edit Link', 'isNoHTML', TRUE, 'input', 200, 'Caption for edit surfer link.', 'Edit'
    ),
    'caption_groups_link' => array(
      'Groups Link', 'isNoHTML', TRUE, 'input', 200, '', 'Groups'
    ),
    'caption_login_link' => array(
      'Login Link', 'isNoHTML', TRUE, 'input', 200, 'For placeholder {%LOGIN_LINK%}', 'login'
    ),
    'caption_logout_link' => array(
      'Logout Link', 'isNoHTML', TRUE, 'input', 200, 'Caption for surfer logout link.', 'Logout'
    ),
    'caption_messages_link' => array(
      'Messages Link', 'isNoHTML', TRUE, 'input', 200, '', 'Messages'
    ),
    'caption_notifications_link' => array(
      'Notifications Link', 'isNoHTML', TRUE, 'input', 200, '', 'Notifications'
    ),
    'caption_notification_settings_link' => array(
      'Notification Settings Link', 'isNoHTML', TRUE, 'input', 200, '', 'Notification settings'
    ),
    'caption_registration_link' => array(
      'Registration Link', 'isNoHTML', TRUE, 'input', 200,
      'For placeholder {%REGISTRATION_LINK%}', 'register'
    ),
    'Messages',
    'message_no_login' => array(
      'No Login', 'isNoHTML', TRUE, 'input', 200, '',
      'Get involved, {%LOGIN_LINK%} or {%REGISTRATION_LINK%}.'
    )
  );

  /**
   * Status object
   * @var ACommunitySurferStatus
   */
  protected $_status = NULL;

  /**
   * Cache definition
   * @var PapayaCacheIdentifierDefinition
   */
  protected $_cacheDefinition = NULL;

  /**
   * Current ressource
   * @var ACommunityUiContentRessource
   */
  protected $_ressource = NULL;

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
      $ressource = $this->setRessourceData();
      $definitionValues = array('acommunity_surfer_status_box');
      if (isset($ressource->id)) {
        include_once(dirname(__FILE__).'/../../Cache/Identifier/Values.php');
        $values = new ACommunityCacheIdentifierValues();
        $definitionValues[] = $ressource->id;
        $definitionValues[] = $values->lastChangeTime('surfer:surfer_'.$ressource->id);
        $definitionValues[] = $values->lastChangeTime('contacts:surfer_'.$ressource->id);
      }
      $this->_cacheDefinition = new PapayaCacheIdentifierDefinitionValues(
        $definitionValues
      );
    }
    return $this->_cacheDefinition;
  }

  /**
   * Get ressource data to load corresponding comments
   * Overwrite this method for customized ressources
   */
  public function setRessourceData() {
    if (is_null($this->_ressource)) {
      $this->_ressource = $this->status()->ressource();
      $this->_ressource->set('surfer', NULL, array('surfer' => array()), NULL, NULL, 'is_selected');
    }
    return $this->_ressource;
  }

  /**
  * Get (and, if necessary, initialize) the ACommunitySurferStatus object
  *
  * @return ACommunitySurferStatus $status
  */
  public function status(ACommunitySurferStatus $status = NULL) {
    if (isset($status)) {
      $this->_status = $status;
    } elseif (is_null($this->_status)) {
      include_once(dirname(__FILE__).'/../Status.php');
      $this->_status = new ACommunitySurferStatus();
      $this->_status->module = $this;
      $this->_status->parameterGroup($this->paramName);
      $this->_status->data()->languageId = $this->papaya()->request->languageId;
    }
    return $this->_status;
  }

  /**
   * Get parsed data
   *
   * @return string $result XML
   */
  public function getParsedData() {
    $this->initializeParams();
    $this->setRessourceData();
    $this->setDefaultData();
    $this->status()->data()->setPluginData(
      $this->data,
      array(
        'caption_login_link', 'caption_registration_link',
        'caption_edit_link', 'caption_logout_link', 'caption_groups_link',
        'caption_contacts_link', 'caption_contact_requests_link',
        'caption_contact_own_requests_link', 'caption_messages_link',
        'caption_notifications_link', 'caption_notification_settings_link',
        'caption_contact_link', 'caption_contact_request_link', 'caption_contact_own_request_link'
      ),
      array('message_no_login')
    );
    return $this->status()->getXml();
  }
}