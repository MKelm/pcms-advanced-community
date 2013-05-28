<?php
/**
 * Advanced community group
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
require_once(dirname(__FILE__).'/Ui/Content.php');

/**
 * Advanced community group
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityGroup extends ACommunityUiContent {

  /**
   * Get/set group data
   *
   * @param ACommunityGroupData $data
   * @return ACommunityGroupData
   */
  public function data(ACommunityGroupData $data = NULL) {
    if (isset($data)) {
      $this->_data = $data;
    } elseif (is_null($this->_data)) {
      include_once(dirname(__FILE__).'/Group/Data.php');
      $this->_data = new ACommunityGroupData();
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
    $page = $parent->appendElement('group-page');
    if (!is_null($this->data()->ressource()) && $this->data()->ressource() != FALSE) {
      $this->data()->initialize();
      $page->appendElement('title', array(), $this->data()->title);
      $page->appendElement(
        'time', array('caption' => $this->data()->captions['time']), $this->data()->time
      );
      $page->appendXml($this->data()->text);
      $page->appendElement('image', array(), PapayaUtilStringXml::escape($this->data()->image));
    } else {
      $page->appendElement('message', array('type' => 'error'), $this->data()->messages['no_group']);
    }
  }

}