<?php
/**
 * Advanced community notification settings database records
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
 * Advanced community notification settings database records
 *
 * @package Papaya-Modules
 * @subpackage External-Guestbook
 */
class ACommunityContentNotificationSettings extends PapayaDatabaseRecords {

  /**
   * Map field names to more convinient property names
   *
   * @var array(string=>string)
   */
  protected $_fields = array(
    'surfer_id' => 'ns.surfer_id',
    'language_id' => 'nt.language_id',
    'by_message' => 'ns.notification_by_message',
    'by_email' => 'ns.notification_by_email',
    'notification_id' => 'n.notification_id',
    'notification_name' => 'n.notification_name',
    'notification_title' => 'nt.notification_title'
  );

  /**
   * Table containing base data
   *
   * @var string
   */
  protected $_tableNameNotifications = 'acommunity_notifications';

  /**
   * Table containing settings
   *
   * @var string
   */
  protected $_tableNameSettings = 'acommunity_notification_settings';

  /**
   * Table containing translations
   *
   * @var string
   */
  protected $_tableNameTranslations = 'acommunity_notification_trans';

  /**
   * An array of properties, used to compile the identifer
   *
   * @var array(string)
   */
  protected $_identifierProperties = array('notification_id');

  /**
   * Order by properties
   * @var array
   */
  protected $_orderByProperties = array(
    'notification_title' => PapayaDatabaseInterfaceOrder::DESCENDING
  );

  /**
  * Load pages defined by filter conditions.
  *
  * @param array $filter
  */
  public function load(array $filter, $limit = NULL, $offset = NULL) {
    if (isset($filter['surfer_id']) && isset($filter['language_id'])) {
      $fields = implode(', ', $this->mapping()->getFields());
      $sql = "SELECT $fields
                FROM %s AS n
                LEFT JOIN %s AS ns
                  ON ns.notification_id = n.notification_id AND ns.surfer_id = '%s'
                LEFT JOIN %s AS nt
                  ON nt.notification_id = n.notification_id AND nt.language_id = '%d'
                     ".$this->_compileOrderBy();
      $parameters = array(
        $this->getDatabaseAccess()->getTableName($this->_tableNameNotifications),
        $this->getDatabaseAccess()->getTableName($this->_tableNameSettings),
        $filter['surfer_id'],
        $this->getDatabaseAccess()->getTableName($this->_tableNameTranslations),
        $filter['language_id']
      );
      return $this->_loadRecords($sql, $parameters, $limit, $offset, $this->_identifierProperties);
    }
    return FALSE;
  }
}