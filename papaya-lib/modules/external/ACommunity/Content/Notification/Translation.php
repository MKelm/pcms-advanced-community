<?php
/**
 * Advanced community notification translation database record
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
 * Advanced community notification translation database record
 *
 * @package Papaya-Modules
 * @subpackage External-Guestbook
 */
class ACommunityContentNotificationTranslation extends PapayaDatabaseRecord {

  /**
   * Map field names to more convinient property names
   *
   * @var array(string=>string)
   */
  protected $_fields = array(
	  'notification_id' => 'nt.notification_id',
    'name' => 'n.notification_name',
    'language_id' => 'nt.language_id',
    'title' => 'nt.notification_title',
    'text' => 'nt.notification_text'
  );

  /**
   * Table containing translation
   *
   * @var string
   */
  protected $_tableNameTranslation = 'acommunity_notification_trans';

  /**
   * Table containing base data
   *
   * @var string
   */
  protected $_tableNameNotifications = 'acommunity_notifications';

  /**
  * Load pages defined by filter conditions.
  *
  * @param array $filter
  */
  public function load($filter) {
    if ($filter instanceOf PapayaDatabaseConditionElement) {
      $condition = $filter->getSql();
    } else {
      if (!is_array($filter)) {
        $filter = array('id' => $filter);
      }
      $generator = new PapayaDatabaseConditionGenerator($this, $this->mapping());
      $condition = (string)$generator->fromArray($filter);
    }
    $fields = implode(', ', $this->mapping()->getFields());
    if (empty($condition)) {
      $sql = "SELECT $fields FROM %s AS n JOIN %s AS nt USING (notification_id)";
    } else {
      $sql = "SELECT $fields FROM %s AS n JOIN %s AS nt USING (notification_id) WHERE $condition";
    }
    $parameters = array(
      $this->getDatabaseAccess()->getTableName($this->_tableNameNotifications),
      $this->getDatabaseAccess()->getTableName($this->_tableNameTranslation)
    );
    return $this->_loadRecord($sql, $parameters);
  }
}