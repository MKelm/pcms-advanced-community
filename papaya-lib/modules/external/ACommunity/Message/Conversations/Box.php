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
class ACommunityMessageConversationsBox extends base_actionbox implements PapayaPluginCacheable {

  /**
   * Parameter prefix name
   * @var string $paramName
   */
  public $paramName = 'acmcb';

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
   * Cache definition
   * @var PapayaCacheIdentifierDefinition
   */
  protected $_cacheDefiniton = NULL;

  /**
   * Current ressource
   * @var ACommunityUiContentRessource
   */
  protected $_ressource = NULL;

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
      $ressource = $this->setRessourceData();
      $definitionValues = array('acommunity_message_conversations_box');
      if (isset($ressource->id)) {
        include_once(dirname(__FILE__).'/../../Cache/Identifier/Values.php');
        $values = new ACommunityCacheIdentifierValues();
        $definitionValues[] = $ressource->type;
        $definitionValues[] = $ressource->id;
        $definitionValues[] = $values->lastMessageConversationTime($ressource->id);
      }
      $this->_cacheDefiniton = new PapayaCacheIdentifierDefinitionGroup(
        new PapayaCacheIdentifierDefinitionValues($definitionValues),
        new PapayaCacheIdentifierDefinitionParameters(
          array('message_conversations_page'), $this->paramName
        )
      );
    }
    return $this->_cacheDefiniton;
  }

  /**
   * Get ressource data to load corresponding comments
   * Overwrite this method for customized ressources
   */
  public function setRessourceData() {
    if (is_null($this->_ressource)) {
      $this->_ressource = $this->messages()->ressource();
      $this->_ressource->set('surfer', NULL, array('surfer' => array()), NULL, NULL, 'is_selected');
    }
    return $this->_ressource;
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
      $this->_messages->data()->languageId = $this->papaya()->request->languageId;
      $this->_messages->mode = 'message-conversations';
      $this->_messages->module = $this;
    }
    return $this->_messages;
  }

  /**
   * Get parsed data
   *
   * @return string $result XML
   */
  public function getParsedData() {
    $this->initializeParams();
    $this->setRessourceData();
    $this->setDefaultData();
    $this->messages()->data()->setPluginData(
      $this->data,
      array(),
      array('message_no_login', 'message_no_message_conversations')
    );
    return $this->messages()->getXml();
  }
}