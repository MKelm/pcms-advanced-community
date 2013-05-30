<?php
/**
 * Advanced community group
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
 * Advanced community group
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityGroup extends ACommunityUiContent {

  /**
   * Get/set group data
   *
   * @param ACommunityGroupData $data
   * @return ACommunityGroupData
   */
  public function data(ACommunityGroupData $data = NULL) {
    if (isset($data)) {
      $this->_data = $data;
    } elseif (is_null($this->_data)) {
      include_once(dirname(__FILE__).'/Group/Data.php');
      $this->_data = new ACommunityGroupData();
      $this->_data->papaya($this->papaya());
      $this->_data->owner = $this;
    }
    return $this->_data;
  }

  /**
   * Perform surfer group commands
   */
  protected function _performCommands() {
    $command = $this->parameters()->get('command', NULL);
    $ressource = $this->data()->ressource();
    if (!empty($ressource)) {
      switch ($command) {
        case 'request_membership':
          return $this->data()->groupSurferChanges()->requestMembership(
            $ressource['id'], $this->data()->currentSurferId()
          );
          break;
        case 'remove_membership_request':
          return $this->data()->groupSurferChanges()->removeRequest(
            $ressource['id'], $this->data()->currentSurferId()
          );
          break;
        case 'accept_membership_invitation':
          return $this->data()->groupSurferChanges()->acceptInvitation(
            $ressource['id'], $this->data()->currentSurferId()
          );
          break;
        case 'decline_membership_invitation':
          return $this->data()->groupSurferChanges()->declineInvitation(
            $ressource['id'], $this->data()->currentSurferId()
          );
          break;
      }
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
    if (!is_null($this->data()->ressource()) && $this->data()->ressource() != FALSE) {
      $result = $this->_performCommands();
      if ($result === FALSE) {
        $parent->appendElement(
          'message', array('type' => 'error'), $this->data()->messages['failed_to_execute_command']
        );
      }

      if (FALSE !== $this->data()->initialize()) {
        $parent->appendElement('title', array(), $this->data()->title);
        $parent->appendElement(
          'time', array('caption' => $this->data()->captions['time']), $this->data()->time
        );
        $parent->appendXml($this->data()->text);
        $parent->appendElement('image', array(), PapayaUtilStringXml::escape($this->data()->image));

        if (!empty($this->data()->commands)) {
          $commands = $parent->appendElement('commands');
          foreach ($this->data()->commands as $name => $command) {
            $commands->appendElement(
              str_replace('_', '-', $name),
              array(
                'href' => PapayaUtilStringXml::escapeAttribute($command['href']),
                'caption' => PapayaUtilStringXml::escapeAttribute($command['caption'])
              )
            );
          }
        }
      } else {
        $parent->appendElement('message', array('type' => 'error'), $this->data()->messages['private_group']);
      }
    } else {
      $parent->appendElement('message', array('type' => 'error'), $this->data()->messages['no_group']);
    }
  }

}