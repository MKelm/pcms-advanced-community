<?php
/**
 * Advanced community group surfer relations helper methods
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
 * Advanced community group surfer relations helper methods
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityGroupSurferRelations extends PapayaObject {

  /**
   * Group database record
   * @var object
   */
  protected $_group = NULL;

  /**
   * Group surfer relations database records
   * @var object
   */
  protected $_content = NULL;

  /**
   * Contains surfer group status by group id
   * @var array
   */
  protected $_status = NULL;

  /**
   * Perform changes to group surfers
   * @var ACommunityGroupSurferChanges
   */
  protected $_changes = NULL;

  /**
   * Set a connector to support some functionalities in group surfer changes class.
   * @var ACommunityConnector
   */
  protected $_acommunityConnector = NULL;

  /**
   * Get/set advanced community connector
   *
   * @param object $connector
   * @return object
   */
  public function acommunityConnector(ACommunityConnector $connector = NULL) {
    if (isset($connector)) {
      $this->_acommunityConnector = $connector;
    }
    return $this->_acommunityConnector;
  }

  /**
  * Access to group database record data
  *
  * @param ACommunityContentGroup $group
  * @return ACommunityContentGroup
  */
  public function group(ACommunityContentGroup $group = NULL) {
    if (isset($group)) {
      $this->_group = $group;
    } elseif (is_null($this->_group)) {
      include_once(dirname(__FILE__).'/../../Content/Group.php');
      $this->_group = new ACommunityContentGroup();
      $this->_group->papaya($this->papaya());
    }
    return $this->_group;
  }

  /**
  * Access to group surfer relations database records data
  *
  * @param ACommunityContentGroupSurferRelations $content
  * @return ACommunityContentGroupSurferRelations
  */
  public function content(ACommunityContentGroupSurferRelations $content = NULL) {
    if (isset($content)) {
      $this->_content = $content;
    } elseif (is_null($this->_content)) {
      include_once(dirname(__FILE__).'/../../Content/Group/Surfer/Relations.php');
      $this->_content = new ACommunityContentGroupSurferRelations();
      $this->_content->papaya($this->papaya());
    }
    return $this->_content;
  }

  /**
   * Detects if the current active surfer has a group status or returns the group status data
   * if no status value (and status name) have been set.
   *
   * @param integer $groupId
   * @param string $name of status
   * @param integer $value of status
   * @return mixed
   */
  public function status($groupId, $surferId, $name = NULL, $value = NULL) {
    if (isset($groupId) && isset($surferId)) {
      if (!isset($this->_status[$surferId][$groupId])) {
        $this->content()->load(array('id' => $groupId, 'surfer_id' => $surferId));
        $this->_status[$surferId][$groupId] = reset($this->content()->toArray());
        if (empty($this->_status[$surferId][$groupId])) {
          $this->_status[$surferId][$groupId] = FALSE;
        }
      }
      if (isset($name) && isset($value) &&
          isset($this->_status[$surferId][$groupId][$name])) {
        return $this->_status[$surferId][$groupId][$name] == $value;
      } elseif (isset($name) && isset($this->_status[$surferId][$groupId][$name])) {
        return $this->_status[$surferId][$groupId][$name];
      } elseif (!empty($this->_status[$surferId][$groupId])) {
        return $this->_status[$surferId][$groupId];
      }
    }
    return FALSE;
  }

  /**
  * Perform changes to group surfers
  *
  * @param ACommunityGroupSurferChanges $changes
  * @return ACommunityGroupSurferChanges
  */
  public function changes(ACommunityGroupSurferChanges $changes = NULL) {
    if (isset($changes)) {
      $this->_changes = $changes;
    } elseif (is_null($this->_changes)) {
      include_once(dirname(__FILE__).'/Changes.php');
      $this->_changes = new ACommunityGroupSurferChanges();
      $this->_changes->papaya($this->papaya());
      $this->_changes->groupSurferRelations = $this;
    }
    return $this->_changes;
  }

}