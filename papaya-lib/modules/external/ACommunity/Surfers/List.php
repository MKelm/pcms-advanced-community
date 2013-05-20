<?php
/**
 * Advanced community surfers list
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
 * Base ui content object
 */
require_once(dirname(__FILE__).'/../Ui/Content/Object.php');

/**
 * Advanced community surfers list
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunitySurfersList extends ACommunityUiContentObject {

  /**
   * Surfer status data
   * @var ACommunitySurferStatusData
   */
  protected $_data = NULL;

  /**
   * Get/set surfer status data
   *
   * @param ACommunitySurferStatusData $data
   * @return ACommunitySurferStatusData
   */
  public function data(ACommunitySurferStatusData $data = NULL) {
    if (isset($data)) {
      $this->_data = $data;
    } elseif (is_null($this->_data)) {
      include_once(dirname(__FILE__).'/List/Data.php');
      $this->_data = new ACommunitySurfersListData();
      $this->_data->papaya($this->papaya());
      $this->_data->owner = $this;
    }
    return $this->_data;
  }

  /**
  * Create dom node structure of the given object and append it to the given xml
  * element node.
  *
  * @param PapayaXmlElement $parent
  */
  public function appendTo(PapayaXmlElement $parent) {
    $listElement = $parent->appendElement('acommunity-surfers-list');
    $this->data()->initialize();
    foreach ($this->data()->surfers as $surfer) {
      $surferElement = $listElement->appendElement(
        'surfer',array(
          'handle' => $surfer['handle'],
          'givenname' => $surfer['givenname'],
          'surname' => $surfer['surname'],
          'avatar' => PapayaUtilStringXml::escapeAttribute($surfer['avatar']),
          'page-link' => PapayaUtilStringXml::escapeAttribute($surfer['page_link'])
        )
      );
      if (!empty($surfer['last_action'])) {
        $surferElement->appendElement(
          'last-time',
          array('caption' => $this->data()->captions['last_action']),
          $surfer['last_action']
        );
      } else {
        $surferElement->appendElement(
          'last-time',
          array('caption' => $this->data()->captions['registration']),
          $surfer['registration']
        );
      }

    }

  }

}