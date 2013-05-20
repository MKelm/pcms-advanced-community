<?php
/**
 * Advanced community surfer gallery teaser box
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
 * Advanced community surfer gallery teaser box
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunitySurferGalleryTeaserBox extends base_actionbox implements PapayaPluginCacheable {

  /**
   * Parameter prefix name
   * @var string $paramName
   */
  public $paramName = 'acsg';

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
      $definitionValues = array('acommunity_surfer_gallery_teaser');
      if (!empty($ressource)) {
        $ressource = $this->setRessourceData();
        if (!empty($ressource)) {
          include_once(dirname(__FILE__).'/../../../Cache/Identifier/Values.php');
          $values = new ACommunityCacheIdentifierValues();
          $definitionValues[] = $this->teaser()->data()->ressourceIsActiveSurfer;
          $definitionValues[] = $ressource['type'];
          $definitionValues[] = $ressource['id'];
          $definitionValues[] = $values->lastChangeTime(
            'surfer_gallery_images:folder_base:surfer_'.$ressource['id']
          );
        }
      }
      $this->_cacheDefinition = new PapayaCacheIdentifierDefinitionValues($definitionValues);
    }
    return $this->_cacheDefinition;
  }

  /**
   * Set ressource data to get surfer
   */
  public function setRessourceData() {
    $surferHandle = NULL;
    if (!empty($this->parentObj->moduleObj->paramName)) {
      $parameters = $this->papaya()->request->getParameterGroup(
        $this->parentObj->moduleObj->paramName
      );
      if (isset($parameters['surfer_handle'])) {
        $surferHandle = $parameters['surfer_handle'];
      }
    }
    return $this->teaser()->data()->ressource('surfer', $surferHandle);
  }

  /**
  * Get (and, if necessary, initialize) the ACommunitySurferGalleryTeaser object
  *
  * @return ACommunitySurferGalleryTeaser $teaser
  */
  public function teaser(ACommunitySurferGalleryTeaser $teaser = NULL) {
    if (isset($teaser)) {
      $this->_teaser = $teaser;
    } elseif (is_null($this->_teaser)) {
      include_once(dirname(__FILE__).'/../Teaser.php');
      $this->_teaser = new ACommunitySurferGalleryTeaser();
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