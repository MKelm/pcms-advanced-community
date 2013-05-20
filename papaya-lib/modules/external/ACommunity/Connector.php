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
    'Page IDs',
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
      'Use a community mixed user data page module', NULL
    ),
    'surfer_page_id' => array(
      'Surfer', 'isNum', TRUE, 'pageid', 30, NULL, NULL
    ),
    'surfer_contacts_page_id' => array(
      'Surfer Contacts', 'isNum', TRUE, 'pageid', 30, '', NULL
    ),
    'surfer_gallery_page_id' => array(
      'Surfer Gallery', 'isNum', TRUE, 'pageid', 30, NULL, NULL
    ),
    'Parameter Groups',
    'surfer_page_parameter_group' => array(
      'Surfer Login', 'isAlpha', TRUE, 'input', 30, NULL, 'acs'
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
   * Action dispatcher function to delete surfer dependend data
   *
   * @param string $surferId
   */
  public function onDeleteSurfer($surferId) {
    $this->surferDeletion()->setDeletedSurferInPageComments($surferId);
    $this->surferDeletion()->deleteSurferComments($surferId);
    $this->surferDeletion()->deleteSurferGalleries($surferId);
  }

  /**
   * Action dispatcher function to delete pages' dependend data
   *
   * Note: You have to add an action dispatcher call in base_topic_edit->destroy()
   * to make onDeletePages available for dispatching. See base_topic_edit_destroy_replacement.txt
   * for a replacement of the whole destroy() method which contains a valid call.
   *
   * @param array $pageIds
   */
  public function onDeletePages($pageIds) {
    $this->pageDeletion()->deletePageComments($pageIds);
  }

  /**
   * Get link to surfer registration page
   *
   * @return string
   */
  public function getSurferRegistrationPageLink() {
    $pageId = papaya_module_options::readOption($this->_guid, 'surfer_registration_page_id', NULL);
    return base_object::getWebLink(
      $pageId, NULL, NULL, NULL, NULL, 'registration-page'
    );
  }

  /**
   * Get link to surfer login page
   *
   * @return string
   */
  public function getSurferLoginPageLink() {
    $pageId = papaya_module_options::readOption($this->_guid, 'surfer_login_page_id', NULL);
    return base_object::getWebLink(
      $pageId, NULL, NULL, NULL, NULL, 'login-page'
    );
  }

  /**
   * Get link to surfer page by surfer id
   *
   * @param string $surferId
   * @return string|NULL
   */
  public function getSurferPageLink($surferId) {
    $handle = $this->communityConnector()->getHandleById($surferId);
    if (!empty($handle)) {
      $pageId = papaya_module_options::readOption($this->_guid, 'surfer_page_id', NULL);
      $parameterGroup = papaya_module_options::readOption(
        $this->_guid, 'surfer_page_parameter_group', 'acs'
      );
      return base_object::getWebLink(
        $pageId, NULL, NULL, array('surfer_handle' => $handle), $parameterGroup, $handle.'s-page'
      );
    }
    return NULL;
  }

  /**
   * Get link to surfer contacts page by surfer id
   *
   * @var string $surferId
   * @return string|NULL
   */
  public function getSurferContactsPageLink($surferId, $anchor = '') {
    $handle = $this->communityConnector()->getHandleById($surferId);
    if (!empty($handle)) {
      $pageId = papaya_module_options::readOption($this->_guid, 'surfer_contacts_page_id', NULL);
      $result = base_object::getWebLink(
        $pageId, NULL, NULL, NULL, NULL, $handle.'s-contacts'
      );
      if (!empty($result) && !empty($anchor)) {
        return $result.'#'.$anchor;
      }
      return $result;
    }
    return NULL;
  }

  /**
   * Get link to surfer editor page by surfer id
   *
   * The default editor page is a content_userdata page
   *
   * @return string|NULL
   */
  public function getSurferEditorPageLink($surferHandle) {
    if (!empty($surferHandle)) {
      $pageId = papaya_module_options::readOption($this->_guid, 'surfer_editor_page_id', NULL);
      return base_object::getWebLink(
        $pageId, NULL, NULL, NULL, NULL, $surferHandle.'s-editor'
      );
    }
    return NULL;
  }

  /**
   * Get link to surfer gallery page by surfer id
   *
   * @param string $surferId
   * @return string|NULL
   */
  public function getSurferGalleryPageLink($surferId) {
    $handle = $this->communityConnector()->getHandleById($surferId);
    if (!empty($handle)) {
      $pageId = papaya_module_options::readOption($this->_guid, 'surfer_gallery_page_id', NULL);
      return base_object::getWebLink(
        $pageId, NULL, NULL, array('surfer_handle' => $handle), 'acg', $handle.'s-gallery'
      );
    }
    return NULL;
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

}
