<?php
/**
 * Advanced community chache identifier values
 *
 * Offers helper methods to get values to define the cache identifier
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
 * Advanced community chache identifier values
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityCacheIdentifierValues extends PapayaObject {

  /**
  * Stored database access object
  * @var PapayaDatabaseAccess
  */
  protected $_databaseAccess = NULL;

  /**
   * Messages database records
   * @var object
   */
  protected $_messages = NULL;

  /**
   * Message conversations database records
   * @var object
   */
  protected $_messageConversations = NULL;

  /**
   * Table name of surfer
   * @var string
   */
  protected $_tableNameSurfer = 'surfer';

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
  * Access to the messages database records data
  *
  * @param ACommunityContentMessages $messages
  * @return ACommunityContentMessages
  */
  public function messages(ACommunityContentMessages $messages = NULL) {
    if (isset($messages)) {
      $this->_messages = $messages;
    } elseif (is_null($this->_messages)) {
      include_once(dirname(__FILE__).'/../../Content/Messages.php');
      $this->_messages = new ACommunityContentMessages();
      $this->_messages->papaya($this->papaya());
    }
    return $this->_messages;
  }

  /**
  * Access to the message conversations database records data
  *
  * @param ACommunityContentMessageConversations $conversations
  * @return ACommunityContentMessageConversations
  */
  public function messageConversations(ACommunityContentMessageSurfers $conversations = NULL) {
    if (isset($conversations)) {
      $this->_messageConversations = $conversations;
    } elseif (is_null($this->_messageConversations)) {
      include_once(dirname(__FILE__).'/../../Content/Message/Conversations.php');
      $this->_messageConversations = new ACommunityContentMessageConversations();
      $this->_messageConversations->papaya($this->papaya());
    }
    return $this->_messageConversations;
  }

  /**
   * Get last change time
   *
   * @param string $ressource
   * @return integer
   */
  public function lastChangeTime($ressource) {
    $sql = "SELECT change_time FROM %s WHERE change_ressource = '%s'";
    $values = array(
      $this->databaseAccess()->getTableName($this->_tableNameLastChanges),
      $ressource
    );
    if ($result = $this->databaseAccess()->queryFmt($sql, $values)) {
      $field = $result->fetchField();
    }
    return !empty($field) ? $field : 0;
  }

  /**
   * Get last message time
   *
   * @param string $currentSurferId
   * @param string $selectedSurfreId
   * @return integer
   */
  public function lastMessageTime($currentSurferId, $selectedSurferId) {
    $this->messages()->load(
      array('current_surfer_id' => $surferId, 'selected_surfer_id' => $selectedSurferId), 1
    );
    if (count($this->messages) > 0) {
      $result = reset($this->messages()->toArray());
      return (int)$result['time'];
    }
    return 0;
  }

  /**
   * Get last message conversation time
   *
   * @param string $surferId
   * @return integer
   */
  public function lastMessageConversationTime($surferId) {
    $this->messageConversations()->load(array('current_surfer_id' => $surferId), 1);
    if (count($this->messageConversations) > 0) {
      $result = reset($this->messageConversations()->toArray());
      return (int)$result['time'];
    }
    return 0;
  }

  /**
   * Get surfer last action time
   *
   * @return integer
   */
  public function surferLastActionTime() {
    $sql = "SELECT MAX(surfer_lastaction) AS time FROM %s";
    $values = array($this->databaseAccess()->getTableName($this->_tableNameSurfer));
    if ($result = $this->databaseAccess()->queryFmt($sql, $values)) {
      $field = $result->fetchField();
    }
    return !empty($field) ? $field : 0;
  }

  /**
   * Get surfer last registration time
   *
   * @return integer
   */
  public function surferLastRegistrationTime() {
    $sql = "SELECT MAX(surfer_registration) AS time FROM %s";
    $values = array($this->databaseAccess()->getTableName($this->_tableNameSurfer));
    if ($result = $this->databaseAccess()->queryFmt($sql, $values)) {
      $field = $result->fetchField();
    }
    return !empty($field) ? $field : 0;
  }
}