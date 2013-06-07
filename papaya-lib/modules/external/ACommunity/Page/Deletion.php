<?php
/**
 * Advanced community page deletion
 *
 * This class offers methods to delete and modify community data on page deletion
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
 * Advanced community page deletion
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityPageDeletion extends PapayaObject {

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
   * Table name of media db files
   * @var string
   */
  protected $_tableMediaDBFiles = PAPAYA_DB_TBL_MEDIADB_FILES;

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
   * Delete all page comments by page ids
   *
   * @param array $pageIds
   */
  public function deletePageComments($pageIds) {
    $this->databaseAccess()->deleteRecord(
      $this->databaseAccess()->getTableName($this->_tableNameComments),
      array('comment_ressource_id' => $pageIds, 'comment_ressource_type' => 'page')
    );
  }

  /**
   * Delete all thumbnail link files of page comments.
   * It is enough to delete the files only, because these entries do not have translations.
   *
   * @param integer $folderId media db folder by text options from connector
   * @param array $pageIds
   */
  public function deletePageCommentsThumbnailLinkFiles($folderId, $pageIds) {
    foreach ($pageIds as $pageId) {
      $sql = "DELETE FROM %s WHERE folder_id = '%d' AND file_name LIKE '%%%s'";
      $parameters = array($this->_tableMediaDBFiles, $folderId, ':comments:page_'.$pageId);
      $this->databaseAccess()->queryFmtWrite($sql, $parameters);
    }
  }
}