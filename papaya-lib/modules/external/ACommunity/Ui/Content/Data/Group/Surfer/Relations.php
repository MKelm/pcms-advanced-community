<?php
/**
 * Advanced community surfers data class with group surfer relations helper methods
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
 * Base ui content data object
 */
require_once(dirname(__FILE__).'/../../../Data.php');

/**
 * Advanced community surfers data class with group surfer relations helper methods
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityUiContentDataGroupSurferRelations extends ACommunityUiContentData {

  /**
   * Group database record
   * @var object
   */
  protected $_group = NULL;

  /**
   * Group surfer relations database records
   * @var object
   */
  protected $_groupSurferRelations = NULL;

  /**
   * Contains surfer group status by group id
   * @var array
   */
  protected $_surferGroupStatus = NULL;

  /**
   * Perform changes to group surfers
   * @var ACommunityGroupSurferChanges
   */
  protected $_groupSurferChanges = NULL;

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
      include_once(dirname(__FILE__).'/../../../../../Content/Group.php');
      $this->_group = new ACommunityContentGroup();
      $this->_group->papaya($this->papaya());
    }
    return $this->_group;
  }

  /**
  * Access to group surfer relations database records data
  *
  * @param ACommunityContentGroupSurferRelations $group
  * @return ACommunityContentGroupSurferRelations
  */
  public function groupSurferRelations(
           ACommunityContentGroupSurferRelations $groupSurferRelations = NULL
         ) {
    if (isset($groupSurferRelations)) {
      $this->_groupSurferRelations = $groupSurferRelations;
    } elseif (is_null($this->_groupSurferRelations)) {
      include_once(dirname(__FILE__).'/../../../../../Content/Group/Surfer/Relations.php');
      $this->_groupSurferRelations = new ACommunityContentGroupSurferRelations();
      $this->_groupSurferRelations->papaya($this->papaya());
    }
    return $this->_groupSurferRelations;
  }

  /**
   * Detects if the current active surfer has a group status
   *
   * @param integer $groupId
   * @param string $name of status
   * @param integer $value of status
   * @return bolean
   */
  public function surferHasStatus($groupId, $name = NULL, $value = NULL) {
    if (is_null($groupId)) {
      $ressource = $this->ressource();
      if (!empty($ressource) && $ressource['type'] == 'group') {
        $groupId = $ressource['id'];
      }
    }
    if (is_null($this->_surferGroupStatus[$groupId]) && !is_null($this->currentSurferId())) {
      $this->groupSurferRelations()->load(
        array('id' => $groupId, 'surfer_id' => $this->currentSurferId())
      );
      $this->_surferGroupStatus[$groupId] = reset($this->groupSurferRelations()->toArray());
      if (empty($this->_surferGroupStatus[$groupId])) {
        $this->_surferGroupStatus[$groupId] = FALSE;
      }
    }
    if (isset($name) && isset($value) && isset($this->_surferGroupStatus[$groupId][$name])) {
      return $this->_surferGroupStatus[$groupId][$name] == $value;
    } elseif (!empty($this->_surferGroupStatus[$groupId])) {
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Perform changes to group surfers
  *
  * @param ACommunityGroupSurferChanges $changes
  * @return ACommunityGroupSurferChanges
  */
  public function groupSurferChanges(ACommunityGroupSurferChanges $changes = NULL) {
    if (isset($changes)) {
      $this->_groupSurferChanges = $changes;
    } elseif (is_null($this->_groupSurferChanges)) {
      include_once(dirname(__FILE__).'/../../../../../Group/Surfer/Changes.php');
      $this->_groupSurferChanges = new ACommunityGroupSurferChanges();
      $this->_groupSurferChanges->papaya($this->papaya());
    }
    return $this->_groupSurferChanges;
  }

}