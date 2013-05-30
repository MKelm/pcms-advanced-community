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
    $groupHandle = $this->parameters()->get('group_handle', NULL);
    $lastChange = 0;
    $groupId = $this->acommunityConnector()->getGroupIdByHandle($groupHandle);

    if ($command == 'delete_group' && !empty($groupId) && (
         (!$this->data()->showOwnGroups() && $this->data()->surferIsModerator()) ||
         ($this->data()->showOwnGroups() && $this->data()->surferHasStatus($groupId, 'is_owner', 1))
        )) {
      $group = clone $this->data()->group();
      $group->load($groupId);
      if ($group->delete()) {
        $lastChange = time();
      }

    } elseif (($command == 'accept_invitation' || $command == 'decline_invitation') &&
              !empty($groupId) && $this->data()->showOwnGroups() &&
              $this->data()->surferHasStatus($groupId, 'is_pending', 2)) {
      switch ($command) {
        case 'accept_invitation':
          $groupSurferRelation = $this->data()->groupSurferRelation();
          $groupSurferRelation->load(
            array('id' => $groupId, 'surfer_id' => $this->data()->currentSurferId())
          );
          if ($groupSurferRelation['surfer_status_pending'] == 2) {
            $groupSurferRelation = clone $this->data()->groupSurferRelation();
            $groupSurferRelation->assign(
              array(
                'id' => $groupId,
                'surfer_id' => $this->data()->currentSurferId(),
                'surfer_status_pending' => 0
              )
            );
            if ($groupSurferRelation->save()) {
              $lastChange = time();
            }
          }
          break;
        case 'decline_invitation':
          $groupSurferRelation = $this->data()->groupSurferRelation();
          $groupSurferRelation->load(
            array('id' => $groupId, 'surfer_id' => $this->data()->currentSurferId())
          );
          if ($groupSurferRelation['surfer_status_pending'] == 2) {
            if ($groupSurferRelation->delete()) {
              $lastChange = time();
            }
          }
          break;
      }
    }

    if ($lastChange > 0) {
      if ($this->data()->showOwnGroups()) {
        $ressource = $this->data()->ressource();
        if ($command == 'accept_invitation' || $command == 'decline_invitation') {
          $lastChange = clone $this->data()->lastChange();
          $lastChange->assign(
            array(
              'ressource' => 'groups:membership_invitations:'.$ressource['type'].'_'.$ressource['id'],
              'time' => $lastChange
            )
          );
          $lastChange->save();
          $lastChange = clone $this->data()->lastChange();
          $lastChange->assign(
            array(
              'ressource' => 'group:membership_invitations:group_'.$groupId,
              'time' => $lastChange
            )
          );
          $lastChange->save();
        }
        if ($command == 'accept_invitation') {
          $lastChange = clone $this->data()->lastChange();
          $lastChange->assign(
            array(
              'ressource' => 'group:memberships:group_'.$groupId,
              'time' => $lastChange
            )
          );
          $lastChange->save();
        }
        $lastChange = clone $this->data()->lastChange();
        $lastChange->assign(
          array(
            'ressource' => 'groups:'.$ressource['type'].'_'.$ressource['id'],
            'time' => $lastChange
          )
        );
        $lastChange->save();
      }
      if ($copmmand == 'delete_group') {
        $this->data()->lastChange()->assign(
          array('ressource' => 'groups', 'time' => $lastChange)
        );
        $this->data()->lastChange()->save();
      }
      $this->parameters()->set('command', '');
      $this->parameters()->set('group_id', 0);
    }
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
      // commands
      if ($mode === NULL || $mode == 'groups') {
        $commands = $groups->appendElement('commands');
        $reference = clone $this->data()->reference();
        $reference->setParameters(
          array('command' => 'add_group'), $this->parameterGroup()
        );
        $commands->appendElement(
          'add',
          array(
            'caption' => $this->data()->captions['command_add'],
            'active' => $this->parameters()->get('command') == 'add_group' ? 1 : 0
          ),
          $reference->getRelative()
        );
      }
    }

    if ($this->data()->surferIsModerator() || $this->data()->showOwnGroups()) {
      $this->performCommands();

      if ($this->data()->showOwnGroups()) {
        $command = $this->parameters()->get('command', '');
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