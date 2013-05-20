<?php
/**
 * Advanced community surfer gallery deletion
 *
 * This class offers methods to delete and modify community data on surfer deletion
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
 * Advanced community surfer gallery deletion
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunitySurferGalleryDeletion extends PapayaObject {

  /**
  * Stored database access object
  * @var PapayaDatabaseAccess
  */
  protected $_databaseAccess = NULL;

  /**
   * Table name of comments
   * @var string
   */
  protected $_tableNameComments = 'acommunity_comments';

  /**
   * Table name of surfer galleries
   * @var string
   */
  protected $_tableNameSurferGalleries = 'acommunity_surfer_galleries';

  /**
   * Media db edit object
   * @var object
   */
  protected $_mediaDBEdit = NULL;

  /**
   * Surfer galleries database records
   * @var object
   */
  protected $_surferGalleries = NULL;

  /**
  * Set/get database access object
  *
  * @return PapayaDatabaseAccess
  */
  public function databaseAccess(PapayaDatabaseAccess $databaseAccess = NULL) {
    if (isset($databaseAccess)) {
      $this->_databaseAccess = $databaseAccess;
    } elseif (is_null($this->_databaseAccess)) {
      $this->_databaseAccess = $this->papaya()->database->createDatabaseAccess($this);
    }
    return $this->_databaseAccess;
  }

  /**
   * Delete all surfer galleries and related data by one surfer id
   *
   * @param string $surferId
   * @param boolean $result
   */
  public function deleteSurferGalleries($surferId) {
    $this->surferGalleries()->load(array('surfer_id' => $surferId));
    $surferGalleries = $this->surferGalleries()->toArray();
    $result = TRUE;
    if (!empty($surferGalleries)) {
      foreach ($surferGalleries as $surferGallery) {
        $this->_deleteMediaDBFolder($surferGallery['folder_id']);
      }
      $result = $result && $this->databaseAccess()->deleteRecord(
        $this->databaseAccess()->getTableName($this->_tableNameSurferGalleries),
        array('surfer_id' => $surferId)
      );
    }
    return $result;
  }

  /**
   * Delete one surfer gallery by folder id
   *
   * @param integer $folderId
   * @param boolean $result
   */
  public function deleteSurferGalleryByFolderId($folderId) {
    $this->surferGalleries()->load(array('folder_id' => $folderId));
    $surferGallery = reset($this->surferGalleries()->toArray());
    $result = 0;
    if (!empty($surferGallery)) {
      if ($this->_deleteMediaDBFolder($surferGallery['folder_id'])) {
        $result = $this->databaseAccess()->deleteRecord(
          $this->databaseAccess()->getTableName($this->_tableNameSurferGalleries),
          array('gallery_folder_id' => $surferGallery['folder_id'])
        );
      }
    }
    return (boolean)$result;
  }

  /**
   * Delete one media db folder, all related files and comments
   *
   * @param integer $folderId
   */
  protected function _deleteMediaDBFolder($folderId) {
    $files = $this->mediaDBEdit()->getFiles($folderId);
    if (!empty($files)) {
      foreach ($files as $file) {
        $this->mediaDBEdit()->deleteFile($file['file_id']);
        $this->databaseAccess()->deleteRecord(
          $this->databaseAccess()->getTableName($this->_tableNameComments),
          array(
            'comment_ressource_type' => 'image',
            'comment_ressource_id' => $file['file_id']
          )
        );
      }
    }
    return $this->mediaDBEdit()->deleteFolder($folderId);
  }

  /**
   * Media DB Edit to delete related data
   *
   * @param base_mediadb_edit $mediaDBEdit
   * @return base_mediadb_edit
   */
  public function mediaDBEdit(base_mediadb_edit $mediaDBEdit = NULL) {
    if (isset($mediaDBEdit)) {
      $this->_mediaDBEdit = $mediaDBEdit;
    } elseif (is_null($this->_mediaDBEdit)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_mediadb_edit.php');
      $this->_mediaDBEdit = new base_mediadb_edit();
    }
    return $this->_mediaDBEdit;
  }

  /**
  * Access to the surfer galleries database records data
  *
  * @param ACommunityContentSurferGalleries $comments
  * @return ACommunityContentSurferGalleries
  */
  public function surferGalleries(ACommunityContentSurferGalleries $galleries = NULL) {
    if (isset($galleries)) {
      $this->_surferGalleries = $galleries;
    } elseif (is_null($this->_surferGalleries)) {
      include_once(dirname(__FILE__).'/../Content/Surfer/Galleries.php');
      $this->_surferGalleries = new ACommunityContentSurferGalleries();
      $this->_surferGalleries->papaya($this->papaya());
    }
    return $this->_surferGalleries;
  }
}