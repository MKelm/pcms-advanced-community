<?php
/**
 * Advanced community surfers page
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
 * Advanced community surfers page
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunitySurfersPage extends base_content implements PapayaPluginCacheable {

  /**
   * Use a advanced community parameter group name
   * @var string
   */
  public $paramName = 'acss';

  /**
   * Display mode
   * @var string
   */
  protected $_displayMode = 'surfers';

  /**
   * Edit fields
   * @var array $editFields
   */
  public $editFields = array(
    'avatar_size' => array(
      'Avatar Size', 'isNum', TRUE, 'input', 30, '', 60
    ),
    'avatar_resize_mode' => array(
      'Avatar Resize Mode', 'isAlpha', TRUE, 'translatedcombo',
       array(
         'abs' => 'Absolute', 'max' => 'Maximum', 'min' => 'Minimum', 'mincrop' => 'Minimum cropped'
       ), '', 'mincrop'
    ),
    'limit' => array(
      'Limit', 'isNum', TRUE, 'input', 30, '', 15
    ),
    'show_filter_navigation' => array(
      'Show Filter Navigation', 'isNum', TRUE, 'yesno', NULL, '', 1
    ),
    'show_search_dialog' => array(
      'Show Search Dialog', 'isNum', TRUE, 'yesno', NULL, '', 1
    ),
    'show_paging' => array(
      'Show Paging', 'isNum', TRUE, 'yesno', NULL, '', 1
    ),
    'Captions',
    'caption_surfers' => array(
      'Surfers', 'isNoHTML', TRUE, 'input', 200, '', 'Surfers'
    ),
    'caption_command_invite_surfer' => array(
      'Command Invite', 'isNoHTML', TRUE, 'input', 200, 'In invite surfers mode for groups.',
      'Invite to group'
    ),
    'caption_command_remove_invitation' => array(
      'Command Remove Invitation', 'isNoHTML', TRUE, 'input', 200,
      'In membership invitations mode for groups.', 'Remove invitation'
    ),
    'caption_command_remove_member' => array(
      'Command Remove Member', 'isNoHTML', TRUE, 'input', 200, 'In members mode for groups.',
      'Remove member'
    ),
    'caption_command_accept_request' => array(
      'Command Accept Request', 'isNoHTML', TRUE, 'input', 200,
      'In membership requests mode for groups.', 'Accept request'
    ),
    'caption_command_decline_request' => array(
      'Command Decline Request', 'isNoHTML', TRUE, 'input', 200,
      'In membership requests mode for groups.', 'Decline request'
    ),
    'Dialog Captions',
    'caption_all' => array(
      'All', 'isNoHTML', TRUE, 'input', 200, '', 'All'
    ),
    'caption_dialog_search' => array(
      'Search', 'isNoHTML', TRUE, 'input', 200, '', 'Search'
    ),
    'caption_dialog_send' => array(
      'Send', 'isNoHTML', TRUE, 'input', 200, '', 'Send'
    ),

    'Messages',
    'message_empty_list' => array(
      'No Entries', 'isNoHTML', TRUE, 'input', 200, '', 'No entries.'
    ),
    'message_failed_to_execute_command' => array(
      'Failed To Execute Command', 'isNoHTML', TRUE, 'input', 200, '', 'Failed to execute command.'
    )
  );

  /**
   * Names of caption data
   * @var array
   */
  protected $_captionNames = array(
    'caption_surfers', 'caption_all', 'caption_dialog_search', 'caption_dialog_send',
    'caption_command_invite_surfer', 'caption_command_remove_invitation',
    'caption_command_accept_request', 'caption_command_decline_request',
    'caption_command_remove_member'
  );

  /**
   * Names of message data
   * @var array
   */
  protected $_messageNames = array('message_empty_list', 'message_failed_to_execute_command');

  /**
   * Surfers object
   * @var ACommunitySurfers
   */
  protected $_surfers = NULL;

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
      $command = $this->surfers()->parameters()->get('command', NULL);
      if (empty($command)) {
        include_once(dirname(__FILE__).'/../Cache/Identifier/Values.php');
        $values = new ACommunityCacheIdentifierValues();
        $definitionValues = array(
          'acommunity_surfers_page',
          $this->_displayMode,
          $values->surferLastRegistrationTime(),
          $values->lastChangeTime('surfer_names')
        );
        $ressource = $this->setRessourceData();
        if (isset($ressource->type) && $ressource->type == 'group') {
          $definitionValues[] = $ressource->type;
          $definitionValues[] = $ressource->id;
          $mode = $this->surfers()->parameters()->get('mode', NULL);
          if ($mode == 'invite_surfers' || $mode = 'membership_invitations') {
            $definitionValues[] = $values->lastChangeTime(
              'group:membership_invitations:group_'.$ressource->id
            );
          } elseif ($mode == 'membership_requests') {
            $definitionValues[] = $values->lastChangeTime(
              'group:membership_requests:group_'.$ressource->id
            );
          } elseif ($mode == 'members') {
            $definitionValues[] = $values->lastChangeTime(
              'group:memberships:group_'.$ressource->id
            );
          }
        }
        $definitionParameters = array(
          'surfers_search', 'surfers_character', 'surfers_list_page', 'mode', 'group_handle'
        );
        $this->_cacheDefiniton = new PapayaCacheIdentifierDefinitionGroup(
          new PapayaCacheIdentifierDefinitionValues($definitionValues),
          new PapayaCacheIdentifierDefinitionParameters($definitionParameters, $this->paramName)
        );
      } else {
        $this->_cacheDefiniton = new PapayaCacheIdentifierDefinitionBoolean(FALSE);
      }
    }
    return $this->_cacheDefiniton;
  }

  /**
  * Get (and, if necessary, initialize) the ACommunitySurfers object
  *
  * @return ACommunitySurfers $surfers
  */
  public function surfers(ACommunitySurfers $surfers = NULL) {
    if (isset($surfers)) {
      $this->_surfers = $surfers;
    } elseif (is_null($this->_surfers)) {
      include_once(dirname(__FILE__).'/../Surfers.php');
      $this->_surfers = new ACommunitySurfers();
      $this->_surfers->parameterGroup($this->paramName);
      $this->_surfers->data()->languageId = $this->papaya()->request->languageId;
      $this->_surfers->data()->displayMode = $this->_displayMode;
    }
    return $this->_surfers;
  }

  /**
   * Set surfer ressource data to load corresponding surfer
   */
  public function setRessourceData() {
    if (!empty($this->parentObj->moduleObj->params['group_handle'])) {
      $ressource = $this->surfers()->data()->ressource(
        'group', $this, array('group' => 'group_handle'), array('group' => array()), NULL, 'object'
      );
      $this->surfers()->acommunityConnector()->ressource($ressource);
      return $ressource;
    }
    return NULL;
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
    $this->surfers()->data()->setPluginData(
      $this->data, $this->_captionNames, $this->_messageNames
    );
    return $this->surfers()->getXml();
  }
}