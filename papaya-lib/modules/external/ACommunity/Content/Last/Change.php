<?php
/**
 * Advanced community last change database record
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
 * @subpackage External-Guestbook
 */

/**
 * Advanced community last change database record
 *
 * @package Papaya-Modules
 * @subpackage External-Guestbook
 */
class ACommunityContentLastChange extends PapayaDatabaseRecord {

  /**
   * Map field names to more convinient property names
   *
   * @var array(string=>string)
   */
  protected $_fields = array(
    'ressource' => 'change_ressource',
    'time' => 'change_time'
  );

  /**
   * Table containing book
   *
   * @var string
   */
  protected $_tableName = 'acommunity_last_changes';

  /**
  * Create a standard autoincrement key object for the property "id".
  *
  * @return PapayaDatabaseRecordKeyFields
  */
  protected function _createKey() {
    return new PapayaDatabaseRecordKeyFields(
      $this, $this->_tableName, array('ressource')
    );
  }
}