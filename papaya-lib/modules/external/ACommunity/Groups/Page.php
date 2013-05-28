<?php
/**
 * Advanced community groups page
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
 * Advanced community groups page
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityGroupsPage extends base_content implements PapayaPluginCacheable {

  /**
   * Use a advanced community parameter group name
   * @var string
   */
  public $paramName = 'acgs';

  /**
   * Edit fields
   * @var array $editFields
   */
  public $editFields = array(
    'image_size' => array(
      'Image Size', 'isNum', TRUE, 'input', 30, '', 60
    ),
    'image_resize_mode' => array(
      'Image Resize Mode', 'isAlpha', TRUE, 'translatedcombo',
       array(
         'abs' => 'Absolute', 'max' => 'Maximum', 'min' => 'Minimum', 'mincrop' => 'Minimum cropped'
       ), '', 'mincrop'
    ),
    'groups_per_row' => array(
      'Groups Per Row', 'isNum', TRUE, 'input', 30, '', 3
    ),
    'groups_per_page' => array(
      'Groups Per Page', 'isNum', TRUE, 'input', 30, '', 12
    ),
    'Captions',
    'caption_command_add' => array(
      'Command Add Group', 'isNoHTML', TRUE, 'input', 200, '', 'Add group'
    ),
    'caption_dialog_button_add' => array(
      'Dialog Button Add', 'isNoHTML', TRUE, 'input', 200, '', 'Add'
    ),
    'caption_command_delete' => array(
      'Command Delete Group', 'isNoHTML', TRUE, 'input', 200, '', 'Delete'
    ),
    'Messages',
    'message_no_groups' => array(
      'No Groups', 'isNoHTML', TRUE, 'input', 200, '', 'No groups.'
    )
  );

  /**
   * Names of caption data
   * @var array
   */
  protected $_captionNames = array(
    'caption_dialog_button_add', 'caption_command_delete', 'caption_command_add'
  );

  /**
   * Names of message data
   * @var array
   */
  protected $_messageNames = array('message_no_groups');

  /**
   * Groups object
   * @var ACommunityGroups
   */
  protected $_groups = NULL;

  /**
   * Cache definition
   * @var PapayaCacheIdentifierDefinition
   */
  protected $_cacheDefiniton = NULL;

  /**
   * Contains current groups onwer status
   * Overwrite this property to get a page with owned groups only
   * @var boolean
   */
  protected $_surferIsGroupsOwner = FALSE;

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
      include_once(dirname(__FILE__).'/../Cache/Identifier/Values.php');
      $values = new ACommunityCacheIdentifierValues();

      $this->setRessourceData();
      $moderator = (int)$this->groups()->data()->surferIsModerator();
      $owner = (int)$this->groups()->data()->surferIsGroupsOwner();
      if ($owner) {
        $lastChangeRessource = 'groups:surfer_'.$this->groups()->data()->currentSurferId();
      } else {
        $lastChangeRessource = 'groups';
      }

      $definitionValues = array(
        'acommunity_groups_page',
        $moderator,
        $owner,
        $values->lastChangeTime($lastChangeRessource)
      );
      $definitionParameters = array('groups_page');
      $this->_cacheDefiniton = new PapayaCacheIdentifierDefinitionGroup(
        new PapayaCacheIdentifierDefinitionValues($definitionValues),
        new PapayaCacheIdentifierDefinitionParameters($definitionParameters, $this->paramName)
      );
    }
    return $this->_cacheDefiniton;
  }

  /**
  * Get (and, if necessary, initialize) the ACommunityGroups object
  *
  * @return ACommunityGroups $groups
  */
  public function groups(ACommunityGroups $groups = NULL) {
    if (isset($groups)) {
      $this->_groups = $groups;
    } elseif (is_null($this->_groups)) {
      include_once(dirname(__FILE__).'/../Groups.php');
      $this->_groups = new ACommunityGroups();
      $this->_groups->parameterGroup($this->paramName);
      $this->_groups->data()->languageId = $this->papaya()->request->languageId;
    }
    return $this->_groups;
  }

  /**
   * Set surfer ressource data to load corresponding surfer
   */
  public function setRessourceData() {
    $this->groups()->data()->surferIsGroupsOwner($this->_surferIsGroupsOwner);
    return $this->groups()->data()->ressource('surfer', $this);
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
    $this->groups()->data()->setPluginData(
      $this->data, $this->_captionNames, $this->_messageNames
    );
    return $this->groups()->getXml();
  }
}