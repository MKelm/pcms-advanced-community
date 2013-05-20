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
 * content_thumbs class to extend
 */
require_once(PAPAYA_INCLUDE_PATH.'modules/free/thumbs/content_thumbs.php');

/**
 * Advanced community surfer gallery page
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunitySurferGalleryPage extends content_thumbs implements PapayaPluginCacheable {

  /**
   * Use a advanced community parameter group name
   * @var string
   */
  public $paramName = 'acsg';

  /**
   * Surfer gallery data
   * @var ACommunitySurferGalleryData
   */
  protected $_data = NULL;

  /**
   * Id by selected surfer
   * @var string
   */
  protected $_surferId = NULL;

  /**
   * Id of elected surfer gallery folder
   * @var integer
   */
  protected $_galleryFolderId = NULL;

  /**
   * Community connector
   * @var connector_surfers
   */
  protected $_communityConnector = NULL;

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
      $surferId = $this->surferId();
      $definitionValues = array('acommunity_surfer_gallery', $surferId);
      if (!empty($surferId)) {
        $command = isset($this->params['command']) ? $this->params['command'] : NULL;
        if ($command != 'delete_folder' && !empty($this->params['folder_id'])) {
          $folder = $this->params['folder_id'];
        } else {
          $folder = 'base';
        }
        include_once(dirname(__FILE__).'/../../Cache/Identifier/Values.php');
        $values = new ACommunityCacheIdentifierValues();
        $definitionValues[] = $folder;
        $definitionValues[] = $values->lastChangeTime(
          'surfer_gallery_images:folder_'.$folder.':surfer_'.$surferId
        );
      }
      $this->_cacheDefinition = new PapayaCacheIdentifierDefinitionGroup(
        new PapayaCacheIdentifierDefinitionValues($definitionValues),
        new PapayaCacheIdentifierDefinitionParameters(
          array('mode', 'idx', 'img'), $this->paramName
        )
      );
    }
    return $this->_cacheDefinition;
  }

  /**
   * Get/set surfer gallery folders data
   *
   * @param ACommunitySurferGalleryData $data
   * @return ACommunitySurferGalleryData
   */
  public function data(ACommunitySurferGalleryData $data = NULL) {
    if (isset($data)) {
      $this->_data = $data;
    } elseif (is_null($this->_data)) {
      include_once(dirname(__FILE__).'/Data.php');
      $this->_data = new ACommunitySurferGalleryData();
      $this->_data->papaya($this->papaya());
      $this->_data->owner = $this;
    }
    return $this->_data;
  }

  /**
   * Overwrite get web link method to get surfer_handle parameter in links
   */
  public function getWebLink(
                    $pageId = NULL, $lng = NULL, $mode = NULL, $params = NULL,
                    $paramName = NULL, $text = '', $categId = NULL
                  ) {
    if (isset($this->params['surfer_handle'])) {
      $params = empty($params) ? array('surfer_handle' => $this->params['surfer_handle']) :
        array_merge($params, array('surfer_handle' => $this->params['surfer_handle']));
    }
    $command = isset($this->params['command']) ? $this->params['command'] : NULL;
    if ($command != 'delete_folder' && isset($this->params['folder_id'])) {
      $params = empty($params) ? array('folder_id' => $this->params['folder_id']) :
        array_merge($params, array('folder_id' => $this->params['folder_id']));
    }
    return parent::getWebLink($pageId, $lng, $mode, $params, $paramName, $text, $categId);
  }

  /**
   * Callback method for comments box to get current image id as ressource id
   *
   * @return string
   */
  public function callbackGetCurrentImageId() {
    $this->setDefaultData();
    $this->initializeParams();
    if (isset($this->params['mode']) && isset($this->params['img']) &&
        $this->params['mode'] == 'max' && $this->params['img'] >= 0) {
      $mediaDB = &base_mediadb::getInstance();
      if (isset($this->params['idx']) && (int)$this->params['idx'] >= $this->data['maxperpage']) {
        $min = (floor($this->params['idx'] / $this->data['maxperpage']) * $this->data['maxperpage']);
      } else {
        $min = 0;
      }
      $files = $mediaDB->getFiles(
        $this->_galleryFolderId(),
        $this->data['maxperpage'],
        $min,
        isset($this->data['order']) ? $this->data['order'] : 'name',
        isset($this->data['sort']) ? $this->data['sort'] : 'asc'
      );
      $files = array_values($files);
      if (isset($files[$this->params['img']])) {
        return $files[$this->params['img']]['file_id'];
      }
    }
    return NULL;
  }

  /**
   * Get id of current selected surfer
   *
   * @return string
   */
  public function surferId() {
    if (is_null($this->_surferId)) {
      if (isset($this->params['surfer_handle'])) {
        $surferId = $this->communityConnector()->getIdByHandle($this->params['surfer_handle']);
      }
      if (!empty($surferId)) {
        $this->_surferId = $surferId;
      } elseif ($this->papaya()->surfer->isValid && !empty($this->papaya()->surfer->surfer['surfer_id'])) {
        $this->_surferId = $this->papaya()->surfer->surfer['surfer_id'];
      }
    }
    return $this->_surferId;
  }

  /**
   * Get id of current selected gallery folder
   *
   * @return string
   */
  protected function _galleryFolderId() {
    if (is_null($this->_galleryFolderId)) {
      $surferId = $this->surferId();
      $filter = array('surfer_id' => $surferId);
      $command = isset($this->params['command']) ? $this->params['command'] : NULL;
      if ($command != 'delete_folder' && !empty($this->params['folder_id'])) {
        $filter['folder_id'] = $this->params['folder_id'];
      } else {
        $filter['parent_folder_id'] = 0;
      }
      $this->data()->galleries()->load($filter, 1);
      if (empty($this->params['folder_id']) && count($this->data()->galleries()) == 0) {
        $languageId = $this->papaya()->request->languageId;
        $parentFolder = $this->data()->mediaDBEdit()->getFolder($this->data['directory']);
        if (!empty($parentFolder[$languageId])) {
          $newFolderId = $this->data()->mediaDBEdit()->addFolder(
            $parentFolder[$languageId]['folder_id'],
            $parentFolder[$languageId]['parent_path'].$parentFolder[$languageId]['folder_id'].';',
            $parentFolder[$languageId]['permission_mode']
          );
          if (!empty($newFolderId)) {
            $this->data()->mediaDBEdit()->addFolderTranslation(
              $newFolderId, $languageId, $surferId
            );
            $gallery = $this->data()->gallery();
            $gallery['surfer_id'] = $surferId;
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
      } elseif (count($this->data()->galleries()) > 0) {
        $gallery = reset($this->data()->galleries()->toArray());
        $this->_galleryFolderId = $gallery['folder_id'];
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
    $surferId = $this->surferId();
    if (!empty($surferId)) {
      $this->data['directory'] = $this->_galleryFolderId();
    }
    return parent::getParsedData();
  }

  /**
   * Get/set community connector
   *
   * @param object $connector
   * @return object
   */
  public function communityConnector(connector_surfers $connector = NULL) {
    if (isset($connector)) {
      $this->_communityConnector = $connector;
    } elseif (is_null($this->_communityConnector)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
      $this->_communityConnector = base_pluginloader::getPluginInstance(
        '06648c9c955e1a0e06a7bd381748c4e4', $this
      );
    }
    return $this->_communityConnector;
  }
}