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
   * A regular expression to filter reference parameters by name
   * @var string
   */
  protected $_referenceParametersExpression = 'group_handle';

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
   * Intitialize surfer data
   */
  public function initialize() {
    // get group data from previous loaded data by ressource initialization
    $group = $this->owner->acommunityConnector()->groupSurferRelations()->group();
    $ressource = $this->owner->ressource();
    if (!empty($group->title)) {
      $this->title = $group->title;
      $this->time = date('Y-m-d H:i:s', $group->time);
      $this->text = $group->description;
      if (empty($group->image)) {
        $group->image = $this->owner->acommunityConnector()->getGroupsDefaultImageId();
      }
      include_once(PAPAYA_INCLUDE_PATH.'system/base_thumbnail.php');
      $thumbnail = new base_thumbnail;
      $this->image = 'media.thumb.'.$thumbnail->getThumbnail(
        $group->image, NULL, $this->_imageThumbnailSize, $this->_imageThumbnailSize,
        $this->_imageThumbnailResizeMode
      );

      $this->commands = array();
      if ($this->mode == 'group-bar') {
        // show gallery
        $this->commands['show_gallery'] = array(
          'href' => $this->owner->acommunityConnector()
            ->getGalleryPageLink($ressource->type, $ressource->id),
          'caption' => $this->captions['link_gallery'],
          'active' => (int)($ressource->displayMode == 'gallery')
        );
      } elseif ($this->mode == 'group-details') {
        // load member command links
        if ($ressource->validSurfer !== 'is_owner') {
          // link to group owner
          $surfer = $this->getSurfer($group->owner);
          $this->commands['owner'] = array(
            'href' => $surfer['page_link'],
            'caption' => sprintf($this->captions['link_owner'], $surfer['name'])
          );
        }

        if (!empty($this->currentSurferId())) {
          $surferStatus = $this->owner->acommunityConnector()->groupSurferRelations()->status(
            $group->id, $this->currentSurferId()
          );

          if (empty($surferStatus)) {
            // request membership
            $reference = clone $this->reference();
            $reference->setParameters(
              array('command' => 'request_membership'), $this->owner->parameterGroup()
            );
            $this->commands['request_membership'] = array(
              'href' => $reference->getRelative(),
              'caption' => $this->captions['link_request_membership']
            );
          } elseif ($surferStatus['is_pending'] == 1) {
            // remove membership request
            $reference = clone $this->reference();
            $reference->setParameters(
              array('command' => 'remove_membership_request'), $this->owner->parameterGroup()
            );
            $this->commands['remove_membership_request'] = array(
              'href' => $reference->getRelative(),
              'caption' => $this->captions['link_remove_membership_request']
            );
          } elseif ($surferStatus['is_pending'] == 2) {
            // accept membership invitation
            $reference = clone $this->reference();
            $reference->setParameters(
              array('command' => 'accept_membership_invitation'), $this->owner->parameterGroup()
            );
            $this->commands['accept_membership_invitation'] = array(
              'href' => $reference->getRelative(),
              'caption' => $this->captions['link_accept_membership_invitation']
            );
            // decline membership invitation
            $reference = clone $this->reference();
            $reference->setParameters(
              array('command' => 'decline_membership_invitation'), $this->owner->parameterGroup()
            );
            $this->commands['decline_membership_invitation'] = array(
              'href' => $reference->getRelative(),
              'caption' => $this->captions['link_decline_membership_invitation']
            );
          }
        }
      }

      if ($ressource->validSurfer === 'is_owner') {
        // invite surfers
        $this->commands['invite_surfers'] = array(
          'href' => $this->owner->acommunityConnector()
            ->getSurfersPageLink($this->languageId, 'invite_surfers', $ressource->handle),
          'caption' => $this->captions['link_invite_surfers'],
          'active' => (int)($ressource->displayMode == 'invite_surfers')
        );
        // n requests pending
        $this->owner->acommunityConnector()->groupSurferRelations()->content()->load(
          array('id' => $ressource->id, 'count' => 1, 'surfer_status_pending' => 1)
        );
        $groupSurferRelations = reset(
          $this->owner->acommunityConnector()->groupSurferRelations()->content()->toArray()
        );
        if (!empty($groupSurferRelations['count'])) {
          $this->commands['membership_requests'] = array(
            'href' => $this->owner->acommunityConnector()
              ->getSurfersPageLink($this->languageId, 'membership_requests', $ressource->handle),
            'caption' => sprintf(
              $groupSurferRelations['count'] > 1 ?
                $this->captions['link_membership_requests'] :
                $this->captions['link_membership_request'],
              $groupSurferRelations['count']
            ),
            'active' => (int)($ressource->displayMode == 'membership_requests')
          );
        }
        // n invites pending
        $this->owner->acommunityConnector()->groupSurferRelations()->content()->load(
          array('id' => $ressource->id, 'count' => 1, 'surfer_status_pending' => 2)
        );
        $groupSurferRelations = reset(
          $this->owner->acommunityConnector()->groupSurferRelations()->content()->toArray()
        );
        if (!empty($groupSurferRelations['count'])) {
          $this->commands['membership_invitations'] = array(
            'href' => $this->owner->acommunityConnector()
              ->getSurfersPageLink($this->languageId, 'membership_invitations', $ressource->handle),
            'caption' => sprintf(
              $groupSurferRelations['count'] > 1 ?
                $this->captions['link_membership_invitations'] :
                $this->captions['link_membership_invitation'],
              $groupSurferRelations['count']
            ),
            'active' => (int)($ressource->displayMode == 'membership_invitations')
          );
        }
      }

      // all members without owner
      $this->owner->acommunityConnector()->groupSurferRelations()->content()->load(
        array('id' => $ressource->id, 'count' => 1, 'surfer_status_pending' => 0)
      );
      $groupSurferRelations = reset(
        $this->owner->acommunityConnector()->groupSurferRelations()->content()->toArray()
      );
      if (!empty($groupSurferRelations['count'])) {
        $this->commands['memberships'] = array(
          'href' => $this->owner->acommunityConnector()
            ->getSurfersPageLink($this->languageId, 'members', $ressource->handle),
          'caption' => sprintf(
            $groupSurferRelations['count'] > 1 ?
              $this->captions['link_members'] : $this->captions['link_member'],
            $groupSurferRelations['count']
          ),
          'active' => (int)($ressource->displayMode == 'members')
        );
      }
      return TRUE;
    }
    return FALSE;
  }

}