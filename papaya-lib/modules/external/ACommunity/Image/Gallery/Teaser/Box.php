<?php
/**
 * Advanced community image gallery teaser box
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
 * Advanced community image gallery teaser box
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityImageGalleryTeaserBox extends base_actionbox implements PapayaPluginCacheable {

  /**
   * Parameter prefix name
   * @var string $paramName
   */
  public $paramName = 'acig';

  /**
   * Edit fields
   * @var array $editFields
   */
  public $editFields = array(
    'Thumbnails',
    'thumbnail_amount' => array(
      'Amount', 'isNum', TRUE, 'input', 30, '', 4
    ),
    'thumbnail_size' => array(
      'Size', 'isNum', TRUE, 'input', 30, '', 100
    ),
    'thumbnail_resize_mode' => array(
      'Resize Mode', 'isAlpha', TRUE, 'translatedcombo',
       array(
         'abs' => 'Absolute', 'max' => 'Maximum', 'min' => 'Minimum',' mincrop' => 'Minimum cropped'
       ), '', 'mincrop'
    ),
    'Captions',
    'caption_add_new_images_link' => array(
      'Add New Images Link', 'isNoHTML', TRUE, 'input', 200, '', 'No gallery images yet. Add new images here!'
    ),
    'caption_more_images_link' => array(
      'More Images Link', 'isNoHTML', TRUE, 'input', 200, '', 'Check out more images here!'
    )
  );

  /**
   * Gallery teaser object
   * @var ACommunitySurferGalleryTeaser
   */
  protected $_teaser = NULL;

  /**
   * Cache definition
   * @var PapayaCacheIdentifierDefinition
   */
  protected $_cacheDefinition = NULL;

  /**
   * Define the cache definition for output.
   *
   * @see PapayaPluginCacheable::cacheable()
   * @param PapayaCacheIdentifierDefinition $definition
   * @return PapayaCacheIdentifierDefinition
   */
  public function cacheable(PapayaCacheIdentifierDefinition $definition = NULL) {
    if (isset($definition)) {
      $this->_cacheDefinition = $definition;
    } elseif (NULL == $this->_cacheDefinition) {
      $ressource = $this->setRessourceData();
      $definitionValues = array('acommunity_image_gallery_teaser');
      if (isset($ressource)) {
        include_once(dirname(__FILE__).'/../../../Cache/Identifier/Values.php');
        $values = new ACommunityCacheIdentifierValues();
        $definitionValues[] = $ressource->type;
        $definitionValues[] = $ressource->id;
        $access = TRUE;
        if ($ressource->type == 'group') {
          if (!empty($this->parentObj->moduleObj->surferHasGroupAccess)) {
            $this->teaser()->data()->surferHasGroupAccess = TRUE;
          } else {
            $access = FALSE;
          }
        }
        $definitionValues[] = (int)$access;
        if ($access) {
          if ($ressource->type == 'surfer') {
            $definitionValues[] = (int)$ressource->isActiveSurfer;
            $lastChangeRessource = 'surfer_gallery_images:folder_base:surfer_'.$ressource->id;
          } elseif ($ressource['type'] == 'group') {
            $definitionValues[] = (int)$this->teaser()->data()->surferHasStatus(
              $ressource->id, 'is_owner', 1
            );
            $lastChangeRessource = 'group_gallery_images:folder_base:group_'.$ressource->id;
          }
          if (isset($lastChangeRessource)) {
            $definitionValues[] = $values->lastChangeTime($lastChangeRessource);
          }
        }
      }
      $this->_cacheDefinition = new PapayaCacheIdentifierDefinitionValues($definitionValues);
    }
    return $this->_cacheDefinition;
  }

  /**
   * Set ressource by page module with connector to get surfer or group
   */
  public function setRessourceData() {
    return $this->teaser()->data()->ressource(
      $this->teaser()->acommunityConnector()->ressource(), NULL, NULL, NULL, NULL, 'object'
    );
  }

  /**
  * Get (and, if necessary, initialize) the ACommunityImageGalleryTeaser object
  *
  * @return ACommunityImageGalleryTeaser $teaser
  */
  public function teaser(ACommunityImageGalleryTeaser $teaser = NULL) {
    if (isset($teaser)) {
      $this->_teaser = $teaser;
    } elseif (is_null($this->_teaser)) {
      include_once(dirname(__FILE__).'/../Teaser.php');
      $this->_teaser = new ACommunityImageGalleryTeaser();
      $this->_teaser->parameterGroup($this->paramName);
      $this->_teaser->data()->languageId = $this->papaya()->request->languageId;
    }
    return $this->_teaser;
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
    $captionNames = array('caption_add_new_images_link', 'caption_more_images_link');
    $messageNames = array();
    $this->teaser()->data()->setPluginData($this->data, $captionNames, $messageNames);
    return $this->teaser()->getXml();
  }
}