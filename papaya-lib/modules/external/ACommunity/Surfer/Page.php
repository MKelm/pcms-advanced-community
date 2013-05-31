<?php
/**
 * Advanced community surfer page
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
 * Advanced community surfer page
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunitySurferPage extends base_content implements PapayaPluginCacheable {

  /**
   * Use a advanced community parameter group name
   * @var string
   */
  public $paramName = 'acsp';

  /**
  * Content edit fields
  * @var array $editFields
  */
  public $editFields = array(
    'avatar_size' => array(
      'Avatar Size', 'isNum', TRUE, 'input', 30, '', 160
    ),
    'avatar_resize_mode' => array(
      'Avatar Resize Mode', 'isAlpha', TRUE, 'translatedcombo',
       array(
         'abs' => 'Absolute', 'max' => 'Maximum', 'min' => 'Minimum', 'mincrop' => 'Minimum cropped'
       ), '', 'mincrop'
    ),
    'dynamic_data_categories' => array(
      'Dynamic Data Categories', 'isNum', FALSE, 'function', 'callbackDynamicDataCategories'
    ),
    'Titles',
    'title_gender_male' => array(
      'Gender Male', 'isNoHTML', TRUE, 'input', 200, '', 'Male'
    ),
    'title_gender_female' => array(
      'Gender Female', 'isNoHTML', TRUE, 'input', 200, '', 'Female'
    ),
    'Captions',
    'caption_base_details' => array(
      'Base Details', 'isNoHTML', TRUE, 'input', 200, '', 'Base'
    ),
    'caption_surfer_name' => array(
      'Surfer Name', 'isNoHTML', TRUE, 'input', 200, '', 'Name'
    ),
    'caption_surfer_email' => array(
      'Surfer E-Mail', 'isNoHTML', TRUE, 'input', 200, '', 'E-Mail'
    ),
    'caption_surfer_gender' => array(
      'Surfer Gender', 'isNoHTML', TRUE, 'input', 200, '', 'Gender'
    ),
    'caption_surfer_avatar' => array(
      'Surfer Avatar', 'isNoHTML', TRUE, 'input', 200, '', 'Avatar'
    ),
    'caption_surfer_lastlogin' => array(
      'Surfer Last Login', 'isNoHTML', TRUE, 'input', 200, '', 'Last login'
    ),
    'caption_surfer_lastaction' => array(
      'Surfer Last Action', 'isNoHTML', TRUE, 'input', 200, '', 'Last action'
    ),
    'caption_surfer_registration' => array(
      'Surfer Registration', 'isNoHTML', TRUE, 'input', 200, '', 'Registration'
    ),
    'caption_surfer_group' => array(
      'Surfer Group', 'isNoHTML', TRUE, 'input', 200, '', 'Group'
    ),
    'caption_send_message' => array(
      'Send message', 'isNoHTML', TRUE, 'input', 200, '', 'Send message'
    ),
    'Contact Status Captions',
    'caption_contact_status_none' => array(
      'Is no contact', 'isNoHTML', TRUE, 'input', 200, '', 'Request contact'
    ),
    'caption_contact_status_direct' => array(
      'Is contact', 'isNoHTML', TRUE, 'input', 200, '', 'Contact accepted'
    ),
    'caption_contact_status_pending' => array(
      'Is request', 'isNoHTML', TRUE, 'input', 200, '', 'Contact request pending'
    ),
    'caption_contact_status_own_pending' => array(
      'Is own request', 'isNoHTML', TRUE, 'input', 200, '', 'Own contact request pending'
    ),
    'Command Captions',
    'caption_command_request_contact' => array(
      'Request contact', 'isNoHTML', TRUE, 'input', 200, '', 'Request'
    ),
    'caption_command_accept_contact_request' => array(
      'Accept contact request', 'isNoHTML', TRUE, 'input', 200, '', 'Accept'
    ),
    'caption_command_decline_contact_request' => array(
      'Decline contact request', 'isNoHTML', TRUE, 'input', 200, '', 'Decline'
    ),
    'caption_command_remove_contact_request' => array(
      'Remove contact request', 'isNoHTML', TRUE, 'input', 200, '', 'Remove contact request'
    ),
    'caption_command_remove_contact' => array(
      'Remove contact', 'isNoHTML', TRUE, 'input', 200, '', 'Remove contact'
    ),
    'Message',
    'message_no_surfer' => array(
      'No Surfer', 'isNoHTML', TRUE, 'input', 200, '', 'No surfer selected.'
    )
  );

  /**
   * Surfer object
   * @var ACommunitySurfer
   */
  protected $_surfer = NULL;

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
      $definitionValues = array('acommunity_surfer_page');
      $ressource = $this->setRessourceData();
      if (isset($ressource->id)) {
        $command = NULL;
        $currentSurferId = $this->surfer()->data()->currentSurferId();
        if (!empty($currentSurferId) && $currentSurferId != $ressource->id) {
          $command = $this->surfer()->parameters()->get('command', NULL);
        }
        if (empty($command)) {
          include_once(dirname(__FILE__).'/../Cache/Identifier/Values.php');
          $values = new ACommunityCacheIdentifierValues();
          $definitionValues[] = $currentSurferId;
          $definitionValues[] = $ressource->id;
          $definitionValues[] = $values->lastChangeTime('surfer:surfer_'.$ressource->id);
          $definitionValues[] = $values->lastChangeTime(
            'contact:surfer_'.$currentSurferId.':surfer_'.$ressource->id
          );
        } else {
          $this->_cacheDefiniton = new PapayaCacheIdentifierDefinitionBoolean(FALSE);
        }
      }
      if (is_null($this->_cacheDefiniton)) {
        $this->_cacheDefiniton = new PapayaCacheIdentifierDefinitionGroup(
          new PapayaCacheIdentifierDefinitionValues($definitionValues),
          new PapayaCacheIdentifierDefinitionPage()
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
    return $this->surfer()->checkURLFileName($this, $currentFileName, $outputMode, 's-page');
  }

  /**
   * Set surfer ressource data to load corresponding surfer
   */
  public function setRessourceData() {
    $ressource = $this->surfer()->data()->ressource(
      'surfer', $this, array('surfer' => 'surfer_handle'), array('surfer' => 'surfer_page'), NULL, 'object'
    );
    $this->surfer()->acommunityConnector()->ressource($ressource);
    return $ressource;
  }

  /**
  * Get (and, if necessary, initialize) the ACommunitySurfer object
  *
  * @return ACommunitySurfer $surfer
  */
  public function surfer(ACommunityComments $surfer = NULL) {
    if (isset($surfer)) {
      $this->_surfer = $surfer;
    } elseif (is_null($this->_surfer)) {
      include_once(dirname(__FILE__).'/../Surfer.php');
      $this->_surfer = new ACommunitySurfer();
      $this->_surfer->parameterGroup($this->paramName);
      $this->_surfer->data()->languageId = $this->papaya()->request->languageId;
    }
    return $this->_surfer;
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
      'caption_base_details', 'caption_surfer_name',
      'caption_surfer_email', 'caption_surfer_gender', 'caption_surfer_avatar',
      'caption_surfer_lastlogin', 'caption_surfer_lastaction', 'caption_surfer_registration',
      'caption_surfer_group', 'caption_send_message',
      'caption_contact_status_none', 'caption_contact_status_direct', 'caption_contact_status_pending',
      'caption_contact_status_own_pending', 'caption_command_request_contact',
      'caption_command_accept_contact_request', 'caption_command_decline_contact_request',
      'caption_command_remove_contact_request', 'caption_command_remove_contact'
    );
    $this->surfer()->data()->setPluginData($this->data, $captionNames, array('message_no_surfer'));
    return $this->surfer()->getXml();
  }

  /**
  * Get form xml to select dynamic data categories by callback.
  *
  * @param string $name Field name
  * @param array $element Field element configurations
  * @param string $data Current field data
  * @return string $result XML
  */
  function callbackDynamicDataCategories($name, $element, $data) {
    $classes = $this->surfer()->communityConnector()->getProfileDataClasses();
    $result = '';
    $commonTitle = $this->_gt('Category');
    foreach ($classes as $class) {
      $classTitles = $this->surfer()->communityConnector()->getProfileDataClassTitles(
        $class['surferdataclass_id']
      );
      if (isset($classTitles[$this->papaya()->request->languageId])) {
        $title = $classTitles[$this->papaya()->request->languageId];
      } else {
        $title = sprintf('%s %d', $commonTitle, $row['surferdataclass_id']);
      }
      $checked = (is_array($data) && in_array($class['surferdataclass_id'], $data)) ?
        ' checked="checked"' : '';
      $result .= sprintf(
        '<input type="checkbox" name="%s[%s][]" value="%d" %s />%s'.LF,
        $this->paramName,
        $name,
        $class['surferdataclass_id'],
        $checked,
        $title
      );
    }
    return $result;
  }
}