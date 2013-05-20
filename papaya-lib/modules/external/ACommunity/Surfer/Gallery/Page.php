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
class ACommunitySurferGalleryPage extends content_thumbs {
  
  /**
   * Use a advanced community parameter group name
   * @var string
   */
  public $paramName = 'acg';
  
  /**
   * Community connector
   * @var connector_surfers
   */
  protected $_communityConnector = NULL;
  
  /**
   * Surfer gallery database record
   * @var object
   */
  protected $_gallery = NULL;
  
  /**
   * Surfer galleries database records
   * @var object
   */
  protected $_galleries = NULL;
  
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
        $this->data['directory'],
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
  
  /**
   * Overwrite get parsed data to load surfer gallery
   * 
   * @return string
   */
  public function getParsedData() {
    $this->setDefaultData();
    $this->initializeParams();
    
    // load media db folder depending on surfer handle
    if (isset($this->params['surfer_handle'])) {
      $surferId = $this->communityConnector()->getIdByHandle($this->params['surfer_handle']);
      if (!empty($surferId)) {
        $this->galleries()->load(
          array('surfer_id' => $surferId, 'parent_folder_id' => $this->data['directory']), 0, 1
        );
        if (count($this->galleries()) == 0) {
          include_once(PAPAYA_INCLUDE_PATH.'system/base_mediadb_edit.php');
          $mediaDBEdit = new base_mediadb_edit();
          $languageId = $this->papaya()->request->languageId;
          $parentFolder = $mediaDBEdit->getFolder($this->data['directory']);
          if (!empty($parentFolder[$languageId])) {
            $newFolderId = $mediaDBEdit->addFolder(
              $parentFolder[$languageId]['folder_id'], 
              $parentFolder[$languageId]['parent_path'].$parentFolder[$languageId]['folder_id'].';', 
              $parentFolder[$languageId]['permission_mode']
            );
            if (!empty($newFolderId)) {
              $mediaDBEdit->addFolderTranslation(
                $newFolderId, $this->papaya()->request->languageId, $surferId
              );
              $gallery = $this->gallery();
              $gallery['surfer_id'] = $surferId;
              $gallery['folder_id'] = $newFolderId;
              $gallery['parent_folder_id'] = $this->data['directory'];
              $gallery->save();
              $this->data['directory'] = $newFolderId;
            }
          }
        } else {
          $galleries = $this->galleries();
          $galleryKeys = array_keys($galleries->toArray());
          $this->data['directory'] = $galleries[$galleryKeys[0]]['folder_id'];
        }
      }
    }
    
    return parent::getParsedData();
  }
  
  /**
  * Access to surfer gallery database record data
  *
  * @param ACommunityContentSurferGallery $comment
  * @return ACommunityContentSurferGallery
  */
  public function gallery(ACommunityContentSurferGallery $gallery = NULL) {
    if (isset($gallery)) {
      $this->_gallery = $gallery;
    } elseif (is_null($this->_gallery)) {
      include_once(dirname(__FILE__).'/../../Content/Surfer/Gallery.php');
      $this->_gallery = new ACommunityContentSurferGallery();
      $this->_gallery->papaya($this->papaya());
    }
    return $this->_gallery;
  }
  
  /**
  * Access to the surfer galleries database records data
  *
  * @param ACommunityContentSurferGalleries $comments
  * @return ACommunityContentSurferGalleries
  */
  public function galleries(ACommunityContentSurferGalleries $galleries = NULL) {
    if (isset($galleries)) {
      $this->_galleries = $galleries;
    } elseif (is_null($this->_galleries)) {
      include_once(dirname(__FILE__).'/../../Content/Surfer/Galleries.php');
      $this->_galleries = new ACommunityContentSurferGalleries();
      $this->_galleries->papaya($this->papaya());
    }
    return $this->_galleries;
  }
  
}
