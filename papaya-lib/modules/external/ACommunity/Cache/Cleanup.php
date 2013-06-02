<?php
/**
 * Advanced community cache cleanup
 *
 * Delete cache files and last change timestamps older than n days
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
 * Advanced community cache cleanup
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityCacheCleanup extends PapayaObject {

  /**
  * Stored database access object
  * @var PapayaDatabaseAccess
  */
  protected $_databaseAccess = NULL;

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
   * Delete cache files
   *
   * @param integer $daysTimeframe
   */
  public function deleteCacheFiles($daysTimeframe) {
    $timeframeMinTime = time() - ($daysTimeframe * 86400);
    $directory = new PapayaFileSystemDirectory(PAPAYA_PATH_CACHE);
    $typeDirectories = $directory->getEntries(
      '(^[^.])', PapayaFileSystemDirectory::FETCH_DIRECTORIES
    );
    foreach ($typeDirectories as $typeDirectory) {
      $directory = new PapayaFileSystemDirectory($typeDirectory->getPathname());
      $sectionDirectories = $directory->getEntries(
        '(^[^.])', PapayaFileSystemDirectory::FETCH_DIRECTORIES
      );
      foreach ($sectionDirectories as $sectionDirectory) {
        $directory = new PapayaFileSystemDirectory($sectionDirectory->getPathname());
        $files = $directory->getEntries(
          '', PapayaFileSystemDirectory::FETCH_FILES
        );
        foreach ($files as $file) {
          if ($file->getCTime() < $timeframeMinTime) {
            $pathName = $file->getPathname();
            unlink($pathName);
          }
        }
      }
    }
  }

  /**
   * Delete last change timestamps
   *
   * @param integer $daysTimeframe
   */
  public function deleteLastChangeTimestamps($daysTimeframe) {
    $timeframeMinTime = time() - ($daysTimeframe * 86400);
    $sql = "DELETE FROM %s WHERE change_time < '%d'";
    $params = array(
      $this->databaseAccess()->getTableName($this->_tableNameLastChanges), $timeframeMinTime
    );
    $this->databaseAccess()->queryFmtWrite($sql, $params);
  }
}