<?php
/**
 * Advanced community image gallery page
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
 * Advanced community image gallery page
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityImageGalleryPage extends MediaImageGalleryPage implements PapayaPluginCacheable {

  /**
   * Use a advanced community parameter group name
   * @var string
   */
  public $paramName = 'acig';

  /**
   * Id of elected surfer gallery folder
   * @var integer
   */
  protected $_galleryFolderId = NULL;

  /**
   * Cache definition
   * @var PapayaCacheIdentifierDefinition
   */
  protected $_cacheDefinition = NULL;

  /**
   * Access flag for box modules, set by gallery()->data()->surferHasGroupAccess()
   * @var boolean
   */
  public $surferHasGroupAccess = FALSE;

  /**
   * Group surfer status
   * @var array
   */
  public $groupSurferStatus = array('is_member' => FALSE, 'is_owner' => FALSE);

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
    } elseif (is_null($this->_cacheDefinition)) {
      $ressource = $this->setRessourceData();
      $definitionValues = array('acommunity_image_gallery');
      if (isset($ressource->id)) {
        $definitionValues[] = $ressource->type;
        $definitionValues[] = $ressource->id;
        $access = TRUE;
        if ($ressource->type== 'group') {
          $access = $this->surferHasGroupAccess;
          if ($access) {
            // set status for delete image actions
            $definitionValues[] = (int)$this->gallery()->data()->surferHasStatus(
              $ressource->id, 'is_owner', 1
            ) || $this->gallery()->data()->surferIsModerator();
          }
        } else {
          // set status for delete image actions
          $definitionValues[] = (int)$ressource->validSurfer ||
            $this->gallery()->data()->surferIsModerator();
        }
        $definitionValues[] = (int)$access;
        // further settings for valid access
        if ($access) {
          include_once(dirname(__FILE__).'/../../Cache/Identifier/Values.php');
          $values = new ACommunityCacheIdentifierValues();
          $folderId = $this->gallery()->parameters()->get('folder_id', 0);
          if ($folderId == 0) {
            $folderId = 'base';
          }
          if ($ressource->type == 'group') {
            $lastChangeRessource = 'group_gallery_images:folder_'.$folderId.':group_'.$ressource->id;
          } else {
            $lastChangeRessource = 'surfer_gallery_images:folder_'.$folderId.':surfer_'.$ressource->id;
          }
          $definitionValues[] = $values->lastChangeTime($lastChangeRessource);
        }
        $this->_cacheDefinition = new PapayaCacheIdentifierDefinitionGroup(
          new PapayaCacheIdentifierDefinitionValues($definitionValues),
          new PapayaCacheIdentifierDefinitionParameters(
            array('enlarge', 'index', 'offset', 'command', 'folder_id', 'id'), $this->paramName
          )
        );
      } else {
        $this->_cacheDefinition = new PapayaCacheIdentifierDefinitionValues($definitionValues);
      }
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
    $ressource = $this->gallery()->ressource();
    $ressource->displayMode = 'gallery';
    $command = $ressource->getSourceParameter('command');
    if ($command != 'delete_folder') {
      $filterParameterNames = array(
        'surfer' => array('surfer_handle', 'folder_id'),
        'group' => array('group_handle', 'folder_id')
      );
    } else {
      $filterParameterNames = array('surfer' => 'surfer_handle', 'group' => 'group_handle');
    }
    $groupHandle = $ressource->getSourceParameter('group_handle');
    $ressource->set(
      isset($groupHandle) ? 'group' : 'surfer',
      array('surfer' => 'surfer_handle', 'group' => 'group_handle'),
      $filterParameterNames
    );
    $this->gallery()->data()->ressource($ressource);
    $this->gallery()->acommunityConnector()->ressource($ressource);
    if (isset($ressource->id) && $ressource->type == 'group') {
      $this->surferHasGroupAccess = $this->gallery()->data()->surferHasGroupAccess();
    }
    return $ressource;
  }

  /**
  * Get (and, if necessary, initialize) the ACommunityImageGallery object
  *
  * @return ACommunityImageGallery $gallery
  */
  public function gallery(ACommunityImageGallery $gallery = NULL) {
    if (isset($gallery)) {
      $this->_gallery = $gallery;
    } elseif (is_null($this->_gallery)) {
      include_once(dirname(__FILE__).'/../Gallery.php');
      $this->_gallery = new ACommunityImageGallery();
      $this->_gallery->papaya($this->papaya());
      $this->_gallery->parameterGroup($this->paramName);
      $this->_gallery->languageId = $this->papaya()->request->languageId;
      $this->_gallery->data()->languageId = $this->papaya()->request->languageId;
      $this->_gallery->module = $this;
    }
    return $this->_gallery;
  }

  /**
   * Callback method for comments box to get current image id as ressource id
   *
   * @return string
   */
  public function callbackGetCurrentImageId() {
    $currentFileId = $this->gallery()->currentFileId;
    if (empty($currentFileId) && !empty($this->params['enlarge'])) {
      $this->data['directory'] = $this->_galleryFolderId();
      $this->gallery()->initialize($this, $this->data);
      $this->gallery()->load();
      $currentFileId = $this->gallery()->currentFileId;
    }
    return $currentFileId;
  }

  /**
   * Get id of current selected gallery folder
   *
   * @return string
   */
  protected function _galleryFolderId() {
    if (is_null($this->_galleryFolderId)) {
      $ressource = $this->gallery()->data()->ressource();
      if (isset($ressource->id)) {
        $filter = array('ressource_type' => $ressource->type, 'ressource_id' => $ressource->id);
        $ressourceParameters = reset($ressource->parameters());
        if (!empty($ressourceParameters['folder_id'])) {
          $filter['folder_id'] = $ressourceParameters['folder_id'];
        } else {
          $filter['parent_folder_id'] = 0;
        }
        $this->gallery()->data()->galleries()->load($filter, 1);
        if (empty($ressourceParameters['folder_id']) &&
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
                $newFolderId, $languageId, $ressource->type.'_'.$ressource->id
              );
              $gallery = $this->gallery()->data()->gallery();
              $gallery['ressource_type'] = $ressource->type;
              $gallery['ressource_id'] = $ressource->id;
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
    $reference = $this->setRessourceData();
    $this->data['directory'] = $this->_galleryFolderId();
    foreach ($reference->parameters() as $paramName => $parameters) {
      $this->gallery()->reference()->setParameters($parameters, $paramName);
    }
    $this->gallery()->initialize($this, $this->data);
    $this->gallery()->load();
    return $this->gallery()->getXml();
  }
}