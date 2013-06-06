<?php
/**
 * Advanced community messages data class to handle all sorts of related data
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
 * Advanced community messages data class to handle all sorts of related data
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityMessagesData extends ACommunityUiContentData {

  /**
   * Data to display paging
   * @var array
   */
  public $paging = array();

  /**
   * A list of messages page links used by message surfers list
   * The message page links contain a surfer handle to identify the message contact
   * @var array
   */
  protected $_messagesPageLinks = NULL;

  /**
   * Contains messages list data
   * @var array
   */
  protected $_messagesList = NULL;

  /**
   * Messages database records
   * @var object
   */
  protected $_messages = NULL;

  /**
   * Message database record
   * @var ACommunityContentMessage
   */
  protected $_message = NULL;

  /**
   * Message surfer database record
   * @var ACommunityContentMessageSurfer
   */
  protected $_messageSurfer = NULL;

  /**
   * Contains message conversations list data
   * @var array
   */
  protected $_messageConversationsList = NULL;

  /**
   * Message conversations database records
   * @var object
   */
  protected $_messageConversations = NULL;

  /**
   * Length of last message text to show in conversations list
   * @var integer
   */
  public $lastMessageMaxLength = NULL;

  /**
   * Page titles for messages and notifications
   * @var array
   */
  public $pageTitles = NULL;

  /**
   * Set data by plugin object
   *
   * @param array $data
   * @param array $captionNames
   * @param array $messageNames
   */
  public function setPluginData($data, $captionNames = array(), $messageNames = array()) {
    if (isset($data['messages_per_page'])) {
      $this->paging['messages_per_page'] = (int)$data['messages_per_page'];
    }
    if (isset($data['message_conversations_per_page'])) {
      $this->paging['message_conversations_per_page'] = (int)$data['message_conversations_per_page'];
    }
    $this->_surferAvatarSize = (int)$data['avatar_size'];
    $this->_surferAvatarResizeMode = $data['avatar_resize_mode'];
    if (isset($data['last_message_max_length'])) {
      $this->lastMessageMaxLength = (int)$data['last_message_max_length'];
    }
    if (isset($data['page_title_messages']) && isset($data['page_title_notifications'])) {
      $this->pageTitles['messages'] = $data['page_title_messages'];
      $this->pageTitles['notifications'] = $data['page_title_notifications'];
    }
    parent::setPluginData($data, $captionNames, $messageNames);
  }

  /**
  * Access to message database record data
  *
  * @param ACommunityContentMessage $message
  * @return ACommunityContentMessage
  */
  public function message(ACommunityContentMessage $message = NULL) {
    if (isset($message)) {
      $this->_message = $message;
    } elseif (is_null($this->_message)) {
      include_once(dirname(__FILE__).'/../Content/Message.php');
      $this->_message = new ACommunityContentMessage();
      $this->_message->papaya($this->papaya());
    }
    return $this->_message;
  }

  /**
  * Access to the messages database records data
  *
  * @param ACommunityContentMessages $messages
  * @return ACommunityContentMessages
  */
  public function messages(ACommunityContentMessages $messages = NULL) {
    if (isset($messages)) {
      $this->_messages = $messages;
    } elseif (is_null($this->_messages)) {
      include_once(dirname(__FILE__).'/../Content/Messages.php');
      $this->_messages = new ACommunityContentMessages();
      $this->_messages->papaya($this->papaya());
    }
    return $this->_messages;
  }

  /**
  * Access to the message surfer database record data
  *
  * @param ACommunityContentMessageSurfer $messageSurfer
  * @return ACommunityContentMessageSurfer
  */
  public function messageSurfer(ACommunityContentMessageSurfer $messageSurfer = NULL) {
    if (isset($messageSurfer)) {
      $this->_messageSurfer = $messageSurfer;
    } elseif (is_null($this->_messageSurfer)) {
      include_once(dirname(__FILE__).'/../Content/Message/Surfer.php');
      $this->_messageSurfer = new ACommunityContentMessageSurfer();
      $this->_messageSurfer->papaya($this->papaya());
    }
    return $this->_messageSurfer;
  }

  /**
  * Access to the message conversations database records data
  *
  * @param ACommunityContentMessageConversations $conversations
  * @return ACommunityContentMessageConversations
  */
  public function messageConversations(ACommunityContentMessageSurfers $conversations = NULL) {
    if (isset($conversations)) {
      $this->_messageConversations = $conversations;
    } elseif (is_null($this->_messageConversations)) {
      include_once(dirname(__FILE__).'/../Content/Message/Conversations.php');
      $this->_messageConversations = new ACommunityContentMessageConversations();
      $this->_messageConversations->papaya($this->papaya());
    }
    return $this->_messageConversations;
  }

  /**
   * Get/set messages list data
   *
   * @param array $list
   * @return array
   */
  public function messagesList($list = NULL) {
    if (isset($list)) {
      $this->_messagesList = $list;
    } elseif (is_null($this->_messagesList)) {
      $this->_messagesList = array();
      $this->_getMessagesList($this->_messagesList);
    }
    return $this->_messagesList;
  }

  /**
   * Get messages list by parameters and database records
   *
   * @param reference $listData
   */
  protected function _getMessagesList(&$listData) {
    $listData = array();
    $page = $this->owner->parameters()->get('messages_page', 0);
    $ressource = $this->owner->ressource();
    $showNotifications = (boolean)$this->owner->parameters()->get('notifications', FALSE);
    $this->messages()->load(
      array(
        'current_surfer_id' => $this->currentSurferId(),
        'selected_surfer_id' => $showNotifications ? 'system' : $ressource->id
      ),
      $this->paging['messages_per_page'],
      ($page > 0) ? ($page - 1) * $this->paging['messages_per_page'] : 0
    );
    $listData['abs_count'] = (int)$this->messages()->absCount();
    $listData['data'] = $this->_getListDataByMessages(
      $this->messages()->toArray(), 'surfer-page', $showNotifications
    );
    return $listData;
  }

  /**
   * Get/set message conversations list data
   *
   * @param array $list
   * @return array
   */
  public function messageConversationsList($list = NULL) {
    if (isset($list)) {
      $this->_messageConversationsList = $list;
    } elseif (is_null($this->_messageConversationsList)) {
      $this->_messageConversationsList = array();
      $this->_getMessageConversationsList($this->_messageConversationsList);
    }
    return $this->_messageConversationsList;
  }

  /**
   * Get message conversations list by parameters and database records
   *
   * @param reference $listData
   */
  protected function _getMessageConversationsList(&$listData) {
    $listData = array();
    $page = $this->owner->parameters()->get('message_conversations_page', 0);
    $this->messageConversations()->load(
      array(
        'current_surfer_id' => $this->currentSurferId()
      ),
      $this->paging['message_conversations_per_page'],
      ($page > 0) ? ($page - 1) * $this->paging['message_conversations_per_page'] : 0
    );
    $listData['abs_count'] = (int)$this->messageConversations()->absCount();
    $listData['data'] = $this->_getListDataByMessages(
      $this->messageConversations()->toArray(), 'messages-page'
    );
    return $listData;
  }

  /**
   * Get list data by loaded messages with two differen link modes
   *
   * @param array $messages
   * @param string $linkMode
   * @return array
   */
  protected function _getListDataByMessages($messages, $linkMode = 'surfer-page', $notifications = FALSE) {
    $data = array();
    foreach ($messages as $id => $message) {
      if ($notifications == FALSE) {
        if ($linkMode == 'surfer-page') {
          $contactSurferId = $message['sender'];
        } elseif ($linkMode == 'messages-page') {
          $contactSurferId = $message['sender'] == $this->currentSurferId() ?
            $message['recipient'] : $message['sender'];
          if (!isset($this->_messagesPageLinks[$contactSurferId])) {
            $message['messages_page_link'] = $this->owner->acommunityConnector()->getMessagesPageLink(
              $contactSurferId
            );
          } else {
            $message['messages_page_link'] = $this->_messagesPageLinks[$contactSurferId];
          }
        }
        $message['surfer'] = $this->getSurfer($contactSurferId);
      } else {
        $message['surfer'] = NULL;
      }
      $data[$id] = $message;
    }
    return $data;
  }
}