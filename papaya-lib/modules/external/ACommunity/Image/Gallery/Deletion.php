<?php
/**
 * Advanced community image gallery deletion
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
 * Advanced community image gallery deletion
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityImageGalleryDeletion extends PapayaObject {

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
   * Table name of galleries
   * @var string
   */
  protected $_tableNameGalleries = 'acommunity_galleries';

  /**
   * Table name of media db files
   * @var string
   */
  protected $_tableMediaDBFiles = PAPAYA_DB_TBL_MEDIADB_FILES;

  /**
   * Media db edit object
   * @var object
   */
  protected $_mediaDBEdit = NULL;

  /**
   * Image galleries database records
   * @var object
   */
  protected $_imageGalleries = NULL;

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
   * Delete all galleries and related data by ressource
   *
   * @param string $ressourceType
   * @param mixed $ressourceId
   * @param integer $imageCommentsThumbnailLinksFolder
   * @param boolean $result
   */
  public function deleteGalleries($ressourceType, $ressourceId, $imageCommentsThumbnailLinksFolder) {
    $this->imageGalleries()->load(
      array('ressource_type' => $ressourceType, 'ressource_id' => $ressourceId)
    );
    $galleries = $this->imageGalleries()->toArray();
    $result = TRUE;
    if (!empty($galleries)) {
      foreach ($galleries as $gallery) {
        $this->_deleteMediaDBFolder($gallery['folder_id'], $imageCommentsThumbnailLinksFolder);
      }
      $result = $result && $this->databaseAccess()->deleteRecord(
        $this->databaseAccess()->getTableName($this->_tableNameGalleries),
        array('ressource_type' => $ressourceType, 'ressource_id' => $ressourceId)
      );
    }
    return $result;
  }

  /**
   * Delete one gallery by folder id
   *
   * @param integer $folderId
   * @param boolean $result
   */
  public function deleteGalleryByFolderId($folderId) {
    $this->imageGalleries()->load(array('folder_id' => $folderId));
    $gallery = reset($this->imageGalleries()->toArray());
    $result = FALSE;
    if (!empty($gallery)) {
      if ($this->_deleteMediaDBFolder($gallery['folder_id'], $imageCommentsThumbnailLinksFolder)) {
        $result = $this->databaseAccess()->deleteRecord(
          $this->databaseAccess()->getTableName($this->_tableNameGalleries),
          array('gallery_folder_id' => $gallery['folder_id'])
        );
      }
    }
    return $result;
  }

  /**
   * Delete one media db folder, all related files and comments
   *
   * @param integer $folderId
   * @param integer $imageCommentsThumbnailLinksFolder
   */
  protected function _deleteMediaDBFolder($folderId, $imageCommentsThumbnailLinksFolder) {
    // use a custom sql query to load file_id only
    // the getFiles method from the media db class has too much overhead
    $sql = "SELECT file_id FROM %s WHERE folder_id = '%d'";
    $parameters = array($this->_tableMediaDBFiles, $folderId);
    if ($result = $this->databaseAccess()->queryFmt($sql, $parameters)) {
      while ($fileId = $result->fetchField()) {
        $this->mediaDBEdit()->deleteFile($fileId); // delete file with translations/derivations
        $this->_deleteImageComments($fileId, $imageCommentsThumbnailLinksFolder);
      }
    }
    return $this->mediaDBEdit()->deleteFolder($folderId);
  }

  /**
   * Delete all image comments
   *
   * @param string $imageId
   * @param integer $thumbnailLinksFolder
   */
  protected function _deleteImageComments($imageId, $thumbnailLinksFolder) {
    $this->databaseAccess()->deleteRecord(
      $this->databaseAccess()->getTableName($this->_tableNameComments),
      array(
        'comment_ressource_type' => 'image',
        'comment_ressource_id' => $file['file_id']
      )
    );
    $this->_deleteImageCommentsThumbnailLinkFiles($thumbnailLinksFolder, $imageId);
  }

  /**
   * Delete all thumbnail link files of image comments.
   * It is enough to delete the files only, because these entries do not have translations.
   *
   * @param integer $folderId media db folder by text options from connector
   * @param string $imageFileId
   */
  protected function _deleteImageCommentsThumbnailLinkFiles($folderId, $imageFileId) {
    $sql = "DELETE FROM %s WHERE folder_id = '%d' AND file_name LIKE '%%%s'";
    $parameters = array($this->_tableMediaDBFiles, $folderId, ':comments:image'.$imageFileId);
    $this->databaseAccess()->queryFmtWrite($sql, $parameters);
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
  * Access to the image galleries database records data
  *
  * @param ACommunityContentImageGalleries $comments
  * @return ACommunityContentImageGalleries
  */
  public function imageGalleries(ACommunityContentImageGalleries $galleries = NULL) {
    if (isset($galleries)) {
      $this->_imageGalleries = $galleries;
    } elseif (is_null($this->_imageGalleries)) {
      include_once(dirname(__FILE__).'/../../Content/Image/Galleries.php');
      $this->_imageGalleries = new ACommunityContentImageGalleries();
      $this->_imageGalleries->papaya($this->papaya());
    }
    return $this->_imageGalleries;
  }
}