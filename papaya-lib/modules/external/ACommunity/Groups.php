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
    $groupId = $this->parameters()->get('group_id', 0);
    if (!empty($command) && $groupId > 0) {
      $lastChange = 0;
      if ($command == 'delete_group') {
        $group = clone $this->data()->group();
        $group->load($groupId);
        if ($group->delete()) {
          $lastChange = time();
        }
      }
      if ($lastChange > 0) {
        $ressource = $this->data()->ressource();
        $lastChange = clone $this->data()->lastChange();
        $lastChange->assign(
          array(
            'ressource' => 'groups:'.$ressource['type'].'_'.$ressource['id'],
            'time' => $lastChange
          )
        );
        $lastChange->save();
        $this->data()->lastChange()->assign(
          array('ressource' => 'groups', 'time' => $lastChange)
        );
        $this->data()->lastChange()->save();
        $this->parameters()->set('command', '');
        $this->parameters()->set('group_id', 0);
      }
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
    if ($this->data()->surferIsGroupsOwner()) {
      $commands = $groups->appendElement('commands');
      $reference = clone $this->data()->reference();
      $reference->setParameters(
        array('command' => 'add_group'), $this->parameterGroup()
      );
      $commands->appendElement(
        'add', array('caption' => $this->data()->captions['command_add']), $reference->getRelative()
      );
    }

    if ($this->data()->surferIsModerator() || $this->data()->surferIsGroupsOwner()) {
      $this->performCommands();

      $command = $this->parameters()->get('command', '');
      if ($command == 'add_group' && $this->data()->surferIsGroupsOwner()) {

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