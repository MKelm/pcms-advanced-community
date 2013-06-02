<?php
/**
 * Advanced community groups
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
 * Base ui content object
 */
require_once(dirname(__FILE__).'/Ui/Content.php');

/**
 * Advanced community groups
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityGroups extends ACommunityUiContent {

  /**
   * Ui content group dialog
   * @var ACommunityUiContentGroupDialog
   */
  protected $_uiContentGroupDialog = NULL;

  /**
   * Ui groups list control
   * @var ACommunityUiContentGroupsList
   */
  protected $_uiGroupsList = NULL;

  /**
   * Get/set groups data
   *
   * @param ACommunityGroupsData $data
   * @return ACommunityGroupsData
   */
  public function data(ACommunityGroupsData $data = NULL) {
    if (isset($data)) {
      $this->_data = $data;
    } elseif (is_null($this->_data)) {
      include_once(dirname(__FILE__).'/Groups/Data.php');
      $this->_data = new ACommunityGroupsData();
      $this->_data->papaya($this->papaya());
      $this->_data->owner = $this;
    }
    return $this->_data;
  }

  /**
   * Perform commands by parameter
   */
  public function performCommands() {
    $command = $this->parameters()->get('command', '');
    if (!empty($command)) {
      $groupHandle = $this->parameters()->get('group_handle', NULL);
      $groupId = $this->acommunityConnector()->getGroupIdByHandle($groupHandle);

      if (!empty($groupId)) {
        switch ($command) {
          case 'delete_group':
            $moderatorAction = !$this->data()->showOwnGroups() && $this->data()->surferIsModerator();
            $ownerAction = $this->data()->showOwnGroups() &&
              $this->data()->surferHasStatus($groupId, 'is_owner', 1);
            if ($moderatorAction || $ownerAction) {
              $group = clone $this->data()->group();
              $group->load($groupId);
              if ($group->delete()) {
                $result1 = TRUE;
                if ($ownerAction) {
                  $result1 = $this->data()->setLastChangeTime(
                    'groups:'.$ressource['type'].'_'.$ressource['id']
                  );
                }
                $result2 = $this->data()->setLastChangeTime('groups');
                return $result1 && $result2;
              }
            }
            break;
          case 'accept_invitation':
            $invitedSurferAction = $this->data()->showOwnGroups() &&
              $this->data()->surferHasStatus($groupId, 'is_pending', 2);
            if ($invitedSurferAction) {
              return $this->data()->groupSurferChanges()->acceptInvitation(
                $groupId, $this->data()->currentSurferId()
              );
            }
            break;
          case 'decline_invitation':
            $invitedSurferAction = $this->data()->showOwnGroups() &&
              $this->data()->surferHasStatus($groupId, 'is_pending', 2);
            if ($invitedSurferAction) {
              return $this->data()->groupSurferChanges()->declineInvitation(
                $groupId, $this->data()->currentSurferId()
              );
            }
            break;
          case 'remove_request':
            $requestedSurferAction = $this->data()->showOwnGroups() &&
              $this->data()->surferHasStatus($groupId, 'is_pending', 1);
            if ($requestedSurferAction) {
              return $this->data()->groupSurferChanges()->removeRequest(
                $groupId, $this->data()->currentSurferId()
              );
            }
            break;
          case 'remove_membership':
            $requestedSurferAction = $this->data()->showOwnGroups() &&
              $this->data()->surferHasStatus($groupId, 'is_member', 1);
            if ($requestedSurferAction) {
              return $this->data()->groupSurferChanges()->removeMember(
                $groupId, $this->data()->currentSurferId(), 'own-membership'
              );
            }
            break;
        }
      }
      return FALSE;
    }
    return NULL;
  }

  /**
  * Create dom node structure of the given object and append it to the given xml
  * element node.
  *
  * @param PapayaXmlElement $parent
  */
  public function appendTo(PapayaXmlElement $parent) {
    $groups = $parent->appendElement(
      'acommunity-groups', array('groups-per-row' => $this->data()->groupsPerRow)
    );
    $removeDialog = 0;
    if ($this->data()->surferIsModerator() || $this->data()->showOwnGroups()) {
      $result = $this->performCommands();
      $command = $this->parameters()->get('command', '');
      if ($result === FALSE && $command != 'add_group' && $command != 'edit_group') {
        $groups->appendElement(
          'message',
          array('type' => 'error'),
          $this->data()->messages['failed_to_execute_command']
        );
      }

      if ($this->data()->showOwnGroups()) {
        if ($command == 'add_group' || $command == 'edit_group') {

          $dom = new PapayaXmlDocument();
          $dom->appendElement('dialog');
          $this->uiContentGroupDialog()->appendTo($dom->documentElement);
          $removeDialog = $this->parameters()->get('remove_dialog', 0);
          if (empty($removeDialog)) {
            $xml = '';
            foreach ($dom->documentElement->childNodes as $node) {
              $xml .= $node->ownerDocument->saveXml($node);
            }
            $groups->appendXml($xml);
            $errorMessage = $this->uiContentGroupDialog()->errorMessage();
            if (!empty($errorMessage)) {
              $groups->appendElement(
                'dialog-message', array('type' => 'error'), $errorMessage
              );
            }
          }
        }
      }
    }
    $this->uiGroupsList()->appendTo($groups);

    if ($this->data()->showOwnGroups()) {
      $mode = $this->parameters()->get('mode', NULL);
      $modes = $groups->appendElement('modes');
      $reference = clone $this->data()->reference();
      $reference->setParameters(
        array('mode' => 'groups'), $this->parameterGroup()
      );
      $modes->appendElement(
        'groups',
        array(
          'caption' => $this->data()->captions['mode_groups'],
          'active' => $mode == 'groups' || $mode === NULL ? 1 : 0
        ),
        $reference->getRelative()
      );
      $reference = clone $this->data()->reference();
      $reference->setParameters(
        array('mode' => 'invitations'), $this->parameterGroup()
      );
      $modes->appendElement(
        'invitations',
        array(
          'caption' => $this->data()->captions['mode_invitations'],
          'active' => $this->parameters()->get('mode') == 'invitations' ? 1 : 0
        ),
        $reference->getRelative()
      );
      $reference = clone $this->data()->reference();
      $reference->setParameters(
        array('mode' => 'requests'), $this->parameterGroup()
      );
      $modes->appendElement(
        'requests',
        array(
          'caption' => $this->data()->captions['mode_requests'],
          'active' => $this->parameters()->get('mode') == 'requests' ? 1 : 0
        ),
        $reference->getRelative()
      );
      // commands
      if ($mode === NULL || $mode == 'groups') {
        $command = $this->parameters()->get('command');
        $commands = $groups->appendElement('commands');
        $reference = clone $this->data()->reference();
        $reference->setParameters(
          array('command' => 'add_group'), $this->parameterGroup()
        );
        $commands->appendElement(
          'add',
          array(
            'caption' => $this->data()->captions['command_add'],
            'active' => ($command == 'add_group' &&
            $removeDialog == 0) ? 1 : 0
          ),
          $reference->getRelative()
        );
        if ($command == 'edit_group' && $removeDialog == 0) {
          $commands->appendElement(
            'edit',
            array(
              'caption' => $this->data()->captions['command_edit_group'],
              'active' => 1
            ),
            '#'
          );
        }
      }
    }
  }

  /**
  * Access to the ui content group dialog control
  *
  * @param ACommunityUiContentGroupDialog $uiContentGroupDialog
  * @return ACommunityUiContentGroupDialog
  */
  public function uiContentGroupDialog(
           ACommunityUiContentGroupDialog $uiContentGroupDialog = NULL
         ) {
    if (isset($uiContentGroupDialog)) {
      $this->_uiContentGroupDialog = $uiContentGroupDialog;
    } elseif (is_null($this->_uiContentGroupDialog)) {
      include_once(dirname(__FILE__).'/Ui/Content/Group/Dialog.php');
      $this->_uiContentGroupDialog = new ACommunityUiContentGroupDialog(
        $this->data()->group()
      );
      $this->_uiContentGroupDialog->data($this->data());
      $this->_uiContentGroupDialog->parameters($this->parameters());
      $this->_uiContentGroupDialog->parameterGroup($this->parameterGroup());
    }
    return $this->_uiContentGroupDialog;
  }

  /**
  * Access to the ui groups list
  *
  * @param ACommunityUiContentGroupsList $uiGroupsList
  * @return ACommunityUiContentGroupsList
  */
  public function uiGroupsList(
           ACommunityUiContentGroupsList $uiGroupsList = NULL
         ) {
    if (isset($uiGroupsList)) {
      $this->_uiGroupsList = $uiGroupsList;
    } elseif (is_null($this->_uiGroupsList)) {
      include_once(dirname(__FILE__).'/Ui/Content/Groups/List.php');
      $this->_uiGroupsList = new ACommunityUiContentGroupsList();
      $this->_uiGroupsList->papaya($this->papaya());
      $this->_uiGroupsList->data($this->data());
    }
    return $this->_uiGroupsList;
  }
}