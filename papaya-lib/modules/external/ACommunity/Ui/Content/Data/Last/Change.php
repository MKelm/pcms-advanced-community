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
   * Owner object
   * @var ACommunityUiContent
   */
  public $owner = NULL;

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
  private function _lastChange(ACommunityContentLastChange $lastChange = NULL) {
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
  public function setLastChangeTime($ressource, $time = NULL) {
    if (method_exists($this->owner, 'acommunityConnector')) {
      if ($this->owner->acommunityConnector()->cacheSupport()) {
        if (empty($time)) {
          $time = time();
        }
        $lastChange = clone $this->_lastChange();
        $lastChange->assign(array('ressource' => $ressource, 'time' => $time));
        if ($lastChange->save()) {
          return TRUE;
        }
        $this->owner->acommunityConnector()->dispatchMessage(
          sprintf('Cache Support : Could not set ressource: '.$ressource)
        );
        return FALSE;
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Get last change time depending on ressource
   *
   * @param string $ressource
   * @return integer
   */
  public function getLastChangeTime($ressource) {
    if (method_exists($this->owner, 'acommunityConnector')) {
      if ($this->owner->acommunityConnector()->cacheSupport()) {
        $lastChange = clone $this->_lastChange();
        if ($lastChange->load(array('ressource' => $ressource))->load()) {
          return $lastChange->time > 0 ? $lastChange->time : 0;
        }
      }
    }
    return 0;
  }

}