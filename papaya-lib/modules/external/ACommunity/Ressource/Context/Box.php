<?php
/**
 * Advanced community ressource context box
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
 * Advanced community ressource context box
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityRessourceContextBox extends base_actionbox implements PapayaPluginCacheable {

  public $paramName = 'acrc';

  /**
  * Content edit fields
  * @var array $editFields
  */
  public $editFields = array(
    'General Ressouce Settings',
    'image_size' => array(
      'Image Size', 'isNum', TRUE, 'input', 30, '', 22
    ),
    'image_resize_mode' => array(
      'Image Resize Mode', 'isAlpha', TRUE, 'translatedcombo',
       array(
         'abs' => 'Absolute', 'max' => 'Maximum', 'min' => 'Minimum', 'mincrop' => 'Minimum cropped'
       ), '', 'mincrop'
    ),
    'Surfer Ressource Settings',
    'Captions',
    'caption_surfer_name' => array(
      'Surfer Name', 'isNoHTML', TRUE, 'input', 200, '', 'Name'
    ),
    'caption_surfer_avatar' => array(
      'Surfer Avatar', 'isNoHTML', TRUE, 'input', 200, '', 'Avatar'
    ),
    'Messages',
    'message_no_surfer' => array(
      'No Surfer', 'isNoHTML', TRUE, 'input', 200, '', 'No surfer selected.'
    ),
    'Group Ressource Settings',
    'Captions',
    'caption_time' => array(
      'Time', 'isNoHTML', TRUE, 'input', 200, '', 'Time'
    ),
    'Messages',
    'message_access_denied' => array(
      'Access Denied', 'isNoHTML', TRUE, 'input', 200, '', 'No group access.'
    )
  );

  /**
   * Connector to get page module's ressource
   * @var ACommunityConnector
   */
  protected $_acommunityConnector = NULL;

  /**
   * Surfer object
   * @var ACommunitySurfer
   */
  protected $_surfer = NULL;

  /**
   * Group object
   * @var ACommunityGroup
   */
  protected $_group = NULL;

  /**
   * Define the cache definition for output.
   *
   * @see PapayaPluginCacheable::cacheable()
   * @param PapayaCacheIdentifierDefinition $definition
   * @return PapayaCacheIdentifierDefinition
   */
  public function cacheable(PapayaCacheIdentifierDefinition $definition = NULL) {
    return $this->_cacheDefiniton = new PapayaCacheIdentifierDefinitionBoolean(FALSE);
  }

  /**
   * Set surfer ressource data to load corresponding surfer
   */
  public function setRessourceData() {
    $ressource = $this->acommunityConnector()->ressource();
    if (isset($ressource->id)) {
      switch ($ressource->type) {
        case 'surfer':
          $this->surfer()->data()->ressource($ressource);
          return $ressource;
          break;
        case 'group':
          $this->group()->data()->ressource($ressource);
          if (!empty($this->parentObj->moduleObj->surferHasGroupAccess)) {
            $this->group()->data()->surferHasGroupAccess(
              $this->parentObj->moduleObj->surferHasGroupAccess
            );
          }
          return $ressource;
          break;
      }
    }
    return NULL;
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
      include_once(dirname(__FILE__).'/../../Surfer.php');
      $this->_surfer = new ACommunitySurfer();
      $this->_surfer->parameterGroup($this->paramName);
      $this->_surfer->data()->languageId = $this->papaya()->request->languageId;
      $this->_surfer->data()->mode = 'surfer-bar';
    }
    return $this->_surfer;
  }

  /**
  * Get (and, if necessary, initialize) the ACommunityGroup object
  *
  * @return ACommunityGroup $group
  */
  public function group(ACommunityGroup $group = NULL) {
    if (isset($group)) {
      $this->_group = $group;
    } elseif (is_null($this->_group)) {
      include_once(dirname(__FILE__).'/../../Group.php');
      $this->_group = new ACommunityGroup();
      $this->_group->module = $this;
      $this->_group->parameterGroup($this->paramName);
      $this->_group->data()->languageId = $this->papaya()->request->languageId;
      $this->_group->data()->mode = 'group-bar';
    }
    return $this->_group;
  }

  /**
   * Get/set advanced community connector
   *
   * @param object $connector
   * @return object
   */
  public function acommunityConnector(ACommunityConnector $connector = NULL) {
    if (isset($connector)) {
      $this->_acommunityConnector = $connector;
    } elseif (is_null($this->_acommunityConnector)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
      $this->_acommunityConnector = base_pluginloader::getPluginInstance(
        '0badeb14ea2d41d5bcfd289e9d190534', $this
      );
    }
    return $this->_acommunityConnector;
  }

  /**
  * Get parsed data
  *
  * @return string $result
  */
  function getParsedData() {
    $this->initializeParams();
    $ressource = $this->setRessourceData();
    $this->setDefaultData();
    if (isset($ressource->type)) {
      if ($ressource->type == 'surfer') {
        $captionNames = array('caption_surfer_name', 'caption_surfer_avatar');
        $messageNames = array('message_no_surfer');
        $this->data['avatar_size'] = $this->data['image_size'];
        $this->data['avatar_resize_mode'] = $this->data['image_resize_mode'];
        $this->surfer()->data()->setPluginData($this->data, $captionNames, $messageNames);
        return $this->surfer()->getXml();
      } elseif ($ressource->type == 'group') {
        $captionNames = array('caption_time');
        $messageNames = array('message_access_denied');
        $this->group()->data()->setPluginData($this->data, $captionNames, $messageNames);
        return $this->group()->getXml();
      }
    }
  }

}