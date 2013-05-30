<?php
/**
 * Advanced community group surfer changes
 *
 * This class offers methods to delete and modify community data on surfer deletion
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
 * Load base contacts object for constants
 */
require_once(PAPAYA_INCLUDE_PATH.'modules/_base/community/base_contacts.php');


/**
 * Advanced community group surfer changes
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityGroupSurferChanges extends PapayaObject {

  /**
   * Group surfer relation database record
   * @var object
   */
  protected $_groupSurferRelation = NULL;

  /**
   * Last cahnge database record
   * @var object
   */
  protected $_lastChange = NULL;

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
      include_once(dirname(__FILE__).'/../../Content/Group/Surfer/Relation.php');
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
      include_once(dirname(__FILE__).'/../../Content/Group/Surfer/Relations.php');
      $this->_groupSurferRelations = new ACommunityContentGroupSurferRelations();
      $this->_groupSurferRelations->papaya($this->papaya());
    }
    return $this->_groupSurferRelations;
  }

  /**
  * Access to last change database record data
  *
  * @param ACommunityContentLastChange $lastChange
  * @return ACommunityContentLastChange
  */
  public function lastChange(ACommunityContentLastChange $lastChange = NULL) {
    if (isset($lastChange)) {
      $this->_lastChange = $lastChange;
    } elseif (is_null($this->_lastChange)) {
      include_once(dirname(__FILE__).'/../../Content/Last/Change.php');
      $this->_lastChange = new ACommunityContentLastChange();
      $this->_lastChange->papaya($this->papaya());
    }
    return $this->_lastChange;
  }

  /**
   * Set last change time depending on ressource
   *
   * @param string $ressource
   * @return boolean
   */
  protected function _setLastChangeTime($ressource) {
    $lastChange = clone $this->lastChange();
    $lastChange->assign(array('ressource' => $ressource, 'time' => time()));
    if ($lastChange->save()) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Remove group to surfer membership
   *
   * @param integer $groupId
   * @param string $surferId
   * @param string $currentSurferId
   * @return boolean
   */
  public function removeMember($groupId, $surferId, $currentSurferId) {
    if (!empty($surferId) && $surferId != $currentSurferId) {
      $groupSurferRelation = clone $this->groupSurferRelation();
      $groupSurferRelation->load(
        array('id' => $groupId, 'surfer_id' => $surferId, 'surfer_status_pending' => 0)
      );
      if ($groupSurferRelation['id'] > 0) {
        if ($groupSurferRelation->delete()) {
          // change affects the amount of group's memberships
          $result1 = $this->_setLastChangeTime('group:memberships:group_'.$groupId);
          // change affects the amount of groups by the selected surfer
          $result2 = $this->_setLastChangeTime('groups:surfer_'.$surferId);
          return $result1 && $result2;
        }
      }
    }
    return FALSE;
  }

  /**
   * Add group to surfer invitation
   *
   * @param integer $groupId
   * @param string $surferId
   * @param string $currentSurferId
   * @return boolean
   */
  public function inviteSurfer($groupId, $surferId, $currentSurferId) {
    if (!empty($surferId) && $surferId != $currentSurferId) {
      $groupSurferRelation = clone $this->groupSurferRelation();
      $groupSurferRelation->load(
        array('id' => $groupId, 'surfer_id' => $surferId)
      );
      if ($groupSurferRelation['id'] == NULL) {
        $groupSurferRelation = clone $this->groupSurferRelation();
        $groupSurferRelation->assign(
          array(
            'id' => $groupId,
            'surfer_id' => $surferId,
            'surfer_status_pending' => 2
          )
        );
        if ($groupSurferRelation->save()) {
          // change affects the amount of group's membership invitations
          $result1 = $this->_setLastChangeTime('group:membership_invitations:group_'.$groupId);
          // change affects the amount of groups invitations by the invited surfer
          $result2 = $this->_setLastChangeTime('groups:membership_invitations:surfer_'.$surferId);
          return $result1 && $result2;
        }
      }
    }
    return FALSE;
  }

  /**
   * Remove group to surfer invitation
   *
   * @param integer $groupId
   * @param string $surferId
   * @param string $currentSurferId
   * @return boolean
   */
  public function removeInvitation($groupId, $surferId, $currentSurferId) {
    if (!empty($surferId) && $surferId != $currentSurferId) {
      $groupSurferRelation = clone $this->groupSurferRelation();
      $groupSurferRelation->load(
        array('id' => $groupId, 'surfer_id' => $surferId, 'surfer_status_pending' => 2)
      );
      if ($groupSurferRelation['id'] > 0) {
        if ($groupSurferRelation->delete()) {
          // change affects the amount of group's membership invitations
          return $this->_setLastChangeTime('group:membership_invitations:group_'.$groupId);
          // change affects the amount of groups invitations by the invited surfer
          $result2 = $this->_setLastChangeTime('groups:membership_invitations:surfer_'.$surferId);
        }
      }
    }
    return FALSE;
  }

  /**
   * Accept surfer to group request
   *
   * @param integer $groupId
   * @param string $surferId
   * @param string $currentSurferId
   * @return boolean
   */
  public function acceptRequest($groupId, $surferId, $currentSurferId) {
    if (!empty($surferId) && $surferId != $currentSurferId) {
      $groupSurferRelation = clone $this->groupSurferRelation();
      $groupSurferRelation->load(
        array('id' => $groupId, 'surfer_id' => $surferId, 'surfer_status_pending' => 1)
      );
      if ($groupSurferRelation['id'] > 0) {
        $groupSurferRelation = clone $this->groupSurferRelation();
        $groupSurferRelation->assign(
          array(
            'id' => $groupId,
            'surfer_id' => $surferId,
            'surfer_status_pending' => 0
          )
        );
        if ($groupSurferRelation->save()) {
          // change affects the amount of group's membership requests
          $result1 = $this->_setLastChangeTime('group:membership_requests:group_'.$groupId);
          // change affects the amount of group's memberships
          $result2 = $this->_setLastChangeTime('group:memberships:group_'.$groupId);
          // change affects the amount of groups by the new member
          $result3 = $this->_setLastChangeTime('groups:surfer_'.$surferId);
          // change affects the amount of requested groups by the new member
          $result4 = $this->_setLastChangeTime('groups:membership_requests:surfer_'.$surferId);
          return $result1 && $result2 && $result3 && $result4;
        }
      }
    }
    return FALSE;
  }

  /**
   * Decline surfer to group request
   *
   * @param integer $groupId
   * @param string $surferId
   * @param string $currentSurferId
   * @return boolean
   */
  public function declineRequest($groupId, $surferId, $currentSurferId) {
    if (!empty($surferId) && $surferId != $currentSurferId) {
      $groupSurferRelation = clone $this->groupSurferRelation();
      $groupSurferRelation->load(
        array('id' => $groupId, 'surfer_id' => $surferId, 'surfer_status_pending' => 1)
      );
      if ($groupSurferRelation['id'] > 0) {
        if ($groupSurferRelation->delete()) {
          // change affects the amount of group's membership requests
          $result1 = $this->_setLastChangeTime('group:membership_requests:group_'.$groupId);
          // change affects the amount of requested groups by the selected surfer
          $result2 = $this->_setLastChangeTime('groups:membership_requests:surfer_'.$surferId);
          return $result1 && $result2;
        }
      }
    }
    return FALSE;
  }

  /**
   * Add surfer to group request
   *
   * @param integer $groupId
   * @param string $surferId
   * @return boolean
   */
  public function requestMembership($groupId, $currentSurferId) {
    if (!empty($currentSurferId)) {
      $groupSurferRelation = clone $this->groupSurferRelation();
      $groupSurferRelation->load(
        array('id' => $groupId, 'surfer_id' => $currentSurferId)
      );
      if (empty($surferGroupStatus)) {
        $groupSurferRelation = clone $this->groupSurferRelation();
        $groupSurferRelation->assign(
          array(
            'id' => $groupId,
            'surfer_id' => $currentSurferId,
            'surfer_status_pending' => 1
          )
        );
        if ($groupSurferRelation->save()) {
          // change affects the amount of group's membership requests
          $result1 = $this->_setLastChangeTime('group:membership_requests:group_'.$groupId);
          // change affects the amount of requested groups by the selected surfer
          $result2 = $this->_setLastChangeTime('groups:membership_requests:surfer_'.$surferId);
          return $result1 && $result2;
        }
      }
    }
    return FALSE;
  }

  /**
   * Remove surfer to group request
   *
   * @param integer $groupId
   * @param string $currentSurferId
   * @return boolean
   */
  public function removeRequest($groupId, $currentSurferId) {
    if (!empty($currentSurferId)) {
      $groupSurferRelation = clone $this->groupSurferRelation();
      $groupSurferRelation->load(
        array('id' => $groupId, 'surfer_id' => $currentSurferId)
      );
      if ($groupSurferRelation['surfer_status_pending'] == 1) {
        $groupSurferRelation = $this->groupSurferRelation();
        $groupSurferRelation->load(
          array(
            'id' => $groupId,
            'surfer_id' => $currentSurferId,
            'surfer_status_pending' => 1
          )
        );
        if ($groupSurferRelation->delete()) {
          // change affects the amount of group's membership requests
          $result1 = $this->_setLastChangeTime('group:membership_requests:group_'.$groupId);
          // change affects the amount of requested groups by the selected surfer
          $result2 = $this->_setLastChangeTime('groups:membership_requests:surfer_'.$surferId);
          return $result1 && $result2;
        }
      }
    }
    return FALSE;
  }

  /**
   * Accept group to surfer invitation
   *
   * @param integer $groupId
   * @param string $currentSurferId
   * @return boolean
   */
  public function acceptInvitation($groupId, $currentSurferId) {
    if (!empty($currentSurferId)) {
      $groupSurferRelation = clone $this->groupSurferRelation();
      $groupSurferRelation->load(
        array('id' => $groupId, 'surfer_id' => $currentSurferId)
      );
      if ($groupSurferRelation['surfer_status_pending'] == 2) {
        $groupSurferRelation = clone $this->groupSurferRelation();
        $groupSurferRelation->assign(
          array(
            'id' => $groupId,
            'surfer_id' => $currentSurferId,
            'surfer_status_pending' => 0
          )
        );
        if ($groupSurferRelation->save()) {
          // change affects the amount of group's membership invitations
          $result1 = $this->_setLastChangeTime('group:membership_invitations:group_'.$groupId);
          // change affects the amount of group's memberships
          $result2 = $this->_setLastChangeTime('group:memberships:group_'.$groupId);
          // change affects the amount of groups by the new member
          $result3 = $this->_setLastChangeTime('groups:surfer_'.$currentSurferId);
          // change affects the amount of group invitations by the new member
          $result4 = $this->_setLastChangeTime('groups:membership_invitations:surfer_'.$currentSurferId);
          return $result1 && $result2 && $result3 && $result4;
        }
      }
    }
    return FALSE;
  }

  /**
   * Decline group to surfer invitation
   *
   * @param integer $groupId
   * @param string $currentSurferId
   * @return boolean
   */
  public function declineInvitation($groupId, $currentSurferId) {
    if (!empty($currentSurferId)) {
      $groupSurferRelation = clone $this->groupSurferRelation();
      $groupSurferRelation->load(
        array('id' => $groupId, 'surfer_id' => $currentSurferId)
      );
      if ($groupSurferRelation['surfer_status_pending'] == 2) {
        if ($groupSurferRelation->delete()) {
          // change affects the amount of group's membership requests
          $result1 = $this->_setLastChangeTime('group:membership_invitations:group_'.$groupId);
          // change affects the amount of group invitations by the current surfer
          $result2 = $this->_setLastChangeTime('groups:membership_invitations:surfer_'.$currentSurferId);
          return $result1 && $result2;
        }
      }
    }
    return FALSE;
  }

}