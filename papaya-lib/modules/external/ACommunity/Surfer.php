<?php
/**
 * Advanced community surfer
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
require_once(dirname(__FILE__).'/Ui/Content/Object.php');

/**
 * Advanced community surfer
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunitySurfer extends ACommunityUiContentObject {
  
  /**
   * Comments data
   * @var ACommunitySurferData
   */
  protected $_data = NULL;
  
  /**
   * Get/set surfer data
   *
   * @param ACommunitySurferData $data
   * @return ACommunitySurferData
   */
  public function data(ACommunitySurferData $data = NULL) {
    if (isset($data)) {
      $this->_data = $data;
    } elseif (is_null($this->_data)) {
      include_once(dirname(__FILE__).'/Surfer/Data.php');
      $this->_data = new ACommunitySurferData();
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
    $page = $parent->appendElement('surfer-page');
    if (!is_null($this->data()->ressource())) {
      $this->data()->initialize();
      $details = $page->appendElement('details');
      $baseDetails = $details->appendElement(
        'group', array('id' => 0, 'caption' => $this->data()->captions['base_details'])
      );
      foreach ($this->data()->surferBaseDetails as $name => $value) {
        $baseDetails->appendElement(
          'detail', 
          array('name' => $name, 'caption' => $this->data()->captions['surfer_'.$name]),
          PapayaUtilStringXml::escape($value)
        );
      }
      foreach ($this->data()->surferDetails as $groupId => $group) {
        $detailsGroup = $details->appendElement(
          'group', array('id' => $groupId, 'caption' => $group['caption'])
        );
        foreach ($group['details'] as $detailName => $detail) {
          $detailsGroup->appendElement(
            'detail', array('name' => $detailName, 'caption' => $detail['caption']), 
            PapayaUtilStringXml::escape($detail['value'])
          );
        }
      }
    }
  }
  
}
