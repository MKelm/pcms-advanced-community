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
 * Class for changeable content data
 */
require_once(dirname(__FILE__).'/../../Ui/Content/Data/Last/Change.php');


/**
 * Advanced community surfer contact changes
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunitySurferContactChanges extends ACommunityUiContentDataLastChange {

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
    $result = TRUE;
    foreach ($ressources as $ressource) {
      foreach ($ressource as $ressourceName => $ressourceIds) {
        foreach ($ressourceIds as $ressourceId) {
          $result = $result && $this->setLastChangeTime($ressourceName.':surfer_'.$ressourceId);
        }
      }
    }
    return $result;
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
      return $this->_setLastChange($surferId, $contactId, SURFERCONTACT_STATUS_PENDING);
    }
    return FALSE;
  }

  /**
   * Accept contact request
   *
   * @param string $surferId
   */
  public function acceptContactRequest($surferId, $contactId) {
    $result1 = FALSE !== $this->databaseAccess()->updateRecord(
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
    $result2 = FALSE !== $this->databaseAccess()->insertRecord(
      $this->_tableNameSurferContacts,
      NULL,
      array(
        'surfercontact_status' => SURFERCONTACT_STATUS_ACCEPTED,
        'surfercontact_requestor' => $surferId,
        'surfercontact_requested' => $contactId,
        'surfercontact_timestamp' => time()
      )
    );
    if ($result1 && $result2) {
      return $this->_setLastChange($surferId, $contactId, SURFERCONTACT_STATUS_ACCEPTED, FALSE);
    }
    return FALSE;
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
      return $this->_setLastChange($surferId, $contactId, SURFERCONTACT_STATUS_PENDING);
    }
    return FALSE;
  }

  /**
   * Delete contact by surfer id
   *
   * @param string $surferId
   * @param string $contactId
   */
  public function deleteContact($surferId, $contactId) {
    $result1 = FALSE !== $this->databaseAccess()->deleteRecord(
      $this->_tableNameSurferContacts,
      array(
        'surfercontact_status' => SURFERCONTACT_STATUS_ACCEPTED,
        'surfercontact_requestor' => $surferId,
        'surfercontact_requested' => $contactId
      )
    );
    $result2 = FALSE !== $this->databaseAccess()->deleteRecord(
      $this->_tableNameSurferContacts,
      array(
        'surfercontact_status' => SURFERCONTACT_STATUS_ACCEPTED,
        'surfercontact_requestor' => $contactId,
        'surfercontact_requested' => $surferId
      )
    );
    if ($result1 && $result2) {
      return $this->_setLastChange($surferId, $contactId, SURFERCONTACT_STATUS_ACCEPTED, TRUE);
    }
    return FALSE;
  }
}