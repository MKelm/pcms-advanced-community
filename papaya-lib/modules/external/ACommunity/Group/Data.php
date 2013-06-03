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
require_once(dirname(__FILE__).'/../Ui/Content/Data/Group/Surfer/Relations.php');

/**
 * Advanced community group data class to handle all sorts of related data
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityGroupData extends ACommunityUiContentDataGroupSurferRelations {

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
   * A regular expression to filter reference parameters by name
   * @var string
   */
  protected $_referenceParametersExpression = 'group_handle';

  /**
   * Flag of surfer group access status
   * @var boolean
   */
  protected $_surferHasGroupAccess = NULL;

  /**
   * group-details or group-bar mode
   * @var string
   */
  public $mode = 'group-details';

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
   * Detects group access by surfer
   *
   * @return boolean
   */
  public function surferHasGroupAccess(boolean $surferHasGroupAccess = NULL) {
    if (isset($surferHasGroupAccess)) {
      $this->_surferHasGroupAccess = $surferHasGroupAccess;
    } elseif (is_null($this->_surferHasGroupAccess)) {
      $ressource = $this->ressource('ressource');
      if (!empty($ressource)) {
        $this->group()->load($ressource->id);
        if ($this->group()->public == 0) {
          if ($this->surferHasStatus(NULL, 'is_owner', 1) ||
              $this->surferHasStatus(NULL, 'is_member', 1)) {
            $this->owner->module->surferHasGroupAccess = TRUE;
            $this->_surferHasGroupAccess = TRUE;
          } else {
            $this->_surferHasGroupAccess = FALSE;
          }
        } else {
          $this->owner->module->surferHasGroupAccess = TRUE;
          $this->_surferHasGroupAccess = TRUE;
        }
      }
    }
    return $this->_surferHasGroupAccess;
  }

  /**
   * Intitialize surfer data
   */
  public function initialize() {
    if ($this->surferHasGroupAccess()) {
      $ressource = $this->ressource('ressource');
      if ($this->mode == 'group-bar') {
        $this->group()->load($ressource->id);
      }
      if (!empty($this->group()->title)) {
        $this->title = $this->group()->title;
        $this->time = date('Y-m-d H:i:s', $this->group()->time);
        $this->text = $this->group()->description;
        if (empty($this->group()->image)) {
          $this->group()->image = $this->owner->acommunityConnector()->getGroupsDefaultImageId();
        }
        include_once(PAPAYA_INCLUDE_PATH.'system/base_thumbnail.php');
        $thumbnail = new base_thumbnail;
        $this->image = 'media.thumb.'.$thumbnail->getThumbnail(
          $this->group()->image, NULL, $this->_imageThumbnailSize, $this->_imageThumbnailSize,
          $this->_imageThumbnailResizeMode
        );

        if ($this->mode == 'group-details') {
          $this->commands = array();
          $referenceParameters = $this->referenceParameters();

          // load member command links
          if (!$this->surferHasStatus(NULL, 'is_owner', 1)) {
            // link to group owner
            $surfer = $this->getSurfer($this->group()->owner);
            $this->commands['owner'] = array(
              'href' => $surfer['page_link'],
              'caption' => sprintf($this->captions['link_owner'], $surfer['name'])
            );
          }
          if (!$this->surferHasStatus(NULL)) {
            // request membership
            $reference = clone $this->reference();
            $reference->setParameters(
              array('command' => 'request_membership'),
              $this->owner->parameterGroup()
            );
            $this->commands['request_membership'] = array(
              'href' => $reference->getRelative(),
              'caption' => $this->captions['link_request_membership']
            );
          } elseif ($this->surferHasStatus(NULL, 'is_pending', 1)) {
            // remove membership request
            $reference = clone $this->reference();
            $reference->setParameters(
              array('command' => 'remove_membership_request'),
              $this->owner->parameterGroup()
            );
            $this->commands['remove_membership_request'] = array(
              'href' => $reference->getRelative(),
              'caption' => $this->captions['link_remove_membership_request']
            );
          } elseif ($this->surferHasStatus(NULL, 'is_pending', 2)) {
            // accept membership invitation
            $reference = clone $this->reference();
            $reference->setParameters(
              array('command' => 'accept_membership_invitation'),
              $this->owner->parameterGroup()
            );
            $this->commands['accept_membership_invitation'] = array(
              'href' => $reference->getRelative(),
              'caption' => $this->captions['link_accept_membership_invitation']
            );
            // decline membership invitation
            $reference = clone $this->reference();
            $reference->setParameters(
              array('command' => 'decline_membership_invitation'),
              $this->owner->parameterGroup()
            );
            $this->commands['decline_membership_invitation'] = array(
              'href' => $reference->getRelative(),
              'caption' => $this->captions['link_decline_membership_invitation']
            );
          }

          if ($this->surferHasStatus(NULL, 'is_owner', 1)) {
            // invite surfers
            $this->commands['invite_surfers'] = array(
              'href' => $this->owner->acommunityConnector()
                ->getSurfersPageLink(
                  $this->languageId, 'invite_surfers', $referenceParameters['group_handle']
                ),
              'caption' => $this->captions['link_invite_surfers']
            );
            // n requests pending
            $this->groupSurferRelations()->load(
              array('id' => $ressource->id, 'count' => 1, 'surfer_status_pending' => 1)
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
                    $this->captions['link_membership_requests'] :
                    $this->captions['link_membership_request'],
                  $groupSurferRelations['count']
                )
              );
            }
            // n invites pending
            $this->groupSurferRelations()->load(
              array('id' => $ressource->id, 'count' => 1, 'surfer_status_pending' => 2)
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
                    $this->captions['link_membership_invitations'] :
                    $this->captions['link_membership_invitation'],
                  $groupSurferRelations['count']
                )
              );
            }
          }

          // all members with owner
          $this->groupSurferRelations()->load(
            array('id' => $ressource->id, 'count' => 1, 'surfer_status_pending' => 0)
          );
          $groupSurferRelations = reset($this->groupSurferRelations()->toArray());
          if (!empty($groupSurferRelations['count'])) {
            $this->commands['memberships'] = array(
              'href' => $this->owner->acommunityConnector()
                ->getSurfersPageLink(
                  $this->languageId, 'members', $referenceParameters['group_handle']
                ),
              'caption' => sprintf(
                $groupSurferRelations['count'] > 1 ?
                  $this->captions['link_members'] : $this->captions['link_member'],
                $groupSurferRelations['count']
              )
            );
          }
        }
        return TRUE;
      }
    }
    return FALSE;
  }

}