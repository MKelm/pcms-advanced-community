<?php
/**
 * Advanced community messages database records
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
 * Advanced community messages database records
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityContentMessages extends PapayaDatabaseRecords {

  /**
   * Map field names to more convinient property names
   *
   * @var array(string=>string)
   */
  protected $_fields = array(
	  'id' => 'message_id',
    'sender' => 'message_sender',
    'recipient' => 'message_recipient',
    'time' => 'message_time',
    'text' => 'message_text'
  );

  /**
   * Table containing books
   *
   * @var string
   */
  protected $_tableName = 'acommunity_messages';

  /**
   * An array of properties, used to compile the identifer
   *
   * @var array(string)
   */
  protected $_identifierProperties = array('id');

  /**
   * Order by properties
   * @var array
   */
  protected $_orderByProperties = array(
    'time' => PapayaDatabaseInterfaceOrder::DESCENDING
  );

  /**
  * Compile a sql condition specified by the filter. Prefix it, if it is not empty.
  *
  * @param scalar|array $filter
  * @param string $prefix
  * @return string
  */
  protected function _compileCondition($filter, $prefix = " WHERE ") {
    $result = '';
    if (isset($filter['current_surfer_id']) && isset($filter['selected_surfer_id'])) {
      $result = sprintf(
        "%s ((message_sender = '%s' AND message_recipient = '%s') OR
             (message_sender = '%s' AND message_recipient = '%s'))",
        $prefix,
        $filter['current_surfer_id'],
        $filter['selected_surfer_id'],
        $filter['selected_surfer_id'],
        $filter['current_surfer_id']
      );
      unset($filter['current_surfer_id']);
      unset($filter['selected_surfer_id']);
    }
    $result .= parent::_compileCondition($filter, empty($result) ? $prefix : " AND ");
    return $result;
  }

}