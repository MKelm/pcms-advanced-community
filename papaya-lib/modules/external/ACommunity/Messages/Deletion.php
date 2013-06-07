<?php
/**
 * Advanced community messages deletion
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
 * Advanced community messages deletion
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityMessagesDeletion extends PapayaObject {

  /**
  * Stored database access object
  * @var PapayaDatabaseAccess
  */
  protected $_databaseAccess = NULL;

  /**
   * Table name of messages
   * @var string
   */
  protected $_tableNameMessages = 'acommunity_messages';

  /**
   * Table name of message surfers
   * @var string
   */
  protected $_tableNameMessageSurfers = 'acommunity_message_surfers';

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
   * Delete messages by surfer
   *
   * @param string $surferId
   */
  public function deleteMessages($surferId) {
    $this->databaseAccess()->deleteRecord(
      $this->databaseAccess()->getTableName($this->_tableNameMessages),
      array('message_recipient' => $surferId)
    );
    $this->databaseAccess()->deleteRecord(
      $this->databaseAccess()->getTableName($this->_tableNameMessages),
      array('message_sender' => $surferId)
    );
    $this->databaseAccess()->deleteRecord(
      $this->databaseAccess()->getTableName($this->_tableNameMessageSurfers),
      array('surfer_id' => $surferId)
    );
    $this->databaseAccess()->deleteRecord(
      $this->databaseAccess()->getTableName($this->_tableNameMessageSurfers),
      array('contact_surfer_id' => $surferId)
    );
  }

  /**
   * Delete all thumbnail link files of surfer's messages.
   * It is enough to delete the files only, because these entries do not have translations.
   *
   * @param integer $folderId media db folder by text options from connector
   * @param string $surferId
   */
  public function deleteMessagesThumbnailLinkFiles($folderId, $surferId) {
    // delete files by message sender
    $sql = "DELETE FROM %s WHERE folder_id = '%d' AND file_name LIKE '%%%s'";
    $parameters = array($this->_tableMediaDBFiles, $folderId, ':messages:surfer_'.$surferId);
    $this->databaseAccess()->queryFmtWrite($sql, $parameters);
    // delete files by message recipient
    $sql = "DELETE FROM %s WHERE folder_id = '%d' AND file_name LIKE '%%%s%%%s'";
    $parameters = array(
      $this->_tableMediaDBFiles, $folderId, ':messages:surfer_', ':surfer_'.$surferId
    );
    $this->databaseAccess()->queryFmtWrite($sql, $parameters);
  }
}