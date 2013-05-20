<?php
/**
 * Advanced community content object contains basic methods for further objects
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
 * Advanced community content object contains basic methods for further objects
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityUiContentObject extends PapayaObjectInteractive
  implements PapayaXmlAppendable {
  
  /**
   * Community connector
   * @var connector_surfers
   */
  protected $_communityConnector = NULL;
  
  /**
   * Advanced Community connector
   * @var ACommunityConnector
   */
  protected $_acommunityConnector = NULL;
    
  /**
   * Create dom node structure of the given object and append it to the given xml
   * element node.
   *
   * @param PapayaXmlElement $parent
   */
  public function appendTo(PapayaXmlElement $parent) {
  }
  
  /**
   * Compile output xml.
   * 
   * @return string
   */
  public function getXml() {
    $dom = new PapayaXmlDocument();
    $dom->appendElement('ui-content');
    $this->appendTo($dom->documentElement);
    $xml = '';
    foreach ($dom->documentElement->childNodes as $node) {
      $xml .= $node->ownerDocument->saveXml($node);
    }
    return $xml;
  }
  
  /**
   * Get/set community connector
   * 
   * @param object $connector
   * @return object
   */
  public function communityConnector(connector_surfers $connector = NULL) {
    if (isset($connector)) {
      $this->_communityConnector = $connector;
    } elseif (is_null($this->_communityConnector)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
      $this->_communityConnector = base_pluginloader::getPluginInstance(
        '06648c9c955e1a0e06a7bd381748c4e4', $this
      );
    }
    return $this->_communityConnector;
  }
  
  /**
   * Get/set advanced community connector
   * 
   * @param object $connector
   * @return object
   */
  public function acommunityConnector(ACommunityConnector $connector = NULL) {
    if (isset($connector)) {
      $this->_acommunityConnector = $connector;
    } elseif (is_null($this->_acommunityConnector)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
      $this->_acommunityConnector = base_pluginloader::getPluginInstance(
        '0badeb14ea2d41d5bcfd289e9d190534', $this
      );
    }
    return $this->_acommunityConnector;
  }
}
