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
   * Last cahnge database record
   * @var object
   */
  protected $_lastChange = NULL;

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
  * Access to last change database record data
  *
  * @param ACommunityContentLastChange $lastChange
  * @return ACommunityContentLastChange
  */
  public function lastChange(ACommunityContentLastChange $lastChange = NULL) {
    if (isset($lastChange)) {
      $this->_lastChange = $lastChange;
    } elseif (is_null($this->_lastChange)) {
      include_once(dirname(__FILE__).'/../../Content/Last/Change.php');
      $this->_lastChange = new ACommunityContentLastChange();
      $this->_lastChange->papaya($this->papaya());
    }
    return $this->_lastChange;
  }

  /**
   * Set last change timestamp on contact changes
   *
   * @param string $surferId
   * @param string $contactId
   * @param integer $status
   */
  protected function _setLastChange($surferId, $contactId, $status, $deletion = FALSE) {
    $ressources = array(array('contacts' => array($surferId, $contactId)));
    $ressources[] = array('contact' => array($surferId.':surfer_'.$contactId));
    $ressources[] = array('contact' => array($contactId.':surfer_'.$surferId));
    if ($status == SURFERCONTACT_STATUS_PENDING) {
      $ressources[] = array('contact_requests' => array($contactId));
      $ressources[] = array('contact_own_requests' => array($surferId));
    } else {
      $ressources[] = array('contacts_accepted' => array($surferId, $contactId));
      if ($deletion == FALSE) {
        $ressources[] = array('contact_requests' => array($surferId));
        $ressources[] = array('contact_own_requests' => array($contactId));
      }
    }
    foreach ($ressources as $ressource) {
      foreach ($ressource as $ressourceName => $ressourceIds) {
        foreach ($ressourceIds as $ressourceId) {
          $lastChange = clone $this->lastChange();
          $lastChange->assign(
            array('ressource' => $ressourceName.':surfer_'.$ressourceId, 'time' => time())
          );
          $lastChange->save();
        }
      }
    }
  }

  /**
   * Add contact request
   *
   * @param string $surferId
   */
  public function addContactRequest($surferId, $contactId) {
    $result = FALSE !== $this->databaseAccess()->insertRecord(
      $this->_tableNameSurferContacts,
      NULL,
      array(
        'surfercontact_status' => SURFERCONTACT_STATUS_PENDING,
        'surfercontact_requestor' => $surferId,
        'surfercontact_requested' => $contactId,
        'surfercontact_timestamp' => time()
      )
    );
    if ($result == TRUE) {
      $this->_setLastChange($surferId, $contactId, SURFERCONTACT_STATUS_PENDING);
    }
    return $result;
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
    $result = $result1 !== FALSE && $result2 !== FALSE;
    if ($result == TRUE) {
      $this->_setLastChange($surferId, $contactId, SURFERCONTACT_STATUS_ACCEPTED, FALSE);
    }
    return $result;
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
    $result = FALSE !== $this->databaseAccess()->deleteRecord(
      $this->_tableNameSurferContacts,
      array(
        'surfercontact_status' => SURFERCONTACT_STATUS_PENDING,
        'surfercontact_requestor' => $surferId,
        'surfercontact_requested' => $contactId
      )
    );
    if ($result == TRUE) {
      $this->_setLastChange($surferId, $contactId, SURFERCONTACT_STATUS_PENDING);
    }
    return $result;
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
    $result = $result1 !== FALSE && $result2 !== FALSE;
    if ($result == TRUE) {
      $this->_setLastChange($surferId, $contactId, SURFERCONTACT_STATUS_ACCEPTED, TRUE);
    }
    return $result;
  }
}