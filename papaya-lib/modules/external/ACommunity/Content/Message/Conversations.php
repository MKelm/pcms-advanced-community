<?php
/**
 * Advanced community message conversations database records
 *
 * Loads all message conversations by current surfer id with last message
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
 * Advanced community message conversations database records
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityContentMessageConversations extends PapayaDatabaseRecords {

  /**
   * Map field names to more convinient property names
   *
   * @var array(string=>string)
   */
  protected $_fields = array(
	  'id' => 'mm.message_id',
    'sender' => 'mm.message_sender',
    'recipient' => 'mm.message_recipient',
    'time' => 'mm.message_time',
    'text' => 'mm.message_text'
  );

  /**
   * Table containing message surfers
   *
   * @var string
   */
  protected $_tableNameMessageSurfers = 'acommunity_message_surfers';

  /**
   * Table containing messages
   *
   * @var string
   */
  protected $_tableNameMessages = 'acommunity_messages';

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
  * Load pages defined by filter conditions.
  *
  * @param array $filter
  * @param NULL|integer $limit
  * @param NULL|integer $offset
  */
  public function load(array $filter, $limit = NULL, $offset = NULL) {
    if (!isset($filter['current_surfer_id'])) {
      return FALSE;
    }
    $databaseAccess = $this->getDatabaseAccess();
    $sql = "SELECT mm.message_id, mm.message_sender, mm.message_recipient, mm.message_time, mm.message_text
              FROM %s AS mm
             WHERE (mm.message_sender = '%s' OR mm.message_recipient = '%s')
               AND mm.message_time = (
                     SELECT MAX(m.message_time)
                       FROM %s AS ms
                       JOIN %s AS m
                         ON ((m.message_sender = ms.surfer_id AND m.message_recipient = ms.contact_surfer_id)
                             OR (m.message_sender = ms.contact_surfer_id AND m.message_recipient = ms.surfer_id))
                      WHERE ms.surfer_id = '%s'
                            AND (ms.contact_surfer_id = mm.message_sender
                                 OR ms.contact_surfer_id = mm.message_recipient)
                      GROUP BY ms.surfer_id, ms.contact_surfer_id
                   )
                   ".$this->_compileOrderBy();

    $parameters = array(
      $databaseAccess->getTableName($this->_tableNameMessages),
      $filter['current_surfer_id'],
      $filter['current_surfer_id'],
      $databaseAccess->getTableName($this->_tableNameMessageSurfers),
      $databaseAccess->getTableName($this->_tableNameMessages),
      $filter['current_surfer_id']
    );
    return $this->_loadRecords($sql, $parameters, $limit, $offset, 'id');
  }
}