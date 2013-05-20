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
abstract class ACommunitySurfersPage extends base_content implements PapayaPluginCacheable {

  /**
   * Use a advanced community parameter group name
   * @var string
   */
  public $paramName = '';

  /**
   * Display mode
   * @var string
   */
  protected $_displayMode = '';

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
    'timeframe' => array(
      'Timeframe in days', 'isNum', TRUE, 'input', 30,
      'Get surfers by last action or registration time in a specified timeframe.', 365
    ),
    'limit' => array(
      'Limit', 'isNum', TRUE, 'input', 30,
      "Note: The contacts display mode has three lists for contacts,
      received contact requests and sent contact requests.",
      5
    ),
    'show_paging' => array(
      'Show paging', 'isNum', TRUE, 'yesno', NULL, 'Note: The contacts display mode needs paging.', 1
    ),
    'Captions',
    'caption_contacts' => array(
      'Contacts', 'isNoHTML', TRUE, 'input', 200, '', 'Contacts'
    ),
    'caption_own_contact_requests' => array(
      'Sent contact requests', 'isNoHTML', TRUE, 'input', 200, '', 'Sent contact requests'
    ),
    'caption_contact_requests' => array(
      'Received contact requests', 'isNoHTML', TRUE, 'input', 200, '', 'Received contact requests'
    ),
    'caption_last_action' => array(
      'Last Action', 'isNoHTML', TRUE, 'input', 200, '', 'Last action'
    ),
    'caption_registration' => array(
      'Registered Since', 'isNoHTML', TRUE, 'input', 200, '', 'Registered sine'
    ),
    'Command Captions',
    'caption_command_accept_contact_request' => array(
      'Accept contact request', 'isNoHTML', TRUE, 'input', 200, '', 'Accept contact request'
    ),
    'caption_command_decline_contact_request' => array(
      'Decline contact request', 'isNoHTML', TRUE, 'input', 200, '', 'Decline contact request'
    ),
    'caption_command_remove_contact_request' => array(
      'Remove contact request', 'isNoHTML', TRUE, 'input', 200, '', 'Remove contact request'
    ),
    'caption_command_remove_contact' => array(
      'Remove contact', 'isNoHTML', TRUE, 'input', 200, '', 'Remove contact'
    ),
    'Messages',
    'message_empty_list' => array(
      'No Entries', 'isNoHTML', TRUE, 'input', 200, '', 'No entries.'
    )
  );

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
      $definitionValues = array('acommunity_surfers_page', $this->_displayMode);
      $definitionParameters = array();
      include_once(dirname(__FILE__).'/../Cache/Identifier/Values.php');
      $values = new ACommunityCacheIdentifierValues();
      switch ($this->_displayMode) {
        case 'lastaction':
          $definitionParameters[] = 'lastaction_list_page';
          $definitionValues[] = $values->surferLastActionTime();
          break;
        case 'registration':
          $definitionParameters[] = 'registration_list_page';
          $definitionValues[] = $values->surferLastRegistrationTime();
          break;
        case 'contacts_and_requests':
          $ressource = $this->setRessourceData();
          if (!empty($ressource)) {
            $command = $this->surfers()->parameters()->get('command', NULL);
            if (empty($command)) {
              $definitionValues[] = $ressource['id'];
              $definitionValues[] = $values->lastChangeTime('contacts:surfer_'.$ressource['id']);
              $definitionParameters[] = 'contacts_list_page';
              $definitionParameters[] = 'own_contact_requests_list_page';
              $definitionParameters[] = 'contact_requests_list_page';
              $definitionParameters[] = 'surfer_handle';
              $definitionParameters[] = 'command';
            } else {
              $this->_cacheDefiniton = new PapayaCacheIdentifierDefinitionBoolean(FALSE);
            }
          }
          break;
      }
      if (is_null($this->_cacheDefiniton)) {
        $this->_cacheDefiniton = new PapayaCacheIdentifierDefinitionGroup(
          new PapayaCacheIdentifierDefinitionValues($definitionValues),
          new PapayaCacheIdentifierDefinitionParameters($definitionParameters, $this->paramName)
        );
      }
    }
    return $this->_cacheDefiniton;
  }

  /**
   * Set surfer ressource data to load corresponding surfer
   */
  public function setRessourceData() {
    return $this->surfers()->data()->ressource('surfer', $this);
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
    }
    return $this->_surfers;
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
      $this->data,
      array(
        'caption_last_action', 'caption_registration',
        'caption_contacts', 'caption_own_contact_requests', 'caption_contact_requests',
        'caption_command_accept_contact_request', 'caption_command_decline_contact_request',
        'caption_command_remove_contact_request', 'caption_command_remove_contact'
      ),
      array('message_empty_list')
    );
    return $this->surfers()->getXml();
  }
}