<?php
/**
 * Advanced community ui content message conversations list
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
 * Advanced community ui content message conversations list
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityUiContentMessageConversationsList extends PapayaUiControl {

  /**
  * Messages data
  * @var ACommunityMessagesData
  */
  protected $_data = NULL;

  /**
  * Paging object
  *
  * @var PapayaUiPagingCount
  */
  protected $_paging = NULL;

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
   *
   * Paging object
   *
   * @param PapayaUiPagingCount $paging
   */
  public function paging(PapayaUiPagingCount $paging) {
    if (isset($paging)) {
      $this->_paging = $paging;
    } elseif (is_null($this->_paging)) {
      $parameter = sprintf(
        '%s[%s]', $this->data()->owner->parameterGroup(), 'message_conversations_page'
      );
      $listData = $this->data()->messageConversationsList();
      $this->_paging = new PapayaUiPagingCount(
        $parameter,
        $this->papaya()->request->getParameter($parameter),
        $listData['abs_count']
      );
      $this->_paging->papaya($this->papaya());
      $this->_paging->itemsPerPage = $this->data()->paging['message_conversations_per_page'];
      $this->_paging->reference($this->data()->reference());
    }
    return $this->_paging;
  }

  /**
  * Append discussion output to parent element.
  *
  * @param PapayaXmlElement $parent
  */
  public function appendTo(PapayaXmlElement $parent) {
    $listData = $this->data()->messageConversationsList();
    if (!empty($listData['abs_count'])) {
      $conversationsElement = $parent->appendElement('conversations');
      foreach ($listData['data'] as $conversation) {
        $conversationElement = $conversationsElement->appendElement('conversation');
        $conversationElement->appendElement(
          'last-message',
          array('time' => date('Y-m-d H:i:s', $conversation['time'])),
          PapayaUtilStringXml::escape($this->_getConversationText($conversation['text']))
        );
        $conversationElement->appendElement(
          'surfer-contact',
          array(
            'name' => $conversation['surfer']['name'],
            'avatar' => PapayaUtilStringXml::escapeAttribute($conversation['surfer']['avatar'])
          )
        );
        $conversationElement->appendElement(
          'messages-page-link', array(), PapayaUtilStringXml::escape($conversation['messages_page_link'])
        );
      }
      $this->paging()->appendTo($conversationsElement);
    } else {
      $parent->appendElement(
        'message',
        array('type' => 'no-message-conversations'),
        $this->data()->messages['no_message_conversations']
      );
    }
  }

  /**
   * Get conversation text and trim message by using lastMessageMaxLength
   *
   * @param string $text
   * @return string
   */
  protected function _getConversationText($text) {
    if (strlen($text) > $this->data()->lastMessageMaxLength) {
      return substr($text, 0, $this->data()->lastMessageMaxLength - 3).'...';
    }
    return $text;
  }
}