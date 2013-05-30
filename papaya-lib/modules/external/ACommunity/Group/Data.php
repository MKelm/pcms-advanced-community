<?php
/**
 * Advanced community group data class to handle all sorts of related data
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
 * Advanced community group data class to handle all sorts of related data
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityGroupData extends ACommunityUiContentData {

  /**
   * Group title
   * @var string
   */
  public $title = NULL;

  /**
   * Group creation time
   * @var string
   */
  public $time = NULL;

  /**
   * Group text contains description
   * @var string
   */
  public $text = NULL;

  /**
   * Group image
   * @var string
   */
  public $image = NULL;

  /**
   * Image size
   * @var integer
   */
  protected $_imageThumbnailSize = NULL;

  /**
   * Image resize mode
   * @var string
   */
  protected $_imageThumbnailResizeMode = NULL;

  /**
   * Group database record
   * @var object
   */
  protected $_group = NULL;

  /**
   * Group surfer relation database record
   * @var object
   */
  protected $_groupSurferRelation = NULL;

  /**
   * Group surfer relations database records
   * @var object
   */
  protected $_groupSurferRelations = NULL;

  /**
   * Boolean status of surfer in relation to the current group
   * @var array
   */
  protected $_surferGroupStatus = NULL;

  /**
   * A regular expression to filter reference parameters by name
   * @var string
   */
  protected $_referenceParametersExpression = 'group_handle';

  /**
   * Set data by plugin object
   *
   * @param array $data
   * @param array $captionNames
   * @param array $messageNames
   */
  public function setPluginData($data, $captionNames = array(), $messageNames = array()) {
    $this->_imageThumbnailSize = (int)$data['image_size'];
    $this->_imageThumbnailResizeMode = $data['image_resize_mode'];
    parent::setPluginData($data, $captionNames, $messageNames);
  }

  /**
  * Access to group database record data
  *
  * @param ACommunityContentGroup $group
  * @return ACommunityContentGroup
  */
  public function group(ACommunityContentGroup $group = NULL) {
    if (isset($group)) {
      $this->_group = $group;
    } elseif (is_null($this->_group)) {
      include_once(dirname(__FILE__).'/../Content/Group.php');
      $this->_group = new ACommunityContentGroup();
      $this->_group->papaya($this->papaya());
    }
    return $this->_group;
  }

  /**
  * Access to group surfer relation database record data
  *
  * @param ACommunityContentGroupSurferRelation $group
  * @return ACommunityContentGroupSurferRelation
  */
  public function groupSurferRelation(
           ACommunityContentGroupSurferRelation $groupSurferRelation = NULL
         ) {
    if (isset($groupSurferRelation)) {
      $this->_groupSurferRelation = $groupSurferRelation;
    } elseif (is_null($this->_groupSurferRelation)) {
      include_once(dirname(__FILE__).'/../Content/Group/Surfer/Relation.php');
      $this->_groupSurferRelation = new ACommunityContentGroupSurferRelation();
      $this->_groupSurferRelation->papaya($this->papaya());
    }
    return $this->_groupSurferRelation;
  }

  /**
  * Access to group surfer relations database records data
  *
  * @param ACommunityContentGroupSurferRelations $group
  * @return ACommunityContentGroupSurferRelations
  */
  public function groupSurferRelations(
           ACommunityContentGroupSurferRelations $groupSurferRelations = NULL
         ) {
    if (isset($groupSurferRelations)) {
      $this->_groupSurferRelations = $groupSurferRelations;
    } elseif (is_null($this->_groupSurferRelations)) {
      include_once(dirname(__FILE__).'/../Content/Group/Surfer/Relations.php');
      $this->_groupSurferRelations = new ACommunityContentGroupSurferRelations();
      $this->_groupSurferRelations->papaya($this->papaya());
    }
    return $this->_groupSurferRelations;
  }

  /**
   * Status of the current surfer in relation to the selected group
   *
   * @return string
   */
  public function surferGroupStatus() {
    if (!isset($this->_surferGroupStatus)) {
      $this->_surferGroupStatus = array();
      $ressource = $this->ressource();
      if (!empty($ressource)) {
        $this->groupSurferRelations()->load(
          array('id' => $ressource['id'], 'surfer_id' => $this->currentSurferId())
        );
        $groupSurferRelations = $this->groupSurferRelations();
        if (isset($groupSurferRelations[$ressource['id']])) {
          $this->_surferGroupStatus = $groupSurferRelations[$ressource['id']];
        }
      }
    }
    return $this->_surferGroupStatus;
  }

  /**
   * Intitialize surfer data
   */
  public function initialize() {
    $ressource = $this->ressource();
    if (!empty($ressource)) {
      $this->group()->load($ressource['id']);
      $group = $this->group()->toArray();
      if (!empty($group['title'])) {
        $this->title = $group['title'];
        $this->time = date('Y-m-d H:i:s', $group['time']);
        $this->text = $group['description'];
        if (empty($group['image'])) {
          $group['image'] = $this->owner->acommunityConnector()->getGroupsDefaultImageId();
        }
        include_once(PAPAYA_INCLUDE_PATH.'system/base_thumbnail.php');
        $thumbnail = new base_thumbnail;
        $this->image = 'media.thumb.'.$thumbnail->getThumbnail(
          $group['image'], NULL, $this->_imageThumbnailSize, $this->_imageThumbnailSize,
          $this->_imageThumbnailResizeMode
        );
      }

      $this->commands = array();
      $surferGroupStatus = $this->surferGroupStatus();
      $referenceParameters = $this->referenceParameters();

      // load member command links
      if ($surferGroupStatus == NULL) {
        // request membership
        $reference = clone $this->reference();
        $reference->setParameters(
          array('command' => 'request_membership'),
          $this->owner->parameterGroup()
        );
        $this->commands['request_membership'] = array(
          'href' => $reference->getRelative(),
          'caption' => $this->captions['command_request_membership']
        );
      } elseif (!empty($surferGroupStatus['is_pending']) && $surferGroupStatus['is_pending'] == 1) {
        // remove membership request
        $reference = clone $this->reference();
        $reference->setParameters(
          array('command' => 'remove_membership_request'),
          $this->owner->parameterGroup()
        );
        $this->commands['remove_membership_request'] = array(
          'href' => $reference->getRelative(),
          'caption' => $this->captions['command_remove_membership_request']
        );
      } elseif (!empty($surferGroupStatus['is_pending']) && $surferGroupStatus['is_pending'] == 2) {
        // accept membership invitation
        $reference = clone $this->reference();
        $reference->setParameters(
          array('command' => 'accept_membership_invitation'),
          $this->owner->parameterGroup()
        );
        $this->commands['accept_membership_invitation'] = array(
          'href' => $reference->getRelative(),
          'caption' => $this->captions['command_accept_membership_invitation']
        );
      } elseif (!empty($surferGroupStatus['is_owner'])) {
        // invite surfers
        $this->commands['invite_surfers'] = array(
          'href' => $this->owner->acommunityConnector()
            ->getSurfersPageLink(
              $this->languageId, 'invite_surfers', $referenceParameters['group_handle']
            ),
          'caption' => $this->captions['command_invite_surfers']
        );
      }

      if (!empty($surferGroupStatus['is_owner'])) {
        // n requests pending
        $this->groupSurferRelations()->load(
          array('id' => $ressource['id'], 'count' => 1, 'surfer_status_pending' => 1)
        );
        $groupSurferRelations = reset($this->groupSurferRelations()->toArray());
        if (!empty($groupSurferRelations['count'])) {
          $this->commands['membership_requests'] = array(
            'href' => $this->owner->acommunityConnector()
              ->getSurfersPageLink(
                $this->languageId, 'membership_requests', $referenceParameters['group_handle']
              ),
            'caption' => sprintf(
              $groupSurferRelations['count'] > 1 ?
                $this->captions['command_membership_requests'] :
                $this->captions['command_membership_request'],
              $groupSurferRelations['count']
            )
          );
        }
        // n invites pending
        $this->groupSurferRelations()->load(
          array('id' => $ressource['id'], 'count' => 1, 'surfer_status_pending' => 2)
        );
        $groupSurferRelations = reset($this->groupSurferRelations()->toArray());
        if (!empty($groupSurferRelations['count'])) {
          $this->commands['membership_invitations'] = array(
            'href' => $this->owner->acommunityConnector()
              ->getSurfersPageLink(
                $this->languageId, 'membership_invitations', $referenceParameters['group_handle']
              ),
            'caption' => sprintf(
              $groupSurferRelations['count'] > 1 ?
                $this->captions['command_membership_invitations'] :
                $this->captions['command_membership_invitation'],
              $groupSurferRelations['count']
            )
          );
        }
      }
    }
  }
}