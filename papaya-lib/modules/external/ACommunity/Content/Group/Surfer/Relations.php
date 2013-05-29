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
    'count' => 'count',
    'surfer_id' => 'current_surfer_id',
    'is_owner' => 'is_owner',
    'is_member' => 'is_member',
    'is_pending' => 'is_pending'
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
  * surfer_status pending values:
  * -> 0 membership accepted
  * -> 1 pending membership request
  * -> 2 pending membership invitation
  *
  * @param array $filter
  * @param NULL|integer $limit
  * @param NULL|integer $offset
  */
  public function load(array $filter, $limit = NULL, $offset = NULL) {
    if (isset($filter['count'])) {
      $fields = " g.group_id, COUNT(g.group_id) AS count,
                  (CASE WHEN gs.surfer_id IS NULL THEN g.group_owner ELSE gs.surfer_id END)
                   AS current_surfer_id ";
    } else {
      $fields = "g.group_id,
                 (CASE WHEN gs.surfer_id IS NULL THEN 1 ELSE 0 END) AS is_owner,
                 (CASE WHEN gs.surfer_id IS NOT NULL AND gs.surfer_status_pending = 0 THEN 1 ELSE 0 END)
                   AS is_member,
                 (CASE WHEN gs.surfer_id IS NOT NULL AND gs.surfer_status_pending > 0
                   THEN gs.surfer_status_pending ELSE 0 END) AS is_pending,
                 (CASE WHEN gs.surfer_id IS NULL THEN g.group_owner ELSE gs.surfer_id END)
                   AS current_surfer_id ";
    }
    unset($filter['count']);
    if (isset($filter['surfer_id'])) {
      $joinCondition = sprintf(
        " ON (gs.group_id = g.group_id AND gs.surfer_id = '%s') ", $filter['surfer_id']
      );
    } else {
      $joinCondition = " USING (group_id) ";
    }
    $databaseAccess = $this->getDatabaseAccess();
    $sql = "SELECT $fields
              FROM %s AS g
         LEFT JOIN %s AS gs $joinCondition
          ".$this->_compileCondition($filter).
          " ORDER BY g.group_title ASC";

    $parameters = array(
      $databaseAccess->getTableName($this->_tableNameGroups),
      $databaseAccess->getTableName($this->_tableNameGroupSurfers)
    );
    return $this->_loadRecords($sql, $parameters, $limit, $offset, 'id');
  }

  /**
  * Compile a sql condition specified by the filter. Prefix it, if it is not empty.
  *
  * @param scalar|array $filter
  * @param string $prefix
  * @return string
  */
  protected function _compileCondition($filter, $prefix = " WHERE ") {
    $result = '';
    if (isset($filter['surfer_id']) && isset($filter['surfer_id'])) {
      $result .= sprintf(
        "%s (g.group_owner = '%s' OR gs.surfer_id = '%s')",
        $prefix,
        $filter['surfer_id'],
        $filter['surfer_id']
      );
      unset($filter['surfer_id']);
      $prefix = " AND ";
    }
    if (isset($filter['group_id'])) {
      $result .= sprintf("%s g.group_id = '%d'" , $prefix, $filter['group_id']);
      unset($filter['group_id']);
      $prefix = " AND ";
    }
    if (isset($filter['surfer_status_pending'])) {
      $result .= sprintf(
        "%s gs.surfer_status_pending = '%d' ", $prefix, $filter['surfer_status_pending']
      );
      unset($filter['surfer_status_pending']);
      $prefix = " AND ";
    }
    $result .= parent::_compileCondition($filter, $prefix);
    return $result;
  }
}