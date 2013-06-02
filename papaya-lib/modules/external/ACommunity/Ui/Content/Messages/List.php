<?php
/**
 * Advanced community ui content messages list
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
 * Advanced community ui content messages list
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityUiContentMessagesList extends PapayaUiControl {

  /**
  * Object buffer for comments
  *
  * @var ACommunityUiContentMessages
  */
  protected $_messages = NULL;

  /**
  * Messages data
  * @var ACommunityMessagesData
  */
  protected $_data = NULL;

  /**
  * Declared public properties, see property annotaiton of the class for documentation.
  *
  * @var array
  */
  protected $_declaredProperties = array(
    'messages' => array('messages', 'messages')
  );

  /**
   * Get/set comments data
   *
   * @param ACommunityMessagesData $data
   * @return ACommunityMessagesData
   */
  public function data(ACommunityMessagesData $data = NULL) {
    if (isset($data)) {
      $this->_data = $data;
    }
    return $this->_data;
  }

  /**
  * Fill comments list with comments data
  */
  private function fill() {
    $messagesList = $this->data()->messagesList();
    if (count($messagesList['data']) > 0) {
      $this->messages()->absCount = $messagesList['abs_count'];
      foreach ($messagesList['data'] as $id => $messageData) {
        include_once(dirname(__FILE__).'/../Message.php');
        $message = new ACommunityUiContentMessage(
          $messageData['id'],
          $messageData['text'],
          $messageData['time']
        );
        if (!empty($messageData['surfer'])) {
          $message->surferName = $messageData['surfer']['name'];
          $message->surferPageLink = $messageData['surfer']['page_link'];
          $message->surferAvatar = $messageData['surfer']['avatar'];
        }
        $this->messages[] = $message;
      }
    }
  }

  /**
  * Append discussion output to parent element.
  *
  * @param PapayaXmlElement $parent
  */
  public function appendTo(PapayaXmlElement $parent) {
    $this->fill();
    $messagesList = $this->data()->messagesList();
    if (empty($messagesList['abs_count'])) {
      $parent->appendElement(
        'message', array('type' => 'no-messages'), $this->data()->messages['no_messages']
      );
    } else {
      $this->messages()->appendTo($parent);
    }
  }

  /**
  * The list of messages
  *
  * @param ACommunityUiContentMessages $messages
  */
  public function messages(ACommunityUiContentMessages $messages = NULL) {
    if (isset($messages)) {
      $this->_messages = $messages;
    } elseif (is_null($this->_messages)) {
      include_once(dirname(__FILE__).'/../Messages.php');
      $this->_messages = new ACommunityUiContentMessages($this);
      $this->_messages->papaya($this->papaya());
      $this->_messages->pagingParameterGroup = $this->data()->owner->parameterGroup();
      $this->_messages->pagingItemsPerPage = (int)$this->data()->paging['messages_per_page'];
      $this->_messages->reference($this->data()->reference());
    }
    return $this->_messages;
  }
}