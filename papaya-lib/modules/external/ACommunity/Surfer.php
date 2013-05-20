<?php
/**
 * Advanced community surfer
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
require_once(dirname(__FILE__).'/Ui/Content/Object.php');

/**
 * Advanced community surfer
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunitySurfer extends ACommunityUiContentObject {

  /**
   * Comments data
   * @var ACommunitySurferData
   */
  protected $_data = NULL;

  /**
   * Get/set surfer data
   *
   * @param ACommunitySurferData $data
   * @return ACommunitySurferData
   */
  public function data(ACommunitySurferData $data = NULL) {
    if (isset($data)) {
      $this->_data = $data;
    } elseif (is_null($this->_data)) {
      include_once(dirname(__FILE__).'/Surfer/Data.php');
      $this->_data = new ACommunitySurferData();
      $this->_data->papaya($this->papaya());
      $this->_data->owner = $this;
    }
    return $this->_data;
  }

  /**
   * Perform commands to change surfer contact
   */
  protected function _performCommands() {
    $command = $this->parameters()->get('command', NULL);
    $ressource = $this->data()->ressource();
    if (!empty($ressource['id']) && $this->data()->ressourceIsActiveSurfer == FALSE) {
      $currentSurferId = $this->data()->currentSurferId();
      if (!empty($currentSurferId)) {
        switch ($command) {
          case 'request_contact':
            $this->data()->contactChanges()->addContactRequest(
              $currentSurferId, $ressource['id']
            );
            break;
          case 'remove_contact_request':
            $this->data()->contactChanges()->deleteContactRequest(
              $currentSurferId, $ressource['id']
            );
            break;
          case 'accept_contact_request':
            $this->data()->contactChanges()->acceptContactRequest(
              $currentSurferId, $ressource['id']
            );
            break;
          case 'decline_contact_request':
            $this->data()->contactChanges()->declineContactRequest(
              $currentSurferId, $ressource['id']
            );
            break;
          case 'remove_contact':
            $this->data()->contactChanges()->deleteContact(
              $currentSurferId, $ressource['id']
            );
            break;
        }
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
    $page = $parent->appendElement('surfer-page');
    if (!is_null($this->data()->ressource()) && $this->data()->ressource() != FALSE) {
      $this->_performCommands();
      $this->data()->initialize();
      $details = $page->appendElement('details');
      $baseDetails = $details->appendElement(
        'group', array('id' => 0, 'caption' => $this->data()->captions['base_details'])
      );
      foreach ($this->data()->surferBaseDetails as $name => $value) {
        $baseDetails->appendElement(
          'detail',
          array('name' => $name, 'caption' => $this->data()->captions['surfer_'.$name]),
          PapayaUtilStringXml::escape($value)
        );
      }
      foreach ($this->data()->surferDetails as $groupId => $group) {
        $detailsGroup = $details->appendElement(
          'group', array('id' => $groupId, 'caption' => $group['caption'])
        );
        foreach ($group['details'] as $detailName => $detail) {
          $detailsGroup->appendElement(
            'detail', array('name' => $detailName, 'caption' => $detail['caption']),
            PapayaUtilStringXml::escape($detail['value'])
          );
        }
      }
      if (!empty($this->data()->contact)) {
        $contactStatus = $this->data()->contact['status'];
        $contact = $page->appendElement(
          'contact',
          array(
            'status' => $contactStatus,
            'status-caption' => $this->data()->captions['contact_status_'.$contactStatus],
          )
        );
        foreach ($this->data()->contact['commands'] as $commandName => $commandLink) {
          $contact->appendElement(
            'command',
            array(
              'name' => $commandName,
              'caption' => $this->data()->captions['command_'.$commandName]
            ),
            PapayaUtilStringXml::escape($commandLink)
          );
        }
      }
    } else {
      $page->appendElement('message', array('type' => 'no-surfer'), $this->data()->messages['no_surfer']);
    }
  }

}
