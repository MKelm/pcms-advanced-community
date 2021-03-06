<?php
/**
 * Advanced community surfers
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
 * Advanced community surfers
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunitySurfers extends ACommunityUiContent {

  /**
  * Paging object
  *
  * @var PapayaUiPagingCount
  */
  protected $_paging = NULL;

  /**
   * Get/set surfer status data
   *
   * @param ACommunitySurferStatusData $data
   * @return ACommunitySurferStatusData
   */
  public function data(ACommunitySurferStatusData $data = NULL) {
    if (isset($data)) {
      $this->_data = $data;
    } elseif (is_null($this->_data)) {
      include_once(dirname(__FILE__).'/Surfers/Data.php');
      $this->_data = new ACommunitySurfersData();
      $this->_data->papaya($this->papaya());
      $this->_data->owner = $this;
    }
    return $this->_data;
  }

  /**
   * Perform commands to change surfer contact
   */
  protected function _performCommands() {
    $ressource = $this->ressource();
    if ($this->data()->displayMode == 'contacts_and_requests') {
      $command = $this->parameters()->get('command', NULL);
      if (!empty($command)) {
        $surferId = $this->communityConnector()->getIdByHandle(
          $this->parameters()->get('surfer_handle', NULL)
        );
        if (isset($ressource->id)) {
          switch ($command) {
            case 'remove_contact_request':
              return $this->data()->contactChanges()->deleteContactRequest($ressource->id, $surferId);
              break;
            case 'accept_contact_request':
              return $this->data()->contactChanges()->acceptContactRequest($ressource->id, $surferId);
              break;
            case 'decline_contact_request':
              return $this->data()->contactChanges()->declineContactRequest($ressource->id, $surferId);
              break;
            case 'remove_contact':
              return $this->data()->contactChanges()->deleteContact($ressource->id, $surferId);
              break;
          }
        }
        return FALSE;
      }

    } elseif ($this->data()->displayMode == 'surfers') {
      $command = $this->parameters()->get('command', NULL);
      if (!empty($command)) {
        if ($ressource->type == 'group') {
          $surferId = $this->communityConnector()->getIdByHandle(
            $this->parameters()->get('surfer_handle', NULL)
          );
          if ($this->acommunityConnector()->groupSurferRelations()
                ->status($ressource->id, $surferId, 'is_owner', 1)) {
            $changes = $this->acommunityConnector()->groupSurferRelations()->changes();
            switch ($command) {
              case 'remove_member':
                return $changes->removeMember(
                  $ressource->id, $surferId, $this->data()->currentSurferId()
                );
                break;
              case 'invite_surfer':
                return $changes->inviteSurfer(
                  $ressource->id, $surferId, $this->data()->currentSurferId()
                );
                break;
              case 'remove_invitation':
                return $changes->removeInvitation(
                  $ressource->id, $surferId, $this->data()->currentSurferId()
                );
                break;
              case 'accept_request':
                return $changes->acceptRequest(
                  $ressource->id, $surferId, $this->data()->currentSurferId()
                );
                break;
              case 'decline_request':
                return $changes->declineRequest(
                  $ressource->id, $surferId, $this->data()->currentSurferId()
                );
                break;
            }
          }
        }
        return FALSE;
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
    $listElement = $parent->appendElement('acommunity-surfers');
    $result = $this->_performCommands();
    if ($result === FALSE) {
      $listElement->appendElement(
        'message', array('type' => 'error'), $this->data()->messages['failed_to_execute_command']
      );
    }

    if ($this->data()->displayMode == 'contacts_and_requests') {
      if (isset($this->ressource()->id)) {
        $this->data()->initialize();
        foreach ($this->data()->surfers as $groupName => $surfers) {
          $groupElement = $listElement->appendElement(
            'group', array('name' => $groupName, 'caption' => $this->data()->captions[$groupName])
          );
          if (empty($surfers)) {
            $groupElement->appendElement(
              'message', array('type' => 'info'), $this->data()->messages['empty_list']
            );
          } else {
            foreach ($surfers as $surfer) {
              $this->_appendSurferTo($groupElement, $surfer);
            }
            if ($this->data()->showPaging) {
              $this->paging(
                NULL, TRUE, $groupName.'_', $this->data()->pagingItemsAbsCount[$groupName]
              )->appendTo($groupElement);
            }
          }
        }
      }
    } else {
      $this->data()->initialize();
      if ($this->data()->displayMode == 'surfers') {
        if (!empty($this->data()->showFilterNavigation)) {
          $this->_appendFilterNavigationTo($listElement);
        }
        if (!empty($this->data()->showSearchDialog)) {
          $this->_appendSimpleSearchDialogTo($listElement);
        }
      }
      if (empty($this->data()->surfers)) {
        $listElement->appendElement(
          'message', array('type' => 'info'), $this->data()->messages['empty_list']
        );
      } else {
        foreach ($this->data()->surfers as $surfer) {
          $this->_appendSurferTo($listElement, $surfer);
        }
        if ($this->data()->showPaging) {
          $this->paging(
            NULL, TRUE, $this->data()->displayMode.'_', $this->data()->pagingItemsAbsCount
          )->appendTo($listElement);
        }
      }
    }
  }

  /**
   * Append filter navigation for sufers view
   *
   * @param PapayaXmlElement $parent
   */
  protected function _appendFilterNavigationTo($parent) {
    $filterNavigation = $parent->appendElement('filter-navigation');
    $reference = clone $this->data()->reference();
    $reference->setParameters(array('surfers_character' => ''), $this->parameterGroup());
    $filterNavigation->appendElement(
      'character',
      array('href' => $reference->getRelative()),
      $this->data()->captions['all']
    );
    $abc = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P',
      'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
    foreach ($abc as $character) {
      $reference = clone $this->data()->reference();
      $reference->setParameters(array('surfers_character' => $character), $this->parameterGroup());
      $filterNavigation->appendElement(
        'character', array('href' => $reference->getRelative()), $character
      );
    }
  }

  /**
   * Append search dialog for sufers view
   *
   * @param PapayaXmlElement $parent
   */
  protected function _appendSimpleSearchDialogTo($parent) {
    $search = $parent->appendElement('search');
    $dialog = new PapayaUiDialog();
    $options = new PapayaUiDialogOptions();
    $options->useConfirmation = FALSE;
    $options->useToken = FALSE;
    $dialog->options($options);

    $dialog->papaya($this->papaya());
    $dialog->parameterGroup($this->parameterGroup());
    $dialog->parameters($this->parameters());
    $dialog->action($this->data()->reference()->getRelative());
    $dialog->caption = NULL;

    $dialog->fields[] = $field = new PapayaUiDialogFieldInput(
      $this->data()->captions['dialog_search'],
      'surfers_search',
      200,
      NULL,
      new PapayaFilterText(PapayaFilterText::ALLOW_SPACES|PapayaFilterText::ALLOW_DIGITS)
    );
    $field->setMandatory(TRUE);
    $field->setId('dialogSurfersSearchField');
    $dialog->buttons[] = new PapayaUiDialogButtonSubmit($this->data()->captions['dialog_send']);

    $dialog->appendTo($search);
  }

  /**
   * Append surfer data node structure to parent element
   *
   * @param PapayaXmlElement $parent
   * @param array $surfer
   */
  protected function _appendSurferTo(papayaXmlElement $parent, $surfer) {
    $surferElement = $parent->appendElement(
      'surfer',
      array(
        'name' => $surfer['name'],
        'avatar' => PapayaUtilStringXml::escapeAttribute($surfer['avatar']),
        'page-link' => PapayaUtilStringXml::escapeAttribute($surfer['page_link'])
      )
    );
    if (!empty($surfer['last_action'])) {
      $surferElement->appendElement(
        'last-time',
        array('caption' => $this->data()->captions['last_action']),
        $surfer['last_action']
      );
    } elseif (!empty($surfer['registration'])) {
      $surferElement->appendElement(
        'last-time',
        array('caption' => $this->data()->captions['registration']),
        $surfer['registration']
      );
    }
    if (!empty($surfer['commands'])) {
      foreach ($surfer['commands'] as $commandName => $commandLink) {
        $surferElement->appendElement(
          'command',
          array(
            'name' => $commandName,
            'caption' => $this->data()->captions['command_'.$commandName]
          ),
          PapayaUtilStringXml::escape($commandLink)
        );
      }
    }
  }

  /**
   * Paging object
   *
   * @param PapayaUiPagingCount $paging
   * @param boolean $reset
   * @param string $parameterNamePrefix
   * @param integer $absCount
   */
  public function paging(
           PapayaUiPagingCount $paging, $reset = FALSE, $parameterNamePrefix = '', $absCount = NULL
         ) {
    if (isset($paging)) {
      $this->_paging = $paging;
    } elseif (is_null($this->_paging) || $reset == TRUE) {
      $parameter = sprintf(
        '%s[%s]', $this->parameterGroup(), $parameterNamePrefix.'list_page'
      );
      $this->_paging = new PapayaUiPagingCount(
        $parameter, $this->papaya()->request->getParameter($parameter), $absCount
      );
      $this->_paging->papaya($this->papaya());
      $this->_paging->itemsPerPage = $this->data()->pagingItemsPerPage;
      $this->_paging->reference($this->data()->reference());
    }
    return $this->_paging;
  }

}