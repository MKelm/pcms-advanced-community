<?php
/**
 * Advanced community ui content groups list
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
 * Advanced community ui content groups list
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityUiContentGroupsList extends PapayaUiControl {

  /**
  * Object buffer for groups
  *
  * @var ACommunityUiContentGroups
  */
  protected $_groups = NULL;

  /**
  * Messages data
  * @var ACommunityGroupsData
  */
  protected $_data = NULL;

  /**
  * Declared public properties, see property annotaiton of the class for documentation.
  *
  * @var array
  */
  protected $_declaredProperties = array(
    'groups' => array('groups', 'groups')
  );

  /**
   * Get/set groups data
   *
   * @param ACommunityGroupsData $data
   * @return ACommunityGroupsData
   */
  public function data(ACommunityGroupsData $data = NULL) {
    if (isset($data)) {
      $this->_data = $data;
    }
    return $this->_data;
  }

  /**
  * Fill comments list with comments data
  */
  private function fill() {
    $groupsList = $this->data()->groupsList();
    if (count($groupsList['data']) > 0) {
      $commandLinks = $this->data()->commandLinks();
      $this->groups()->absCount = $groupsList['abs_count'];
      foreach ($groupsList['data'] as $id => $groupData) {
        include_once(dirname(__FILE__).'/../Group.php');
        $group = new ACommunityUiContentGroup(
          $groupData['id'],
          $groupData['title'],
          $groupData['time'],
          $groupData['image'],
          $groupData['page_link'],
          isset($groupData['public']) ? $groupData['public'] : NULL
        );
        if (isset($commandLinks[$id]) && isset($commandLinks[$id]['delete'])) {
          $group->deleteLink = $commandLinks[$id]['delete'];
          $group->deleteLinkCaption = $this->data()->captions['command_delete'];
        }
        if (isset($commandLinks[$id]) && isset($commandLinks[$id]['remove_membership'])) {
          $group->removeMembershipLink = $commandLinks[$id]['remove_membership'];
          $group->removeMembershipLinkCaption = $this->data()->captions['command_remove_membership'];
        }
        if (isset($commandLinks[$id]) && isset($commandLinks[$id]['edit'])) {
          $group->editLink = $commandLinks[$id]['edit'];
          $group->editLinkCaption = $this->data()->captions['command_edit'];
        }
        if (isset($commandLinks[$id]) && isset($commandLinks[$id]['accept_invitation'])) {
          $group->acceptInvitationLink = $commandLinks[$id]['accept_invitation'];
          $group->acceptInvitationLinkCaption = $this->data()->captions['command_accept_invitation'];
        }
        if (isset($commandLinks[$id]) && isset($commandLinks[$id]['decline_invitation'])) {
          $group->declineInvitationLink = $commandLinks[$id]['decline_invitation'];
          $group->declineInvitationLinkCaption = $this->data()->captions['command_decline_invitation'];
        }
        if (isset($commandLinks[$id]) && isset($commandLinks[$id]['remove_request'])) {
          $group->removeRequestLink = $commandLinks[$id]['remove_request'];
          $group->removeRequestLinkCaption = $this->data()->captions['command_remove_request'];
        }
        $this->groups[] = $group;
      }
    }
  }

  /**
  * Append groups output to parent element.
  *
  * @param PapayaXmlElement $parent
  */
  public function appendTo(PapayaXmlElement $parent) {
    $this->fill();
    if (count($this->groups()->toArray()) == 0) {
      $mode = $this->data()->owner->parameters()->get('mode', NULL);
      switch ($mode) {
        case 'invitations':
          $message = $this->data()->messages['no_invitations'];
          break;
        case 'requests':
          $message = $this->data()->messages['no_requests'];
          break;
        default:
          $message = $this->data()->messages['no_groups'];
      }
      $parent->appendElement('message', array('type' => 'error'), $message);
    } else {
      $this->groups()->appendTo($parent);
    }
  }

  /**
  * The list of groups
  *
  * @param ACommunityUiContentGroups $groups
  */
  public function groups(ACommunityUiContentGroups $groups = NULL) {
    if (isset($groups)) {
      $this->_groups = $groups;
    } elseif (is_null($this->_groups)) {
      include_once(dirname(__FILE__).'/../Groups.php');
      $this->_groups = new ACommunityUiContentGroups($this);
      $this->_groups->papaya($this->papaya());
      $this->_groups->pagingParameterGroup = $this->data()->owner->parameterGroup();
      $this->_groups->pagingItemsPerPage = (int)$this->data()->pagingItemsPerPage;
      $this->_groups->reference($this->data()->reference());
    }
    return $this->_groups;
  }
}