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
   * Table name of last changes
   * @var string
   */
  protected $_tableNameLastChanges = 'acommunity_last_changes';

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
   * Delete all page comments last changes by page ids
   *
   * @param array $pageIds
   */
  public function deletePageCommentsLastChanges($pageIds) {
    foreach ($pageIds as $pageId) {
      $this->databaseAccess()->deleteRecord(
        $this->databaseAccess()->getTableName($this->_tableNameLastChanges),
        array('ressource' => 'comments:page_'.$pageId)
      );
    }
  }
}