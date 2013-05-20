<?php
/**
 * Advanced community messages page
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
* Basic class page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

/**
 * Advanced community messages page
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityMessagesPage extends base_content implements PapayaPluginCacheable {

  /**
   * Use a advanced community parameter group name
   * @var string
   */
  public $paramName = 'acmp';

  /**
  * Content edit fields
  * @var array $editFields
  */
  public $editFields = array(
    'page_title_messages' => array(
      'Title Messages',  'isNoHTML', TRUE, 'input', 200, '', 'Messages'
    ),
    'page_title_notifications' => array(
      'Title Notifications', 'isNoHTML', TRUE, 'input', 200, '', 'Notifications'
    ),
    'avatar_size' => array(
      'Avatar Size', 'isNum', TRUE, 'input', 30, '', 40
    ),
    'avatar_resize_mode' => array(
      'Avatar Resize Mode', 'isAlpha', TRUE, 'translatedcombo',
       array(
         'abs' => 'Absolute', 'max' => 'Maximum', 'min' => 'Minimum', 'mincrop' => 'Minimum cropped'
       ), '', 'mincrop'
    ),
    'messages_per_page' => array(
      'Messages per page', 'isNum', TRUE, 'input', 30, NULL, 10
    ),
    'Captions',
    'caption_dialog_text' => array(
      'Dialog Text', 'isNoHTML', TRUE, 'input', 200, '', 'Text'
    ),
    'caption_dialog_button' => array(
      'Dialog Button', 'isNoHTML', TRUE, 'input', 200, '', 'Add'
    ),
    'Message',
    'message_dialog_input_error' => array(
      'Dialog Input Error', 'isNoHTML', TRUE, 'input', 200, '',
      'Invalid input. Please check the field(s) "%s".'
    ),
    'message_no_message_conversation' => array(
      'No Message Conversations', 'isNoHTML', TRUE, 'input', 200, '',
      'No message conversation selected, please select one in the right box.'
    ),
    'message_no_messages' => array(
      'No Messages', 'isNoHTML', TRUE, 'input', 200, '', 'No messages found.'
    ),
    'message_no_login' => array(
      'No Login', 'isNoHTML', TRUE, 'input', 200, '', 'Please login to get messages.'
    )
  );

  /**
   * Messages object
   * @var ACommunityMessages
   */
  protected $_messages = NULL;

  /**
   * Cache definition
   * @var PapayaCacheIdentifierDefinition
   */
  protected $_cacheDefiniton = NULL;

  /**
   * Define the cache definition for output.
   *
   * @see PapayaPluginCacheable::cacheable()
   * @param PapayaCacheIdentifierDefinition $definition
   * @return PapayaCacheIdentifierDefinition
   */
  public function cacheable(PapayaCacheIdentifierDefinition $definition = NULL) {
    if (isset($definition)) {
      $this->_cacheDefiniton = $definition;
    } elseif (NULL == $this->_cacheDefiniton) {
      $currentSurferId = !empty($this->papaya()->surfer->surfer['surfer_id']) ?
          $this->papaya()->surfer->surfer['surfer_id'] : NULL;
      $notifications = $this->messages()->parameters()->get('notifications', NULL);
      if (!empty($currentSurferId) && !isset($notifications)) {
        $this->_cacheDefiniton = new PapayaCacheIdentifierDefinitionBoolean(FALSE);
      } else {
        $definitionValues = array('acommunity_messages_page');
        if (!empty($ressource) && isset($currentSurferId)) {
          include_once(dirname(__FILE__).'/../Cache/Identifier/Values.php');
          $values = new ACommunityCacheIdentifierValues();
          $definitionValues[] = $currentSurferId;
          $definitionValues[] = 'system';
          $definitionValues[] = $values->lastMessageTime($currentSurferId, 'system');
        }
        $this->_cacheDefiniton = new PapayaCacheIdentifierDefinitionGroup(
          new PapayaCacheIdentifierDefinitionValues($definitionValues),
          new PapayaCacheIdentifierDefinitionParameters(
            array('messages_page'), $this->paramName
          )
        );
      }
    }
    return $this->_cacheDefiniton;
  }

  /**
   * Check url name to fix wrong page names
   *
   * @param string $currentFileName
   * @param string $outputMode
   */
  public function checkURLFileName($currentFileName, $outputMode) {
    $this->setRessourceData();
    $pageNamePostfix = (empty($this->params['surfer_handle']) && empty($this->params['notifications'])) ?
      's-messages' : '-messages';
    $handle = empty($this->params['notifications']) ? NULL : 'system';
    return $this->messages()->checkURLFileName(
      $this, $currentFileName, $outputMode, $pageNamePostfix, $handle
    );
  }

  /**
   * Set surfer ressource data to load corresponding surfer
   */
  public function setRessourceData() {
    return $this->messages()->data()->ressource(
      'surfer',
      $this,
      array('surfer' => 'surfer_handle'),
      array('surfer' => array('surfer_handle', 'notifications'))
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
      include_once(dirname(__FILE__).'/../Messages.php');
      $this->_messages = new ACommunityMessages();
      $this->_messages->parameterGroup($this->paramName);
      $this->_messages->data()->languageId = $this->papaya()->request->languageId;
    }
    return $this->_messages;
  }

  /**
  * Get parsed data
  *
  * @return string $result
  */
  function getParsedData() {
    $this->initializeParams();
    $this->setRessourceData();
    $this->setDefaultData();
    $captionNames = array(
      'caption_dialog_text', 'caption_dialog_button'
    );
    $messageNames = array(
      'message_dialog_input_error', 'message_no_messages', 'message_no_login',
      'message_no_message_conversation'
    );
    $this->messages()->data()->setPluginData($this->data, $captionNames, $messageNames);
    return $this->messages()->getXml();
  }
}