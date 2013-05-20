<?php
/**
 * Advanced community surfers box
 *
 * Offers status information of logged in user and links to certain surfer pages
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
 * If you want to ignore the last time of the surfers' last action property set this
 * constant to 0.
 */
if (!defined('PAPAYA_ACOMMUNITY_CACHE_LAST_ACTIONS_BOX_USE_LAST_TIME')) {
  define('PAPAYA_ACOMMUNITY_CACHE_LAST_ACTIONS_BOX_USE_LAST_TIME', 1);
}

/**
 * If you want to ignore the last time of the surfers' registration property set this
 * constant to 0.
 */
if (!defined('PAPAYA_ACOMMUNITY_CACHE_REGISTRATIONS_BOX_USE_LAST_TIME')) {
  define('PAPAYA_ACOMMUNITY_CACHE_REGISTRATIONS_BOX_USE_LAST_TIME', 1);
}

/**
 * If you want to ignore the last change time of surfer's contacts set this constant to 0.
 */
if (!defined('PAPAYA_ACOMMUNITY_CACHE_CONTACTS_BOX_USE_LAST_CHANGE_TIME')) {
  define('PAPAYA_ACOMMUNITY_CACHE_CONTACTS_BOX_USE_LAST_CHANGE_TIME', 1);
}

/**
 * Advanced community surfers box
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
abstract class ACommunitySurfersBox extends base_actionbox implements PapayaPluginCacheable {

  /**
   * Parameter prefix name
   * @var string $paramName
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
      'Limit', 'isNum', TRUE, 'input', 30, '', 5
    ),
    'show_paging' => array(
      'Show paging', 'isNum', TRUE, 'yesno', NULL, NULL, 0
    ),
    'Captions',
    'caption_last_action' => array(
      'Last Action', 'isNoHTML', TRUE, 'input', 200, '', 'Last action'
    ),
    'caption_registration' => array(
      'Registered Since', 'isNoHTML', TRUE, 'input', 200, '', 'Registered sine'
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
      $definitionValues = array('acommunity_surfers_box', $this->_displayMode);
      $definitionParameters = array();
      include_once(dirname(__FILE__).'/../Cache/Identifier/Values.php');
      $values = new ACommunityCacheIdentifierValues();
      switch ($this->_displayMode) {
        case 'lastaction':
          $definitionParameters[] = 'lastaction_list_page';
          if (PAPAYA_ACOMMUNITY_CACHE_LAST_ACTIONS_BOX_USE_LAST_TIME == 1) {
            $definitionValues[] = $values->surferLastActionTime();
          }
          break;
        case 'registration':
          $definitionParameters[] = 'registration_list_page';
          if (PAPAYA_ACOMMUNITY_CACHE_REGISTRATIONS_BOX_USE_LAST_TIME == 1) {
            $definitionValues[] = $values->surferLastRegistrationTime();
          }
          break;
        case 'contacts':
          $ressource = $this->setRessourceData();
          $definitionValues[] = !empty($ressource['id']) ? $ressource['id'] : NULL;
          if (PAPAYA_ACOMMUNITY_CACHE_CONTACTS_BOX_USE_LAST_CHANGE_TIME == 1) {
            $definitionValues[] = !empty($ressource['id']) ?
              $values->lastChangeTime('contacts_accepted:surfer_'.$ressource['id']) : 0;
          }
          $definitionParameters[] = 'contacts_list_page';
          break;
      }
      $this->_cacheDefiniton = new PapayaCacheIdentifierDefinitionGroup(
        new PapayaCacheIdentifierDefinitionValues($definitionValues),
        new PapayaCacheIdentifierDefinitionParameters($definitionParameters, $this->paramName)
      );
    }
    return $this->_cacheDefiniton;
  }

  /**
   * Set surfer ressource data to load corresponding surfer
   */
  public function setRessourceData() {
    return $this->surfers()->data()->ressource(
      'surfer', $this, array('surfer' => array('surfer_handle'))
    );
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
      array('caption_last_action', 'caption_registration'),
      array('message_empty_list')
    );
    return $this->surfers()->getXml();
  }
}