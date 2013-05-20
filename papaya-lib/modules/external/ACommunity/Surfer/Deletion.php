<?php
/**
 * Advanced community surfer deletion
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
 * Advanced community surfer deletion
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunitySurferDeletion extends PapayaObject {

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
   */
  public function deleteSurferGalleries($surferId) {
    include_once(dirname(__FILE__).'/Gallery/Deletion.php');
    $deletion = new ACommunitySurferGalleryDeletion();
    $deletion->papaya($this->papaya());
    $deletion->deleteSurferGalleries($surferId);
  }

  /**
   * Set deleted surfer flag in comments for comments filter
   *
   * @param string $surferId
   */
  public function deleteSurferComments($surferId) {
    $this->databaseAccess()->deleteRecord(
      $this->databaseAccess()->getTableName($this->_tableNameComments),
      array('comment_ressource_id' => $surferId, 'comment_ressource_type' => 'surfer')
    );
  }

  /**
   * Set deleted surfer flag in comments for comments filter
   *
   * @param string $surferId
   */
  public function setDeletedSurferInPageComments($surferId) {
    $this->databaseAccess()->updateRecord(
      $this->databaseAccess()->getTableName($this->_tableNameComments),
      array('comment_deleted_surfer' => 1),
      array('surfer_id' => $surferId, 'comment_ressource_type' => 'page')
    );
  }

  /**
   * Delete messages by surfer
   *
   * @param string $surferId
   */
  public function deleteMessages($surferId) {
    include_once(dirname(__FILE__).'/../Messages/Deletion.php');
    $deletion = new ACommunityMessagesDeletion();
    $deletion->papaya($this->papaya());
    $deletion->deleteMessages($surferId);
  }

}