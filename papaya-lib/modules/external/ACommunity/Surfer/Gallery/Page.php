<?php
/**
 * Advanced community surfer gallery page
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
 * MediaImageGalleryPage class to extend
 */
require_once(PAPAYA_INCLUDE_PATH.'modules/free/thumbs/Image/Gallery/Page.php');

/**
 * Advanced community surfer gallery page
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunitySurferGalleryPage extends MediaImageGalleryPage implements PapayaPluginCacheable {

  /**
   * Use a advanced community parameter group name
   * @var string
   */
  public $paramName = 'acsg';

  /**
   * Id of elected surfer gallery folder
   * @var integer
   */
  protected $_galleryFolderId = NULL;

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
      $this->_cacheDefinition = $definition;
    } elseif (NULL == $this->_cacheDefinition) {
      $this->initializeParams();
      $ressource = $this->setRessourceData();
      $definitionValues = array('acommunity_surfer_gallery');
      if (!empty($ressource)) {
        $command = isset($this->params['command']) ? $this->params['command'] : NULL;
        if ($command != 'delete_folder' && !empty($this->params['folder_id'])) {
          $folder = $this->params['folder_id'];
        } else {
          $folder = 'base';
        }
        include_once(dirname(__FILE__).'/../../Cache/Identifier/Values.php');
        $values = new ACommunityCacheIdentifierValues();
        $definitionValues[] = $ressource['id'];
        $definitionValues[] = $folder;
        $definitionValues[] = $values->lastChangeTime(
          'surfer_gallery_images:folder_'.$folder.':surfer_'.$ressource['id']
        );
      }
      $this->_cacheDefinition = new PapayaCacheIdentifierDefinitionGroup(
        new PapayaCacheIdentifierDefinitionValues($definitionValues),
        new PapayaCacheIdentifierDefinitionParameters(
          array('enlarge', 'index', 'offset', 'command', 'id'), $this->paramName
        )
      );
    }
    return $this->_cacheDefinition;
  }

  /**
   * Check url name to fix wrong page names
   *
   * @param string $currentFileName
   * @param string $outputMode
   */
  public function checkURLFileName($currentFileName, $outputMode) {
    $this->setRessourceData();
    return $this->gallery()->checkURLFileName($this, $currentFileName, $outputMode, 's-gallery');
  }

  /**
   * Set surfer ressource data to load corresponding surfer
   */
  public function setRessourceData() {
    return $this->gallery()->data()->ressource('surfer', $this, array('surfer' => 'surfer_handle'));
  }

  /**
  * Get (and, if necessary, initialize) the ACommunitySurferGallery object
  *
  * @return ACommunitySurferGallery $gallery
  */
  public function gallery(ACommunitySurferGallery $gallery = NULL) {
    if (isset($gallery)) {
      $this->_gallery = $gallery;
    } elseif (is_null($this->_gallery)) {
      include_once(dirname(__FILE__).'/../Gallery.php');
      $this->_gallery = new ACommunitySurferGallery();
      $this->_gallery->parameterGroup($this->paramName);
      $this->_gallery->languageId = $this->papaya()->request->languageId;
      $this->_gallery->data()->languageId = $this->papaya()->request->languageId;
    }
    return $this->_gallery;
  }

  /**
   * Callback method for comments box to get current image id as ressource id
   *
   * @return string
   */
  public function callbackGetCurrentImageId() {
    return $this->gallery()->currentFileId;
  }

  /**
   * Get id of current selected gallery folder
   *
   * @return string
   */
  protected function _galleryFolderId() {
    if (is_null($this->_galleryFolderId)) {
      $ressource = $this->gallery()->data()->ressource();
      if (!empty($ressource)) {
        $filter = array('surfer_id' => $ressource['id']);
        $command = isset($this->params['command']) ? $this->params['command'] : NULL;
        if ($command != 'delete_folder' && !empty($this->params['folder_id'])) {
          $filter['folder_id'] = $this->params['folder_id'];
        } else {
          $filter['parent_folder_id'] = 0;
        }
        $this->gallery()->data()->galleries()->load($filter, 1);
        if (empty($this->params['folder_id']) &&
            count($this->gallery()->data()->galleries()) == 0) {
          $languageId = $this->papaya()->request->languageId;
          $parentFolder = $this->gallery()->data()->mediaDBEdit()->getFolder(
            $this->data['directory']
          );
          if (!empty($parentFolder[$languageId])) {
            $newFolderId = $this->gallery()->data()->mediaDBEdit()->addFolder(
              $parentFolder[$languageId]['folder_id'],
              $parentFolder[$languageId]['parent_path'].$parentFolder[$languageId]['folder_id'].';',
              $parentFolder[$languageId]['permission_mode']
            );
            if (!empty($newFolderId)) {
              $this->gallery()->data()->mediaDBEdit()->addFolderTranslation(
                $newFolderId, $languageId, $ressource['id']
              );
              $gallery = $this->gallery()->data()->gallery();
              $gallery['surfer_id'] = $ressource['id'];
              $gallery['folder_id'] = $newFolderId;
              $gallery['parent_folder_id'] = 0;
              $gallery->save();
              $this->_galleryFolderId = $newFolderId;
            } else {
              $this->_galleryFolderId = FALSE;
            }
          } else {
            $this->_galleryFolderId = FALSE;
          }
        } elseif (count($this->gallery()->data()->galleries()) > 0) {
          $gallery = reset($this->gallery()->data()->galleries()->toArray());
          $this->_galleryFolderId = $gallery['folder_id'];
        }
      }
    }
    return $this->_galleryFolderId;
  }

  /**
   * Overwrite get parsed data to load surfer gallery
   *
   * @return string
   */
  public function getParsedData() {
    $this->setDefaultData();
    $this->initializeParams();
    $this->setRessourceData();
    $this->data['directory'] = $this->_galleryFolderId();
    $additionalParameters = array();
    if (isset($this->params['surfer_handle'])) {
      $additionalParameters['surfer_handle'] = $this->params['surfer_handle'];
    }
    $command = isset($this->params['command']) ? $this->params['command'] : NULL;
    if ($command != 'delete_folder' && isset($this->params['folder_id'])) {
      $additionalParameters['folder_id'] = $this->params['folder_id'];
    }
    $this->gallery()->reference()->setParameters(
      $additionalParameters, $this->paramName
    );
    $this->gallery()->initialize($this, $this->data);
    $this->gallery()->load();
    return $this->gallery()->getXml();
  }
}