<?php
/**
 * Advanced community ui content group
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
 * Advanced community ui content group
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityUiContentGroup extends PapayaUiControlCollectionItem {

  /**
  * Id
  *
  * @var integer
  */
  protected $_id = NULL;

  /**
  * Image
  *
  * @var string
  */
  protected $_image = NULL;

  /**
  * Text
  *
  * @var string
  */
  protected $_title = NULL;

  /**
  * Time
  *
  * @var string
  */
  protected $_time = NULL;

  /**
   * Delete link
   * @var string
   */
  protected $_deleteLink = NULL;

  /**
   * Delete link caption
   * @var string
   */
  protected $_deleteLinkCaption = NULL;

  /**
   * Group page link
   * @var string
   */
  protected $_pageLink = NULL;

  /**
  * Allow to assign the internal (protected) variables using a public property
  *
  * @var array
  */
  protected $_declaredProperties = array(
    'id' => array('_id', '_id'),
    'image' => array('_image', '_image'),
    'title' => array('_title', '_title'),
    'time' => array('_time', 'setTime'),
    'deleteLink' => array('_deleteLink', '_deleteLink'),
    'deleteLinkCaption' => array('_deleteLinkCaption', '_deleteLinkCaption'),
    'pageLink' => array('_pageLink', '_pageLink')
  );

  /**
  * Create object and store intialization values.
  *
  * @param integer $id
  * @param string $text
  * @param string $surferHandle
  * @param integer $time
  */
  public function __construct($id, $title, $time, $image, $pageLink) {
    $this->id = $id;
    $this->title = $title;
    $this->time = $time;
    $this->image = $image;
    $this->pageLink = $pageLink;
  }

  /**
  * Set a date time string.
  *
  * @param integer $time
  */
  protected function setTime($time) {
    $this->_time = date('Y-m-d H:i:s', $time);
  }

  /**
  * Return the collection for the item, overload for code completion and type check
  *
  * @param ACommunityUiContentGroups $groups
  * @return ACommunityUiContentGroups
  */
  public function collection(ACommunityUiContentGroups $groups = NULL) {
    return parent::collection($groups);
  }

  /**
  * Append entry item xml to parent xml element.
  *
  * @param PapayaXmlElement $parent
  */
  public function appendTo(PapayaXmlElement $parent) {
    $message = $parent->appendElement(
      'group',
      array(
        'id' => $this->id,
        'title' => PapayaUtilStringXml::escapeAttribute($this->title),
        'time' => $this->time,
        'image' => PapayaUtilStringXml::escapeAttribute($this->image),
        'page-link' => PapayaUtilStringXml::escapeAttribute($this->pageLink)
      )
    );
    if (!empty($this->_deleteLink) && !empty($this->_deleteLinkCaption)) {
      $commands = $message->appendElement('commands');
      $commands->appendElement(
        'delete',
        array('caption' => PapayaUtilStringXml::escapeAttribute($this->deleteLinkCaption)),
        PapayaUtilStringXml::escape($this->deleteLink)
      );
    }
  }
}