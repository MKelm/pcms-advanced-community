<?php
/**
 * Advanced community surfer contact changes
 *
 * This class offers methods to delete and modify community data on surfer deletion
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
 * Load base contacts object for constants
 */
require_once(PAPAYA_INCLUDE_PATH.'modules/_base/community/base_contacts.php');


/**
 * Advanced community surfer contact changes
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunitySurferContactChanges extends PapayaObject {

  /**
  * Stored database access object
  * @var PapayaDatabaseAccess
  */
  protected $_databaseAccess = NULL;

  /**
   * Table name of comments
   * @var string
   */
  protected $_tableNameSurferContacts = PAPAYA_DB_TBL_SURFERCONTACTS;

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
   * Add contact request
   *
   * @param string $surferId
   */
  public function addContactRequest($surferId, $contactId) {
    return FALSE !== $this->databaseAccess()->insertRecord(
      $this->_tableNameSurferContacts,
      NULL,
      array(
        'surfercontact_status' => SURFERCONTACT_STATUS_PENDING,
        'surfercontact_requestor' => $surferId,
        'surfercontact_requested' => $contactId,
        'surfercontact_timestamp' => time()
      )
    );
  }

  /**
   * Accept contact request
   *
   * @param string $surferId
   */
  public function acceptContactRequest($surferId, $contactId) {
    $result1 = $this->databaseAccess()->updateRecord(
      $this->_tableNameSurferContacts,
      array(
        'surfercontact_status' => SURFERCONTACT_STATUS_ACCEPTED,
        'surfercontact_timestamp' => time()
      ),
      array(
        'surfercontact_requestor' => $contactId,
        'surfercontact_requested' => $surferId
      )
    );
    $result2 = $this->databaseAccess()->insertRecord(
      $this->_tableNameSurferContacts,
      NULL,
      array(
        'surfercontact_status' => SURFERCONTACT_STATUS_ACCEPTED,
        'surfercontact_requestor' => $surferId,
        'surfercontact_requested' => $contactId,
        'surfercontact_timestamp' => time()
      )
    );
    return $result1 !== FALSE && $result2 !== FALSE;
  }

  /**
   * Decline contact request
   *
   * @param string $surferId
   */
  public function declineContactRequest($surferId, $contactId) {
    /**
     * Deletes contact request instead of setting contact status to 0 (none),
     * because the base_contact->isContact() method cannot distinguish between existing records
     * and non-existing records. This approach avoids duplicate entries on contact requests.
     */
    return $this->deleteContactRequest($contactId, $surferId);
  }

  /**
   * Delete contact request
   *
   * @param string $surferId
   * @param string $contactId
   */
  public function deleteContactRequest($surferId, $contactId) {
    return FALSE !== $this->databaseAccess()->deleteRecord(
      $this->_tableNameSurferContacts,
      array(
        'surfercontact_status' => SURFERCONTACT_STATUS_PENDING,
        'surfercontact_requestor' => $surferId,
        'surfercontact_requested' => $contactId
      )
    );
  }

  /**
   * Delete contact by surfer id
   *
   * @param string $surferId
   * @param string $contactId
   */
  public function deleteContact($surferId, $contactId) {
    $result1 = $this->databaseAccess()->deleteRecord(
      $this->_tableNameSurferContacts,
      array(
        'surfercontact_status' => SURFERCONTACT_STATUS_ACCEPTED,
        'surfercontact_requestor' => $surferId,
        'surfercontact_requested' => $contactId
      )
    );
    $result2 = $this->databaseAccess()->deleteRecord(
      $this->_tableNameSurferContacts,
      array(
        'surfercontact_status' => SURFERCONTACT_STATUS_ACCEPTED,
        'surfercontact_requestor' => $contactId,
        'surfercontact_requested' => $surferId
      )
    );
    return $result1 !== FALSE || $result2 !== FALSE;
  }
}