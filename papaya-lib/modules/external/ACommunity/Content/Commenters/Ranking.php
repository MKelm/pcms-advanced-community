<?php
/**
 * Advanced community commenters ranking database records
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
 * Advanced community commenters ranking database records
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityContentCommentersRanking extends PapayaDatabaseRecords {

  /**
   * Map field names to more convinient property names
   *
   * @var array(string=>string)
   */
  protected $_fields = array(
	  'surfer_id' => 'surfer_id',
    'comments_amount' => 'comments_amount',
    'deleted_surfer' => 'comment_deleted_surfer'
  );

  /**
   * Table containing books
   *
   * @var string
   */
  protected $_tableName = 'acommunity_comments';

  /**
   * An array of properties, used to compile the identifer
   *
   * @var array(string)
   */
  protected $_identifierProperties = array('surfer_id');

  /**
   * Order by properties
   * @var array
   */
  protected $_orderByProperties = array(
    'comments_amount' => PapayaDatabaseInterfaceOrder::DESCENDING
  );

  /**
  * Load pages defined by filter conditions.
  *
  * @param array $filter
  * @param NULL|integer $limit
  * @param NULL|integer $offset
  */
  public function load(array $filter, $limit = NULL, $offset = NULL) {
    $databaseAccess = $this->getDatabaseAccess();
    $sql = "SELECT surfer_id, COUNT(surfer_id) AS comments_amount, comment_deleted_surfer
              FROM %s
                   ".$this->_compileCondition($filter)."
              GROUP BY surfer_id
                   ".$this->_compileOrderBy();
    $parameters = array(
      $databaseAccess->getTableName($this->_tableName)
    );
    return $this->_loadRecords($sql, $parameters, $limit, $offset, $this->_identifierProperties);
  }
}