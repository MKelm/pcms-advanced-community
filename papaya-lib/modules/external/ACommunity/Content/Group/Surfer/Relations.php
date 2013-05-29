<?php
/**
 * Advanced community group surfer relation database record
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
 * Advanced community group surfer relation database record
 *
 * @package Papaya-Modules
 * @subpackage External-Guestbook
 */
class ACommunityContentGroupSurferRelations extends PapayaDatabaseRecords {

  /**
   * Map field names to more convinient property names
   *
   * @var array(string=>string)
   */
  protected $_fields = array(
    'id' => 'group_id',
    'is_owner' => 'group_owner',
    'is_member' => 'surfer_id'
  );

  /**
   * Table containing message surfers
   *
   * @var string
   */
  protected $_tableNameGroupSurfers = 'acommunity_group_surfers';

  /**
   * Table containing messages
   *
   * @var string
   */
  protected $_tableNameGroups = 'acommunity_groups';

  /**
   * An array of properties, used to compile the identifer
   *
   * @var array(string)
   */
  protected $_identifierProperties = array('id');

  /**
  * Load pages defined by filter conditions.
  *
  * @param array $filter
  * @param NULL|integer $limit
  * @param NULL|integer $offset
  */
  public function load(array $filter, $limit = NULL, $offset = NULL) {
    if (!isset($filter['surfer_id'])) {
      return FALSE;
    }
    if (isset($filter['group_id'])) {
      $groupCondition = sprintf(" AND g.group_id = '%d' ", $filter['group_id']);
    } else {
      $groupCondition = '';
    }
    $databaseAccess = $this->getDatabaseAccess();
    $sql = "SELECT g.group_id,
                   (CASE WHEN (gs.surfer_id IS NULL) THEN 1 ELSE 0 END) AS group_owner,
                   (CASE WHEN (gs.surfer_id IS NULL) THEN 0 ELSE 1 END) AS surfer_id
              FROM %s AS g
         LEFT JOIN %s AS gs USING (group_id)
             WHERE (g.group_owner = '%s' OR gs.surfer_id = '%s')$groupCondition
             ORDER BY g.group_title ASC";

    $parameters = array(
      $databaseAccess->getTableName($this->_tableNameGroups),
      $databaseAccess->getTableName($this->_tableNameGroupSurfers),
      $filter['surfer_id'],
      $filter['surfer_id']
    );
    return $this->_loadRecords($sql, $parameters, $limit, $offset, 'id');
  }
}