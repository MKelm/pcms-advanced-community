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
   * Flag to show filter navigation or not
   * @var boolean
   */
  public $showFilterNavigation = FALSE;

  /**
   * Flag to show search dialog or not
   * @var boolean
   */
  public $showSearchDialog = FALSE;

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
   * Set reference parameters expression on construct
   */
  public function __construct() {
    $this->_referenceParametersExpression =
      '(lastaction|registration|contacts|own_contact_requests|contact_requests)_list_page'.
      '|surfers_search|surfers_character|group_handle|mode';
  }

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
    if (isset($data['timeframe'])) {
      $this->_timeframe = $data['timeframe'];
    }
    if (isset($data['show_filter_navigation'])) {
      $this->showFilterNavigation = $data['show_filter_navigation'];
    }
    if (isset($data['show_search_dialog'])) {
      $this->showSearchDialog = $data['show_search_dialog'];
    }
    $this->pagingItemsPerPage = (int)$data['limit'];
    $this->showPaging = !isset($data['show_paging']) ? TRUE : (bool)$data['show_paging'];
    parent::setPluginData($data, $captionNames, $messageNames);
  }

  /**
   * Intitialize surfer data
   */
  public function initialize() {
    $timeframe = 60 * 60 * 24 * $this->_timeframe;
    $ressource = $this->owner->ressource();
    $surfers = array();
    switch ($this->displayMode) {
      case 'lastaction':
        // load surfers by last actions
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
        break;

      case 'registration':
        // load surfers by registrations
        $page = $this->owner->parameters()->get('registration_list_page', 0);
        $surfers = $this->owner->communityConnector()->getLatestRegisteredSurfers(
          time() - $timeframe,
          $this->pagingItemsPerPage,
          $page > 0 ? ($page - 1) * $this->pagingItemsPerPage : 0
        );
        $this->pagingItemsAbsCount = $this->owner->communityConnector()->surferAdmin->surfersAbsCount;
        $this->getSurfer(array_keys($surfers));
        break;

      case 'contacts_and_requests':
        if ($ressource->validSurfer === 'is_selected') {
          $this->pagingItemsAbsCount = array();
          // load contacts by current surfer
          $page = $this->owner->parameters()->get('contacts_list_page', 0);
          $surfers['contacts'] = $this->owner->communityConnector()->getContacts(
            $ressource->id,
            FALSE,
            $this->pagingItemsPerPage,
            $page > 0 ? ($page - 1) * $this->pagingItemsPerPage : 0
          );
          $this->pagingItemsAbsCount['contacts'] = $this->owner->communityConnector()->contactsAbsCount;
          // load own contact requests by current surfer
          $page = $this->owner->parameters()->get('own_contact_requests_list_page', 0);
          $surfers['own_contact_requests'] = $this->owner->communityConnector()->getContactRequestsSent(
            $ressource->id,
            FALSE,
            $this->pagingItemsPerPage,
            $page > 0 ? ($page - 1) * $this->pagingItemsPerPage : 0
          );
          $this->pagingItemsAbsCount['own_contact_requests'] = $this->owner->communityConnector()->contactsAbsCount;
          // load contact requests by current surfer
          $page = $this->owner->parameters()->get('contact_requests_list_page', 0);
          $surfers['contact_requests'] = $this->owner->communityConnector()->getContactRequestsReceived(
            $ressource->id,
            FALSE,
            $this->pagingItemsPerPage,
            $page > 0 ? ($page - 1) * $this->pagingItemsPerPage : 0
          );
          $this->pagingItemsAbsCount['contact_requests'] = $this->owner->communityConnector()->contactsAbsCount;
          // merge surfer ids to get additional surfers' data
          $contactSurferIds = array_flip($surfers['contacts']);
          $ownRequestSurferIds = array_flip($surfers['own_contact_requests']);
          $requestSurferIds = array_flip($surfers['contact_requests']);
          $surferIds = array_keys(array_merge(
            $contactSurferIds, $ownRequestSurferIds, $requestSurferIds
          ));
          $this->getSurfer($surferIds);
          // create action links by loaded surfers
          $surfers['links'] = array();
          foreach ($surferIds as $surferId) {
            $reference = clone $this->reference();
            if (isset($ownRequestSurferIds[$surferId])) {
              // remove contact request for own requests
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
              // accept contact request for requests
              $referenceAccept = clone $reference;
              $referenceAccept->setParameters(
                array(
                  'command' => 'accept_contact_request',
                  'surfer_handle' => $this->_surfers[$surferId]['handle']
                ),
                $this->owner->parameterGroup()
              );
              // decline contact request for requests
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
              // remove contact for existing contacts
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
        }
        break;

      case 'contacts':
        // load surfers by contacts
        $page = $this->owner->parameters()->get('contacts_list_page', 0);
        $contactIds = $this->owner->communityConnector()->getContacts(
          $ressource->id,
          FALSE,
          $this->pagingItemsPerPage,
          $page > 0 ? ($page - 1) * $this->pagingItemsPerPage : 0
        );
        $this->pagingItemsAbsCount = $this->owner->communityConnector()->contactsAbsCount;
        $surfers = $this->getSurfer($contactIds);
        break;

      case 'surfers':
        // load group surfer relations in group modes
        if ($ressource->type == 'group') {
          $surferIds = NULL;
          if ($ressource->displayMode == 'members') {
            $this->owner->acommunityConnector()->groupSurferRelations()->content()->load(
              array('id' => $ressource->id, 'surfer_status_pending' => 0)
            );
            $groupSurferRelations = $this->owner->acommunityConnector()->groupSurferRelations()
              ->content()->toArray();
            $surferIds = array_keys($groupSurferRelations);
            if (empty($surferIds)) {
              return FALSE;
            }
          } elseif ($ressource->displayMode == 'membership_invitations') {
            $this->owner->acommunityConnector()->groupSurferRelations()->content()->load(
              array('id' => $ressource->id, 'surfer_status_pending' => 2)
            );
            $groupSurferRelations =$this->owner->acommunityConnector()->groupSurferRelations()
              ->content()->toArray();
            $surferIds = array_keys($groupSurferRelations);
            if (empty($surferIds)) {
              return FALSE;
            }
          } elseif ($ressource->displayMode == 'membership_requests') {
            $this->owner->acommunityConnector()->groupSurferRelations()->content()->load(
              array('id' => $ressource->id, 'surfer_status_pending' => 1)
            );
            $groupSurferRelations = $this->owner->acommunityConnector()->groupSurferRelations()
              ->content()->toArray();
            $surferIds = array_keys($groupSurferRelations);
            if (empty($surferIds)) {
              return FALSE;
            }
          } elseif ($ressource->displayMode == 'invite_surfers') {
            $this->owner->acommunityConnector()->groupSurferRelations()->content()->load(
              array('id' => $ressource->id)
            );
            $groupSurferRelations = $this->owner->acommunityConnector()->groupSurferRelations()
              ->content()->toArray();
          }
        }
        // get list limit / offset
        $page = $this->owner->parameters()->get('surfers_list_page', 0);
        $offset = $page > 0 ? ($page - 1) * $this->pagingItemsPerPage : 0;
        // get character filter or search filter
        $character = $this->owner->parameters()->get('surfers_character', NULL);
        if (!empty($character)) {
          $patternFirstChar = TRUE;
          $search = $character;
        } else {
          $patternFirstChar = FALSE;
          $search = $this->owner->parameters()->get('surfers_search', NULL);
          if (!empty($search)) {
            $search = explode(' ', $search);
          }
        }
        // get search fields and order by condition by display mode surfer name
        $displayModeSurferName = $this->owner->acommunityConnector()->getDisplayModeSurferName();
        switch ($displayModeSurferName) {
          case 'all':
            $orderBy = array('surfer_givenname', 'surfer_handle', 'surfer_surname');
            if (!empty($character)) {
              $searchFields = array('surfer_givenname');
            } else {
              $searchFields = NULL;
            }
            break;
          case 'names':
            $orderBy = array('surfer_givenname', 'surfer_surname');
            if (!empty($character)) {
              $searchFields = array('surfer_givenname');
            } else {
              $searchFields = array('surfer_givenname', 'surfer_surname');
            }
            break;
          case 'handle':
            $orderBy = array('surfer_handle');
            $searchFields = array('surfer_handle');
            break;
          case 'givenname':
            $orderBy = array('surfer_givenname');
            $searchFields = array('surfer_givenname');
            break;
          case 'surname':
            $orderBy = array('surfer_surname');
            $searchFields = array('surfer_surname');
            break;
        }
        // load surfers by search / filter parameters
        $surfers = $this->owner->communityConnector()->searchSurfers(
          $search, $searchFields, FALSE, $orderBy, $this->pagingItemsPerPage, $offset,
          $patternFirstChar, $surferIds
        );
        $this->pagingItemsAbsCount = $this->owner->communityConnector()->surferAdmin->surfersAbsCount;
        $surfers = $this->getSurfer(array_keys($surfers), NULL, $surfers);
        // action commands for group modes
        if ($ressource->type == 'group' && $ressource->validSurfer === 'is_owner') {
          if ($mode == 'members') {
            // add remove member in members mode
            foreach ($surfers as $key => $surfer) {
              if ($surfer['id'] != $this->currentSurferId()) {
                $reference = clone $this->reference();
                $reference->setParameters(
                  array('command' => 'remove_member', 'surfer_handle' => $surfer['handle']),
                  $this->owner->parameterGroup()
                );
                $surfers[$key]['commands']['remove_member'] = $reference->getRelative();
              }
            }
          } elseif ($mode == 'invite_surfers') {
            // add invite commands in invite_surfers mode
            foreach ($surfers as $key => $surfer) {
              if (!isset($groupSurferRelations[$surfer['id']]) &&
                  $surfer['id'] != $this->currentSurferId()) {
                $reference = clone $this->reference();
                $reference->setParameters(
                  array('command' => 'invite_surfer', 'surfer_handle' => $surfer['handle']),
                  $this->owner->parameterGroup()
                );
                $surfers[$key]['commands']['invite_surfer'] = $reference->getRelative();
              }
            }
          } elseif ($mode == 'membership_invitations') {
            // add remove invitation commands in invitations mode
            foreach ($surfers as $key => $surfer) {
              $reference = clone $this->reference();
              $reference->setParameters(
                array('command' => 'remove_invitation', 'surfer_handle' => $surfer['handle']),
                $this->owner->parameterGroup()
              );
              $surfers[$key]['commands']['remove_invitation'] = $reference->getRelative();
            }
          } elseif ($mode == 'membership_requests') {
            // add accept / decline requests commands in requests mode
            foreach ($surfers as $key => $surfer) {
              $reference = clone $this->reference();
              $reference->setParameters(
                array('command' => 'accept_request', 'surfer_handle' => $surfer['handle']),
                $this->owner->parameterGroup()
              );
              $surfers[$key]['commands']['accept_request'] = $reference->getRelative();
              $reference = clone $this->reference();
              $reference->setParameters(
                array('command' => 'decline_request', 'surfer_handle' => $surfer['handle']),
                $this->owner->parameterGroup()
              );
              $surfers[$key]['commands']['decline_request'] = $reference->getRelative();
            }
          }
        }
        break;
    }

    // create surfers list by previous loaded surfers
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
        foreach ($surfers as $surfer) {
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
      $this->_contactChanges = $changes;
    } elseif (is_null($this->_contactChanges)) {
      include_once(dirname(__FILE__).'/../Surfer/Contact/Changes.php');
      $this->_contactChanges = new ACommunitySurferContactChanges();
      $this->_contactChanges->papaya($this->papaya());
      $this->_contactChanges->owner = $this->owner;
      $this->_contactChanges->data = $this;
    }
    return $this->_contactChanges;
  }
}