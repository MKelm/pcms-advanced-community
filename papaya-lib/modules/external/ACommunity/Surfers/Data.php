<?php
/**
 * Advanced community surfers data class to handle all sorts of related data
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
require_once(dirname(__FILE__).'/../Ui/Content/Data.php');

/**
 * Advanced community surfers data class to handle all sorts of related data
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunitySurfersData extends ACommunityUiContentData {

  /**
   * Surfers data
   * @var array
   */
  public $surfers = NULL;

  /**
   * Display surfers by last action time or registration time or surfer contacts
   * @var string
   */
  public $displayMode = NULL;

  /**
   * Get surfer actions or registrations in a specified timeframe
   * @var string
   */
  protected $_timeframe = NULL;

  /**
   * Flag to show paging or not
   * @var boolean
   */
  public $showPaging = FALSE;

  /**
   * Limit of items per page
   * @var integer
   */
  public $pagingItemsPerPage = NULL;

  /**
   * Absolute count of items
   * @var integer|array
   */
  public $pagingItemsAbsCount = NULL;

  /**
   * Perform changes to contact data
   * @var ACommunitySurferContactChanges
   */
  protected $_contactChanges = NULL;

  /**
   * A regular expression to filter reference parameters by name
   * @var string
   */
  protected $_referenceParametersExpression =
    '(lastaction|registration|contacts|own_contact_requests|contact_requests)_list_page';

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
    $this->displayMode = $data['display_mode'];
    $this->_timeframe = $data['timeframe'];
    $this->pagingItemsPerPage = (int)$data['limit'];
    $this->showPaging = !isset($data['show_paging']) ? TRUE : (bool)$data['show_paging'];
    parent::setPluginData($data, $captionNames, $messageNames);
  }

  /**
   * Intitialize surfer data
   */
  public function initialize() {
    $timeframe = 60 * 60 * 24 * $this->_timeframe;
    $ressource = $this->ressource();
    $surfers = array();
    if ($this->displayMode == 'lastaction') {
      $page = $this->owner->parameters()->get('lastaction_list_page', 0);
      $surfers = $this->owner->communityConnector()->getLastActiveSurfers(
        $timeframe,
        $this->pagingItemsPerPage,
        $page > 0 ? ($page - 1) * $this->pagingItemsPerPage : 0
      );
      if (!empty($surfers)) {
        $this->pagingItemsAbsCount = $surfers[1];
        $surfers = $surfers[0];
        $this->getSurfer(array_keys($surfers));
      }

    } elseif ($this->displayMode == 'registration') {
      $page = $this->owner->parameters()->get('registration_list_page', 0);
      $surfers = $this->owner->communityConnector()->getLatestRegisteredSurfers(
        time() - $timeframe,
        $this->pagingItemsPerPage,
        $page > 0 ? ($page - 1) * $this->pagingItemsPerPage : 0
      );
      $this->pagingItemsAbsCount = $this->owner->communityConnector()->surferAdmin->surfersAbsCount;
      $this->getSurfer(array_keys($surfers));

    } elseif ($this->displayMode == 'contacts_and_requests' &&
              $this->ressourceIsActiveSurfer == TRUE) {
      $this->pagingItemsAbsCount = array();
      $page = $this->owner->parameters()->get('contacts_list_page', 0);
      $surfers['contacts'] = $this->owner->communityConnector()->getContacts(
        $this->currentSurferId(),
        FALSE,
        $this->pagingItemsPerPage,
        $page > 0 ? ($page - 1) * $this->pagingItemsPerPage : 0
      );
      $this->pagingItemsAbsCount['contacts'] = $this->owner->communityConnector()->contactsAbsCount;
      $page = $this->owner->parameters()->get('own_contact_requests_list_page', 0);
      $surfers['own_contact_requests'] = $this->owner->communityConnector()->getContactRequestsSent(
        $this->currentSurferId(),
        FALSE,
        $this->pagingItemsPerPage,
        $page > 0 ? ($page - 1) * $this->pagingItemsPerPage : 0
      );
      $this->pagingItemsAbsCount['own_contact_requests'] = $this->owner->communityConnector()->contactsAbsCount;
      $page = $this->owner->parameters()->get('contact_requests_list_page', 0);
      $surfers['contact_requests'] = $this->owner->communityConnector()->getContactRequestsReceived(
        $this->currentSurferId(),
        FALSE,
        $this->pagingItemsPerPage,
        $page > 0 ? ($page - 1) * $this->pagingItemsPerPage : 0
      );
      $this->pagingItemsAbsCount['contact_requests'] = $this->owner->communityConnector()->contactsAbsCount;

      $contactSurferIds = array_flip($surfers['contacts']);
      $ownRequestSurferIds = array_flip($surfers['own_contact_requests']);
      $requestSurferIds = array_flip($surfers['contact_requests']);
      $surferIds = array_keys(array_merge(
        $contactSurferIds, $ownRequestSurferIds, $requestSurferIds
      ));
      $this->getSurfer($surferIds);
      $surfers['links'] = array();
      foreach ($surferIds as $surferId) {
        $reference = clone $this->reference();
        if (isset($ownRequestSurferIds[$surferId])) {
          $reference->setParameters(
            array(
              'command' => 'remove_contact_request',
              'surfer_handle' => $this->_surfers[$surferId]['handle']
            ),
            $this->owner->parameterGroup()
          );
          $surfers['links'][$surferId]['commands'] = array(
            'remove_contact_request' => $reference->getRelative()
          );
        } elseif (isset($requestSurferIds[$surferId])) {
          $referenceAccept = $reference;
          $referenceAccept->setParameters(
            array(
              'command' => 'accept_contact_request',
              'surfer_handle' => $this->_surfers[$surferId]['handle']
            ),
            $this->owner->parameterGroup()
          );
          $referenceDecline = clone $reference;
          $referenceDecline->setParameters(
            array(
              'command' => 'decline_contact_request',
              'surfer_handle' => $this->_surfers[$surferId]['handle']
            ),
            $this->owner->parameterGroup()
          );
          $surfers['links'][$surferId]['commands'] = array(
            'accept_contact_request' => $referenceAccept->getRelative(),
            'decline_contact_request' => $referenceDecline->getRelative()
          );
        } elseif (isset($contactSurferIds[$surferId])) {
          $reference->setParameters(
            array(
              'command' => 'remove_contact',
              'surfer_handle' => $this->_surfers[$surferId]['handle']
            ),
            $this->owner->parameterGroup()
          );
          $surfers['links'][$surferId]['commands'] = array(
            'remove_contact' => $reference->getRelative()
          );
        }
      }

    } elseif ($this->displayMode == 'contacts') {
      $page = $this->owner->parameters()->get('contacts_list_page', 0);
      $contactIds = $this->owner->communityConnector()->getContacts(
        $ressource['id'],
        FALSE,
        $this->pagingItemsPerPage,
        $page > 0 ? ($page - 1) * $this->pagingItemsPerPage : 0
      );
      $this->pagingItemsAbsCount = $this->owner->communityConnector()->contactsAbsCount;
      $surfers = $this->getSurfer($contactIds);
    }

    $this->surfers = array();

    if (!empty($surfers)) {
      if ($this->displayMode == 'contacts_and_requests') {
        $this->surfers = array(
          'contacts' => array(),
          'own_contact_requests' => array(),
          'contact_requests' => array()
        );
        foreach ($this->surfers as $groupName => $surfer) {
          foreach ($surfers[$groupName] as $surferId) {
            $this->surfers[$groupName][] = $this->_getSurfer($surferId, $surfers['links'][$surferId]);
          }
        }
      } else {
        foreach ($surfers as $surfer) {;
          $this->surfers[] = $this->_getSurfer(
            isset($surfer['id']) ? $surfer['id'] : $surfer['surfer_id'], $surfer
          );
        }
      }
    }
  }

  /**
   * Get surfer data in array
   *
   * @param string $surferId
   * @param array $surfer
   * @return array
   */
  protected function _getSurfer($surferId, $surfer) {
    return array(
      'name' => isset($surfer['name']) ? $surfer['name'] : $this->_surfers[$surferId]['name'],
      'last_action' => !empty($surfer['surfer_lastaction']) ?
        date('Y-m-d H:i:s', $surfer['surfer_lastaction']) : NULL,
      'registration' => !empty($surfer['surfer_registration']) ?
        date('Y-m-d H:i:s', $surfer['surfer_registration']) : NULL,
      'avatar' => isset($surfer['avatar']) ? $surfer['avatar'] : $this->_surfers[$surferId]['avatar'],
      'page_link' => isset($surfer['page_link']) ? $surfer['page_link'] : $this->_surfers[$surferId]['page_link'],
      'commands' => !empty($surfer['commands']) ? $surfer['commands'] : NULL
    );
  }

  /**
  * Perform changes to contact data
  *
  * @param ACommunitySurferContactChanges $changes
  * @return ACommunitySurferContactChanges
  */
  public function contactChanges(ACommunitySurferContactChanges $changes = NULL) {
    if (isset($changes)) {
      $this->_changes = $changes;
    } elseif (is_null($this->_changes)) {
      include_once(dirname(__FILE__).'/../Surfer/Contact/Changes.php');
      $this->_changes = new ACommunitySurferContactChanges();
      $this->_changes->papaya($this->papaya());
    }
    return $this->_changes;
  }

}