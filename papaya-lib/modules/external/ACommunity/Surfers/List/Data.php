<?php
/**
 * Advanced community surfers list data class to handle all sorts of related data
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
 * Advanced community surfers list data class to handle all sorts of related data
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunitySurfersListData extends ACommunityUiContentData {

  /**
   * Surfers data
   * @var array
   */
  public $surfers = NULL;

  /**
   * Avatar size
   * @var integer
   */
  protected $_avatarSize = 0;

  /**
   * Avatar resize mode
   * @var string
   */
  protected $_avatarResizeMode = 'mincrop';

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
   * Surfers limit
   * @var string
   */
  protected $_limit = NULL;

  /**
   * Perform changes to contact data
   * @var ACommunitySurferContactChanges
   */
  protected $_contactChanges = NULL;

  /**
   * Set data by plugin object
   *
   * @param array $data
   * @param array $captionNames
   * @param array $messageNames
   */
  public function setPluginData($data, $captionNames = array(), $messageNames = array()) {
    $this->_avatarSize = (int)$data['avatar_size'];
    $this->_avatarResizeMode = $data['avatar_resize_mode'];
    $this->displayMode = $data['display_mode'];
    $this->_timeframe = $data['timeframe'];
    $this->_limit = $data['limit'];
    parent::setPluginData($data, $captionNames, $messageNames);
  }

  /**
   * Intitialize surfer data
   */
  public function initialize() {
    $timeframe = 60 * 60 * 24 * $this->_timeframe;
    $ressource = $this->ressource();
    if ($this->displayMode == 'lastaction') {
      $surfers = $this->owner->communityConnector()->getLastActiveSurfers(
        $timeframe, $this->_limit
      );
      if (!empty($surfers)) {
        $surfers = reset($surfers);
      }
    } elseif ($this->displayMode == 'registration') {
      $surfers = $this->owner->communityConnector()->getLatestRegisteredSurfers(
        time() - $timeframe, $this->_limit
      );
    } elseif ($this->displayMode == 'contacts_and_requests' &&
              $this->ressourceIsActiveSurfer == TRUE) {
      $surfers = array(
        'contacts' => $this->owner->communityConnector()->getContacts(
          $this->currentSurferId(), FALSE, $this->_limit
        ),
        'own_contact_requests' => $this->owner->communityConnector()->getContactRequestsSent(
          $this->currentSurferId(), FALSE, $this->_limit
        ),
        'contact_requests' => $this->owner->communityConnector()->getContactRequestsReceived(
          $this->currentSurferId(), FALSE, $this->_limit
        )
      );
      $contactSurferIds = array_flip($surfers['contacts']);
      $ownRequestSurferIds = array_flip($surfers['own_contact_requests']);
      $requestSurferIds = array_flip($surfers['contact_requests']);
      $surferIds = array_keys(array_merge(
        $contactSurferIds, $ownRequestSurferIds, $requestSurferIds
      ));
      $surfers['data'] = $this->owner->communityConnector()->getNameById($surferIds);
      foreach ($surferIds as $surferId) {
        if (isset($surfers['data'][$surferId])) {
          $reference = clone $this->reference();
          if (isset($ownRequestSurferIds[$surferId])) {
            $reference->setParameters(
              array(
                'command' => 'remove_contact_request',
                'surfer_handle' => $surfers['data'][$surferId]['surfer_handle']
              ),
              $this->owner->parameterGroup()
            );
            $surfers['data'][$surferId]['commands'] = array(
              'remove_contact_request' => $reference->getRelative()
            );
          } elseif (isset($requestSurferIds[$surferId])) {
            $referenceAccept = $reference;
            $referenceAccept->setParameters(
              array(
                'command' => 'accept_contact_request',
                'surfer_handle' => $surfers['data'][$surferId]['surfer_handle']
              ),
              $this->owner->parameterGroup()
            );
            $referenceDecline = clone $reference;
            $referenceDecline->setParameters(
              array(
                'command' => 'decline_contact_request',
                'surfer_handle' => $surfers['data'][$surferId]['surfer_handle']
              ),
              $this->owner->parameterGroup()
            );
            $surfers['data'][$surferId]['commands'] = array(
              'accept_contact_request' => $referenceAccept->getRelative(),
              'decline_contact_request' => $referenceDecline->getRelative()
            );
          } elseif (isset($contactSurferIds[$surferId])) {
            $reference->setParameters(
              array(
                'command' => 'remove_contact',
                'surfer_handle' => $surfers['data'][$surferId]['surfer_handle']
              ),
              $this->owner->parameterGroup()
            );
            $surfers['data'][$surferId]['commands'] = array(
              'remove_contact' => $reference->getRelative()
            );
          }
        }
      }
    } elseif ($this->displayMode == 'contacts') {
      $contactIds = $this->owner->communityConnector()->getContacts(
        $ressource['id'], FALSE, $this->_limit
      );
      $surfers = $this->owner->communityConnector()->getNameById($contactIds);
      foreach ($surfers as $surferId => $surfer) {
        $surfers[$surferId]['surfer_id'] = $surferId;
      }
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
            $this->surfers[$groupName][] = $this->_getSurfer($surferId, $surfers['data'][$surferId]);
          }
        }
      } else {
        foreach ($surfers as $surfer) {
          $this->surfers[] = $this->_getSurfer($surfer['surfer_id'], $surfer);
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
      'handle' => $surfer['surfer_handle'],
      'givenname' => $surfer['surfer_givenname'],
      'surname' => $surfer['surfer_surname'],
      'last_action' => !empty($surfer['surfer_lastaction']) ?
        date('Y-m-d H:i:s', $surfer['surfer_lastaction']) : NULL,
      'registration' => !empty($surfer['surfer_registration']) ?
        date('Y-m-d H:i:s', $surfer['surfer_registration']) : NULL,
      'avatar' => $this->owner->communityConnector()->getAvatar(
        $surferId, $this->_avatarSize, TRUE, $this->_avatarResizeMode
      ),
      'page_link' => $this->owner->acommunityConnector()->getSurferPageLink($surferId),
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
      include_once(dirname(__FILE__).'/../../Surfer/Contact/Changes.php');
      $this->_changes = new ACommunitySurferContactChanges();
      $this->_changes->papaya($this->papaya());
    }
    return $this->_changes;
  }

}