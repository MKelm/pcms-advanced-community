<?php
/**
 * Advanced community ui content data last change
 *
 * Features for changeable content data
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
 * Advanced community ui content data last change
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityUiContentDataLastChange extends PapayaObject {

  /**
   * Last cahnge database record
   * @var object
   */
  protected $_lastChange = NULL;

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
      include_once(dirname(__FILE__).'/../../../../Content/Last/Change.php');
      $this->_lastChange = new ACommunityContentLastChange();
      $this->_lastChange->papaya($this->papaya());
    }
    return $this->_lastChange;
  }

  /**
   * Set last change time depending on ressource
   *
   * @param string $ressource
   * @return boolean
   */
  protected function _setLastChangeTime($ressource) {
    $lastChange = clone $this->lastChange();
    $lastChange->assign(array('ressource' => $ressource, 'time' => time()));
    if ($lastChange->save()) {
      return TRUE;
    }
    return FALSE;
  }

}