<?php
/**
 * Advanced community messages
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
 * Advanced community messages
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityMessages extends ACommunityUiContent {

  /**
   * Ui content message dialog
   * @var ACommunityUiContentMessageDialog
   */
  protected $_uiContentMessageDialog = NULL;

  /**
   * Ui content messages list control
   * @var ACommunityUiContentMessagesList
   */
  protected $_uiContentMessagesList = NULL;

  /**
   * Ui content message conversations list control
   * @var ACommunityUiContentMessageConversationsList
   */
  protected $_uiContentMessageConversationsList = NULL;

  /**
   * Messages or message-conversations
   * @var string
   */
  public $mode = 'messages';

  /**
   * Get/set messages data
   *
   * @param ACommunityMessagesData $data
   * @return ACommunityMessagesData
   */
  public function data(ACommunityMessagesData $data = NULL) {
    if (isset($data)) {
      $this->_data = $data;
    } elseif (is_null($this->_data)) {
      include_once(dirname(__FILE__).'/Messages/Data.php');
      $this->_data = new ACommunityMessagesData();
      $this->_data->papaya($this->papaya());
      $this->_data->owner = $this;
    }
    return $this->_data;
  }

  /**
  * Create dom node structure of the given object and append it to the given xml
  * element node.
  *
  * @param PapayaXmlElement $parent
  */
  public function appendTo(PapayaXmlElement $parent) {
    $currentSurferId = $this->data()->currentSurferId();
    if ($this->mode == 'messages') {
      $messages = $parent->appendElement('acommunity-messages');
      $showNotifications = (boolean)$this->parameters()->get('notifications', FALSE);
      if (isset($this->data()->pageTitles)) {
        $messages->appendElement(
          'title',
          array(),
          $showNotifications ?
            $this->data()->pageTitles['notifications'] : $this->data()->pageTitles['messages']
        );
      }
      if (!empty($currentSurferId)) {
        $ressource = $this->ressource();
        if ($showNotifications == TRUE) {
          $this->uiContentMessagesList()->appendTo($messages);
        } elseif (isset($ressource->id) && $ressource->validSurfer !== 'is_selected') {
          $messagesPage = $this->parameters()->get('messages_page', 0);
          if (!($messagesPage > 1)) {
            $this->uiContentMessageDialog()->appendTo($messages);
            $errorMessage = $this->uiContentMessageDialog()->errorMessage();
            if (!empty($errorMessage)) {
              $messages->appendElement(
                'dialog-message', array('type' => 'error'), $errorMessage
              );
            }
          }
          $this->uiContentMessagesList()->appendTo($messages);
        } else {
          $messages->appendElement(
            'message',
            array('type' => 'no-message-conversation'),
            $this->data()->messages['no_message_conversation']
          );
        }
      } else {
        $messages->appendElement(
          'message', array('type' => 'no-login'), $this->data()->messages['no_login']
        );
      }
    } else {
      $messageConversations = $parent->appendElement('acommunity-message-conversations');
      if (!empty($currentSurferId)) {
        $this->uiContentMessageConversationsList()->appendTo($messageConversations);
      } else {
        $messageConversations->appendElement(
          'message', array('type' => 'no-login'), $this->data()->messages['no_login']
        );
      }
    }

  }

  /**
  * Access to the ui content message dialog control
  *
  * @param ACommunityUiContentMessageDialog $uiContentMessageDialog
  * @return ACommunityUiContentMessageDialog
  */
  public function uiContentMessageDialog(
           ACommunityUiContentMessageDialog $uiContentMessageDialog = NULL
         ) {
    if (isset($uiContentMessageDialog)) {
      $this->_uiContentMessageDialog = $uiContentMessageDialog;
    } elseif (is_null($this->_uiContentMessageDialog)) {
      include_once(dirname(__FILE__).'/Ui/Content/Message/Dialog.php');
      $this->_uiContentMessageDialog = new ACommunityUiContentMessageDialog(
        $this->data()->message()
      );
      $this->_uiContentMessageDialog->data($this->data());
      $this->_uiContentMessageDialog->parameters($this->parameters());
      $this->_uiContentMessageDialog->parameterGroup($this->parameterGroup());
    }
    return $this->_uiContentMessageDialog;
  }

  /**
  * Access to the ui content messages list
  *
  * @param ACommunityUiContentMessagesList $uiContentMessagesList
  * @return ACommunityUiContentMessagesList
  */
  public function uiContentMessagesList(
           ACommunityUiContentMessagesList $uiContentMessagesList = NULL
         ) {
    if (isset($uiContentMessagesList)) {
      $this->_uiContentMessagesList = $uiContentMessagesList;
    } elseif (is_null($this->_uiContentMessagesList)) {
      include_once(dirname(__FILE__).'/Ui/Content/Messages/List.php');
      $this->_uiContentMessagesList = new ACommunityUiContentMessagesList();
      $this->_uiContentMessagesList->papaya($this->papaya());
      $this->_uiContentMessagesList->data($this->data());
    }
    return $this->_uiContentMessagesList;
  }

  /**
  * Access to the ui content message conversations list
  *
  * @param ACommunityUiContentMessageConversationsList $uiContentMessageConversationsList
  * @return ACommunityUiContentMessageConversationsList
  */
  public function uiContentMessageConversationsList(
           ACommunityUiContentMessageConversationsList $uiContentMessageConversationsList = NULL
         ) {
    if (isset($uiContentMessageConversationsList)) {
      $this->_uiContentMessageConversationsList = $uiContentMessageConversationsList;
    } elseif (is_null($this->_uiContentMessagesList)) {
      include_once(dirname(__FILE__).'/Ui/Content/Message/Conversations/List.php');
      $this->_uiContentMessageConversationsList = new ACommunityUiContentMessageConversationsList();
      $this->_uiContentMessageConversationsList->papaya($this->papaya());
      $this->_uiContentMessageConversationsList->data($this->data());
    }
    return $this->_uiContentMessageConversationsList;
  }

}