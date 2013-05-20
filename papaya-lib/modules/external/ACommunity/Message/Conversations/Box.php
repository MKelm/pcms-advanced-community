<?php
/**
 * Advanced community message conversations box
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
 * Basic box class
 */
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');

/**
 * Advanced community message conversations box
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityMessageConversationsBox extends base_actionbox {

  /**
   * Parameter prefix name
   * @var string $paramName
   */
  public $paramName = 'acmc';

  /**
   * Edit fields
   * @var array $editFields
   */
  public $editFields = array(
    'avatar_size' => array(
      'Avatar Size', 'isNum', TRUE, 'input', 30, '', 40
    ),
    'avatar_resize_mode' => array(
      'Avatar Resize Mode', 'isAlpha', TRUE, 'translatedcombo',
       array(
         'abs' => 'Absolute', 'max' => 'Maximum', 'min' => 'Minimum', 'mincrop' => 'Minimum cropped'
       ), '', 'mincrop'
    ),
    'message_conversations_per_page' => array(
      'Conversations per page', 'isNum', TRUE, 'input', 30, NULL, 10
    ),
    'last_message_max_length' => array(
      'Last Message Max. Length', 'isNum', TRUE, 'input', 30, NULL, 40
    ),
    'Messages',
    'message_no_login' => array(
      'No Login', 'isNoHTML', TRUE, 'input', 200, '', 'Please login to get message conversations.'
    ),
    'message_no_message_conversations' => array(
      'Empty List', 'isNoHTML', TRUE, 'input', 200, '', 'No message conversations yet.'
    )
  );

  /**
   * Messages object
   * @var ACommunityMessages
   */
  protected $_messages = NULL;

  /**
   * Get ressource data to load corresponding comments
   * Overwrite this method for customized ressources
   */
  public function setRessourceData() {
    $this->messages()->data()->ressource(
      'surfer', $this, array('surfer' => array('surfer_handle'))
    );
  }

  /**
  * Get (and, if necessary, initialize) the ACommunityMessages object
  *
  * @return ACommunityMessages $messages
  */
  public function messages(ACommunityMessages $messages = NULL) {
    if (isset($messages)) {
      $this->_messages = $messages;
    } elseif (is_null($this->_messages)) {
      include_once(dirname(__FILE__).'/../../Messages.php');
      $this->_messages = new ACommunityMessages();
      $this->_messages->parameterGroup($this->paramName);
      $this->_messages->data()->setPluginData(
        $this->data,
        array(),
        array('message_no_login', 'message_no_message_conversations')
      );
      $this->_messages->data()->languageId = $this->papaya()->request->languageId;
      $this->_messages->mode = 'message-conversations';
    }
    return $this->_messages;
  }

  /**
   * Get parsed data
   *
   * @return string $result XML
   */
  public function getParsedData() {
    $this->setDefaultData();
    $this->initializeParams();
    $this->setRessourceData();
    return $this->messages()->getXml();
  }

}
