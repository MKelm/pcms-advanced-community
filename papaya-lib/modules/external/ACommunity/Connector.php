<?php
/**
 * Advanced community connector
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
 * Advanced community connector
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityConnector extends base_connector {

  /**
   * Guid of connector to get module options
   */
  protected $_guid = '0badeb14ea2d41d5bcfd289e9d190534';

  /**
  * Plugin option fields to set module options
  * @var array
  */
  public $pluginOptionFields = array(
    'cache_support' => array(
      'Cache Support', 'isNum', TRUE, 'yesno', NULL,
      'Use last changes status for modules\' cache identification.', 1
    ),
    'Display Modes',
    'display_mode_surfer_name' => array(
      'Surfer Name', 'isAlpha',
      TRUE,
      'combo',
      array(
        'all' => "Givenname 'Handle' Surname",
        'names' => 'Givenname Surname',
        'handle' => 'Handle',
        'givenname' => 'Givenname',
        'surname' => 'Surname'
      ),
      'How to display names in outputs',
      'names'
    ),
    'display_mode_ajax_requests' => array(
      'Ajax Requests', 'isAlpha', FALSE, 'input', 50, 'Enter a view mode for ajax requests.', 'ajax'
    ),
    'Groups',
    'groups_default_image_id' => array(
      'Default Image', 'isGUID', TRUE, 'mediaimage', 32, NULL, NULL
    ),
    'Notifications',
    'notification_sender_email' => array(
      'Sender E-Mail', 'isEmail', TRUE, 'input', 200, 'Sender address for use in notification emails.', ''
    ),
    'notification_sender_name' => array(
      'Sender Name', 'isAlphaNumChar', TRUE, 'input', 200, 'Sender name for use in notifcation emails.', ''
    ),
    'notification_by_message' => array(
      'Notify By Message', 'isNum', TRUE, 'yesno', NULL, 'Default value for new surfers.', 1
    ),
    'notification_by_email' => array(
      'Notify By E-Mail', 'isNum', TRUE, 'yesno', NULL, 'Default value for new surfers.', 0
    ),
    'Moderators',
    'moderator_group_id' => array(
      'Surfer Group', 'isNum', TRUE, 'function', 'callbackSurferGroupsList'
    ),
    'Page IDs',
    'comments_page_id' => array(
      'Comments', 'isNum', TRUE, 'pageid', 30, 'The page module is designed for ajax requests.', NULL
    ),
    'surfer_registration_page_id' => array(
      'Surfer Registration', 'isNum', TRUE, 'pageid', 30,
      'Use a community registration page module', NULL
    ),
    'surfer_login_page_id' => array(
      'Surfer Login', 'isNum', TRUE, 'pageid', 30,
      'Use a community login page module', NULL
    ),
    'surfer_editor_page_id' => array(
      'Surfer Editor', 'isNum', TRUE, 'pageid', 30,
      'Use a community profile page module', NULL
    ),
    'surfer_page_id' => array(
      'Surfer', 'isNum', TRUE, 'pageid', 30, NULL, NULL
    ),
    'surfer_contacts_page_id' => array(
      'Surfer Contacts', 'isNum', TRUE, 'pageid', 30, '', NULL
    ),
    'gallery_page_id' => array(
      'Gallery', 'isNum', TRUE, 'pageid', 30, NULL, NULL
    ),
    'group_page_id' => array(
      'Group', 'isNum', TRUE, 'pageid', 30, '', NULL
    ),
    'surfer_groups_page_id' => array(
      'Surfer Groups', 'isNum', TRUE, 'pageid', 30, '', NULL
    ),
    'messages_page_id' => array(
      'Messages / Notifications', 'isNum', TRUE, 'pageid', 30, '', NULL
    ),
    'notification_settings_page_id' => array(
      'Notification Settings', 'isNum', TRUE, 'pageid', 30, '', NULL
    ),
    'surfers_page_id' => array(
      'Surfers', 'isNum', TRUE, 'pageid', 30, 'To invite surfers to a group.', NULL
    ),
    'Parameter Groups',
    'surfer_page_parameter_group' => array(
      'Surfer', 'isAlpha', TRUE, 'input', 30, NULL, 'acsp'
    ),
    'Text Thumbnails',
    'text_thumbnails' => array(
      'Active', 'isNum', TRUE, 'yesno', NULL, 'Show thumbnails for image links.', 1
    ),
    'text_thumbnails_folder' => array(
      'Folder', 'isNum', TRUE, 'mediafolder', NULL, 'Media DB folder to get thumbnails for image links.'
    ),
    'text_thumbnails_size' => array(
      'Size', 'isNum', TRUE, 'input', 200, NULL, '100'
    ),
    'text_thumbnails_resize_mode' => array(
      'Resize Mode', 'isAlpha', TRUE, 'translatedcombo',
       array(
         'abs' => 'Absolute', 'max' => 'Maximum', 'min' => 'Minimum', 'mincrop' => 'Minimum cropped'
       ), '', 'mincrop'
    )
  );

  /**
   * Surfer deletion object
   * @var ACommunitySurferDeletion
   */
  protected $_surferDeletion = NULL;

  /**
   * Page deletion object
   * @var ACommunityPageDeletion
   */
  protected $_pageDeletion = NULL;

  /**
   * Community connector
   * @var connector_surfers
   */
  protected $_communityConnector = NULL;

  /**
   * Pages connector
   * @var connector_surfers
   */
  protected $_pagesConnector = NULL;

  /**
   * Notification handler object
   * @var ACommunityNotificationHandler
   */
  protected $_notifcationHandler = NULL;

  /**
   * Data object for group surfer relations
   * @var ACommunityGroupSurferRelations
   */
  protected $_groupSurferRelations = NULL;

  /**
  * Get form xml to select a surfer group by callback.
  *
  * @param string $name Field name
  * @param array $element Field element configurations
  * @param string $data Current field data
  * @return string $result XML
  */
  public function callbackSurferGroupsList($name, $element, $data) {
    $groups = $this->communityConnector()->getGroupsList();
    $result = sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">',
      $this->paramName,
      $name
    );
    foreach ($groups as $id => $group) {
      $selected = $data == $id ? ' selected="selected"' : '';
      $result .= sprintf(
        '<option value="%d"%s>%s</option>'.LF,
        $id,
        $selected,
        $group['surfergroup_title']
      );
    }
    $result .= '</select>';
    return $result;
  }

  /**
   * Get/set object for group surfer relations helper methods.
   * Used by content ressource to identify valid group surfers.
   *
   * @param ACommunityGroupSurferRelations $relations
   * @return ACommunityGroupSurferRelations
   */
  public function groupSurferRelations(ACommunityGroupSurferRelations $relations = NULL) {
    if (isset($relations)) {
      $this->_groupSurferRelations = $relations;
    } elseif (is_null($this->_groupSurferRelations)) {
      include_once(dirname(__FILE__).'/Group/Surfer/Relations.php');
      $this->_groupSurferRelations = new ACommunityGroupSurferRelations();
      $this->_groupSurferRelations->papaya($this->papaya());
      $this->_groupSurferRelations->acommunityConnector($this);
    }
    return $this->_groupSurferRelations;
  }

  /**
   * Dispatch log message
   *
   * @param string $message
   * @param integer $messageType
   */
  public function dispatchMessage($message, $messageType = PapayaMessage::TYPE_ERROR) {
    $this
      ->papaya()
      ->messages
      ->dispatch(
        new PapayaMessageLog(
          PapayaMessageLogable::GROUP_MODULES, $messageType, $message
        )
      );
  }

  /**
   * Get cache support status
   *
   * @return integer
   */
  public function cacheSupport() {
    return papaya_module_options::readOption($this->_guid, 'cache_support', 1) > 0;
  }

  /**
   * Get moderator group id
   *
   * @return integer
   */
  public function getModeratorGroupId() {
    return papaya_module_options::readOption($this->_guid, 'moderator_group_id', 0);
  }

  /**
   * Get groups default image id
   *
   * @return integer
   */
  public function getGroupsDefaultImageId() {
    return papaya_module_options::readOption($this->_guid, 'groups_default_image_id', NULL);
  }

  /**
   * Surfer deletion object
   *
   * @param ACommunitySurferDeletion $deletion
   * @return ACommunitySurferDeletion
   */
  public function surferDeletion(ACommunitySurferDeletion $deletion = NULL) {
    if (isset($deletion)) {
      $this->_surferDeletion = $deletion;
    } elseif (is_null($this->_surferDeletion)) {
      include_once(dirname(__FILE__).'/Surfer/Deletion.php');
      $this->_surferDeletion = new ACommunitySurferDeletion();
    }
    return $this->_surferDeletion;
  }

  /**
   * Page deletion object
   *
   * @param ACommunityPageDeletion $deletion
   * @return ACommunityPageDeletion
   */
  public function pageDeletion(ACommunityPageDeletion $deletion = NULL) {
    if (isset($deletion)) {
      $this->_pageDeletion = $deletion;
    } elseif (is_null($this->_pageDeletion)) {
      include_once(dirname(__FILE__).'/Page/Deletion.php');
      $this->_pageDeletion = new ACommunityPageDeletion();
    }
    return $this->_pageDeletion;
  }

  /**
   * Notification handler object
   *
   * @param ACommunityNotificationHandler $handler
   * @return ACommunityNotificationHandler
   */
  public function notificationHandler(ACommunityNotificationHandler $handler = NULL) {
    if (isset($handler)) {
      $this->_notifcationHandler = $handler;
    } elseif (is_null($this->_notifcationHandler)) {
      include_once(dirname(__FILE__).'/Notification/Handler.php');
      $this->_notifcationHandler = new ACommunityNotificationHandler();
    }
    return $this->_notifcationHandler;
  }

  /**
   * Action dispatcher function to delete surfer dependend data
   *
   * @param string $surferId
   */
  public function onDeleteSurfer($surferId) {
    $textThumbnailsFolder =
      papaya_module_options::readOption($this->_guid, 'text_thumbnails_folder', NULL);
    $this->surferDeletion()->setDeletedSurferInPageComments($surferId);
    $this->surferDeletion()->deleteSurferComments($surferId);
    $this->surferDeletion()->deleteSurferCommentsThumbnailLinkFiles($textThumbnailsFolder, $surferId);
    $this->surferDeletion()->deleteSurferGalleries($surferId, $textThumbnailsFolder);
    $this->surferDeletion()->deleteMessages($surferId);
    $this->surferDeletion()->deleteMessagesThumbnailLinkFiles($textThumbnailsFolder, $surferId);
  }

  /**
   * Action dispatcher function to delete pages' dependend data
   *
   * Note: You need an action dispatcher call in base_topic_edit->destroy()
   * to make onDeletePages available for dispatching. Please use the papaya CMS patches from the
   * Advanced Community package to get it.
   *
   * @param array $pageIds
   */
  public function onDeletePages($pageIds) {
    $this->pageDeletion()->deletePageComments($pageIds);
    $this->pageDeletion()->deletePageCommentsThumbnailLinkFiles($pageIds);
  }

  /**
   * Sender data for notification emails
   *
   * @return array
   */
  public function getNotificationSender() {
    return array(
      'email' => papaya_module_options::readOption($this->_guid, 'notification_sender_email', NULL),
      'name' => papaya_module_options::readOption($this->_guid, 'notification_sender_name', NULL)
    );
  }

  /**
   * Default setting for notifications
   *
   * @return array
   */
  public function getNotificationDefaultSetting() {
    return array(
      'by_message' => papaya_module_options::readOption($this->_guid, 'notification_by_message', NULL),
      'by_email' => papaya_module_options::readOption($this->_guid, 'notification_by_email', NULL)
    );
  }

  /**
   * Display mode for surfer names
   *
   * @return string
   */
  public function getDisplayModeSurferName() {
    return papaya_module_options::readOption($this->_guid, 'display_mode_surfer_name', 'names');
  }

  /**
   * Get link to comments page
   *
   * @return string
   */
  public function getCommentsPageLink($languageId, $ressourceType, $ressourceId, $additionalParameters = array()) {
    $ressourceParameterName = NULL;
    switch ($ressourceType) {
      case 'page':
        $ressourceParameterName = 'page_id';
        break;
      case 'image':
        $ressourceParameterName = 'image_id';
        break;
      case 'surfer':
        $parameters = TRUE;
        $surferId = $ressourceId;
        break;
      case 'group':
        $ressourceParameterName = 'group_id';
        if (is_numeric($ressourceId)) {
          $ressourceId = $this->getGroupIdByHandle($ressourceId);
        }
        break;
    }
    if (!isset($parameters)) {
      $parameters = array($ressourceParameterName => $ressourceId);
      $surferId = NULL;
    }
    if (isset($additionalParameters)) {
      if ($parameters === TRUE) {
        $parameters = array();
      }
      $parameters = array_merge($parameters, $additionalParameters);
    }
    $mode = papaya_module_options::readOption($this->_guid, 'display_mode_ajax_requests', 'ajax');
    return $this->_getPageLink(
      'comments_page_id', $surferId, $parameters, 'accs', NULL, NULL, NULL, $languageId, $mode
    );
  }

  /**
   * Get link to surfer registration page
   *
   * @return string
   */
  public function getSurferRegistrationPageLink($languageId) {
    return $this->_getPageLink(
      'surfer_registration_page_id', NULL, FALSE, NULL, NULL, NULL, NULL, $languageId
    );
  }

  /**
   * Get link to surfer login page
   *
   * @return string
   */
  public function getSurferLoginPageLink($languageId) {
    return $this->_getPageLink(
      'surfer_login_page_id', NULL, FALSE, NULL, NULL, NULL, NULL, $languageId
    );
  }

  /**
   * Get link to surfer page by surfer id
   *
   * @param string $surferId
   * @return string|NULL
   */
  public function getSurferPageLink($surferId) {
    $parameterGroup = papaya_module_options::readOption(
      $this->_guid, 'surfer_page_parameter_group', 'acsp'
    );
    return $this->_getPageLink('surfer_page_id', $surferId, TRUE, $parameterGroup, 's-page');
  }

  /**
   * Get link to surfers page
   *
   * Used by group page to invite surfers to a group, mode = invite_surfers
   *
   * @return string
   */
  public function getSurfersPageLink($languageId, $mode = NULL, $groupHandle = NULL) {
    if ($mode !== NULL && $groupHandle !== NULL) {
      $parameters = array('mode' => $mode, 'group_handle' => $groupHandle);
    } else {
      $parameters = FALSE;
    }
    return $this->_getPageLink(
      'surfers_page_id', NULL, $parameters, 'acss', NULL, NULL, NULL, $languageId
    );
  }

  /**
   * Get link to surfer contacts page by surfer id
   *
   * @var string $surferId
   * @return string|NULL
   */
  public function getSurferContactsPageLink($surferId, $anchor = '') {
    return $this->_getPageLink('surfer_contacts_page_id', $surferId, FALSE, NULL, 's-contacts', $anchor);
  }

  /**
   * Get link to surfer editor page by surfer id
   *
   * The default editor page is a content_userdata page
   *
   * @param string $surferId
   * @return string|NULL
   */
  public function getSurferEditorPageLink($surferId) {
    return $this->_getPageLink('surfer_editor_page_id', $surferId, FALSE, NULL, 's-editor');
  }

  /**
   * Get link to gallery page by ressource
   *
   * @param string $ressourceType
   * @param string|integer $ressourceId
   * @return string|NULL
   */
  public function getGalleryPageLink($ressourceType, $ressourceId) {
    if ($ressourceType == 'surfer') {
      $surferId = $ressourceId;
      $parameters = TRUE;
      $handle = NULL;
    } else {
      $surferId = NULL;
      $handle = $this->getGroupHandleById($ressourceId);
      $parameters = array('group_handle' => $handle);
    }
    return $this->_getPageLink(
      'gallery_page_id', $surferId, $parameters, 'acig', 's-gallery', NULL, $handle
    );
  }

  /**
   * Get link to messages / notifications page by surfer id
   *
   * @param string $surferId
   * @param string $target surfer (default), overview, or notifications
   * @return string|NULL
   */
  public function getMessagesPageLink($surferId, $target = 'surfer') {
    $parameterNamePostfix = $target == 'overview' ? 's-messages' : '-messages';
    $handle = NULL;
    if ($target == 'overview') {
      $parameters = array();
    } elseif ($target == 'notifications') {
      $parameters = array('notifications' => 1);
      $handle = 'system';
    } else {
      $parameters = TRUE;
    }
    return $this->_getPageLink(
      'messages_page_id', $surferId, $parameters, 'acmp', $parameterNamePostfix, NULL, $handle
    );
  }

  /**
   * Get link to group page by group id
   *
   * @param string $groupHandle
   * @return string|NULL
   */
  public function getGroupPageLink($groupHandle) {
    $parameters = array('group_handle' => $groupHandle);
    return $this->_getPageLink(
      'group_page_id', NULL, $parameters, 'acg', 's-page', NULL, $groupHandle
    );
  }

  /**
   * Get link to groups page by mode / group handle
   *
   * @param integer $languageId
   * @return string|NULL
   */
  public function getSurferGroupsPageLink($languageId, $mode = NULL) {
    if (!empty($mode)) {
      $parameters = array('mode' => $mode);
    } else {
      $parameters = FALSE;
    }
    return $this->_getPageLink(
      'surfer_groups_page_id', NULL, $parameters, 'acgs', NULL
    );
  }

  /**
   * Get link to notification settings page
   *
   * @param string $surferId to generate page name
   * @return string|NULL
   */
  public function getNotificationSettingsPageLink($surferId) {
    return $this->_getPageLink(
      'notification_settings_page_id', $surferId, FALSE, NULL, 's-notification-settings'
    );
  }

  /**
   * Get group id by handle
   *
   * @param string $handle
   * @return integer
   */
  public function getGroupIdByHandle($handle) {
    $group = clone $this->groupSurferRelations()->group();
    $group->load(array('handle' => $handle));
    if (!empty($group['id'])) {
      return (int)$group['id'];
    }
    return 0;
  }

  /**
   * Get group handle by id
   *
   * @param integer $id
   * @return string
   */
  public function getGroupHandleById($id) {
    $group = clone $this->groupSurferRelations()->group();
    $group->load($id);
    if (!empty($group['handle'])) {
      return $group['handle'];
    }
    return NULL;
  }

  /**
   * Get page links by option with additional parameters
   *
   * @param string $optionName by module options
   * @param string $surferId destination surfer
   * @param boolean|array $parameters flag to activate surfer_handle parameter or add custom parameters
   * @param string $parameterGroup a valid parameter group
   * @param string $pageNamePostfix postfix to set after handle or page name without handle
   * @param string $anchor add an anchor to the page link
   * @param string $handle to use a customized handle in links
   * @param integer $languageId for page names by pages connector
   * @return string|NULL
   */
  protected function _getPageLink(
              $optionName, $surferId = NULL, $parameters = FALSE, $parameterGroup = NULL,
              $pageNamePostfix = 'page', $anchor = NULL, $handle = NULL, $languageId = NULL,
              $mode = 'page'
            ) {
    if (!empty($optionName)) {
      $proceed = FALSE;
      if (empty($handle) && !empty($surferId)) {
        $handleBySurferId = TRUE;
        $handle = $this->communityConnector()->getHandleById($surferId);
        if (!empty($handle)) {
          $proceed = TRUE;
        }
      } else {
        $proceed = TRUE;
      }
      if ($proceed) {
        if ($parameters === TRUE) {
          if (!empty($handle)) {
            $parameters = array('surfer_handle' => $handle);
          } else {
            $parameters = array();
          }
        } elseif ($parameters === FALSE) {
          $parameters = array();
        } elseif (isset($handleBySurferId)) {
          $parameters = array_merge(array('surfer_handle' => $handle), $parameters);
        }
        if (!empty($handle)) {
          $pageName = base_object::escapeForFilename($handle).$pageNamePostfix;
        } elseif (!empty($pageNamePostfix)) {
          $pageName = $pageNamePostfix;
        } else {
          $pageName = NULL;
        }
        $pageId = papaya_module_options::readOption($this->_guid, $optionName, NULL);
        if (!empty($pageId)) {
          if (empty($pageName) && !empty($languageId)) {
            $titles = $this->pagesConnector()->getTitles($pageId, $languageId);
            if (!empty($titles[$pageId])) {
              $pageName = base_object::escapeForFilename($titles[$pageId]);
            }
          }
          $result = base_object::getAbsoluteURL(
            base_object::getWebLink($pageId, NULL, $mode, $parameters, $parameterGroup, $pageName)
          );
          if (!empty($anchor)) {
            return $result.'#'.$anchor;
          }
          return $result;
        }
      }
    }
    return NULL;
  }

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
   * @param integer $languageId
   * @param string $recipientId Surfer ID of recipient surfer
   * @param array $parameters
   */
  public function notify($notificationName, $languageId, $recipientId, $parameters = array()) {
    $this->notificationHandler()->notify($notificationName, $languageId, $recipientId, $parameters);
  }

  /**
   * Get text options to generate thumbnails for image links
   *
   * @return array
   */
  public function getTextOptions() {
    return array(
      'thumbnails' => papaya_module_options::readOption($this->_guid, 'text_thumbnails', NULL),
      'thumbnails_folder' => papaya_module_options::readOption($this->_guid, 'text_thumbnails_folder', NULL),
      'thubmnails_size' => papaya_module_options::readOption($this->_guid, 'text_thumbnails_size', NULL),
      'thubmnails_resize_mode' => papaya_module_options::readOption(
        $this->_guid, 'text_thumbnails_resize_mode', NULL
      )
    );
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
   * Get/set pages connector
   *
   * @param object $connector
   * @return object
   */
  public function pagesConnector(PapayaBasePagesConnector $connector = NULL) {
    if (isset($connector)) {
      $this->_pagesConnector = $connector;
    } elseif (is_null($this->_pagesConnector)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
      $this->_pagesConnector = base_pluginloader::getPluginInstance(
        '69db080d0bb7ce20b52b04e7192a60bf', $this
      );
    }
    return $this->_pagesConnector;
  }
}