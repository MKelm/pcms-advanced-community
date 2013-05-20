<?php
/**
 * Advanced community ui content message
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
 * Advanced community ui content message
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityUiContentMessage extends PapayaUiControlCollectionItem {

  /**
  * Id
  *
  * @var integer
  */
  protected $_id = NULL;

  /**
  * Surfer handle
  *
  * @var string
  */
  protected $_surferHandle = NULL;

  /**
  * Surfer givenname
  *
  * @var string
  */
  protected $_surferGivenname = NULL;

  /**
  * Surfer surname
  *
  * @var string
  */
  protected $_surferSurname = NULL;

  /**
  * Surfer avatar image
  *
  * @var string
  */
  protected $_surferAvatar = NULL;

  /**
  * Surfer page link
  *
  * @var string
  */
  protected $_surferPageLink = NULL;

  /**
  * Text
  *
  * @var string
  */
  protected $_text = NULL;

  /**
  * Time
  *
  * @var string
  */
  protected $_time = NULL;

  /**
  * Allow to assign the internal (protected) variables using a public property
  *
  * @var array
  */
  protected $_declaredProperties = array(
    'id' => array('_id', '_id'),
    'text' => array('_text', '_text'),
    'surferHandle' => array('_surferHandle', '_surferHandle'),
    'surferGivenname' => array('_surferGivenname', '_surferGivenname'),
    'surferSurname' => array('_surferSurname', '_surferSurname'),
    'surferAvatar' => array('_surferAvatar', '_surferAvatar'),
    'surferPageLink' => array('_surferPageLink', '_surferPageLink'),
    'time' => array('_time', 'setTime')
  );

  /**
  * Create object and store intialization values.
  *
  * @param integer $id
  * @param string $text
  * @param string $surferHandle
  * @param integer $time
  */
  public function __construct($id, $text, $surferHandle, $surferGivenname, $surferSurname, $time) {
    $this->id = $id;
    $this->text = $text;
    $this->surferHandle = $surferHandle;
    $this->surferGivenname = $surferGivenname;
    $this->surferSurname = $surferSurname;
    $this->time = $time;
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
  * @param ACommunityUiContentMessages $messages
  * @return ACommunityUiContentMessages
  */
  public function collection(ACommunityUiContentMessages $messages = NULL) {
    return parent::collection($messages);
  }

  /**
  * Append entry item xml to parent xml element.
  *
  * @param PapayaXmlElement $parent
  */
  public function appendTo(PapayaXmlElement $parent) {
    $message = $parent->appendElement(
      'message', array('id' => $this->id, 'time' => $this->time)
    );
    include_once(
      $this->papaya()->options->get('PAPAYA_INCLUDE_PATH', '/').
      'system/sys_base_object.php'
    );
    $text = $message->appendElement('text');
    $text->appendXml(
      base_object::getXHTMLString($this->text, TRUE)
    );
    $message->appendElement(
      'surfer',
      array(
        'handle' => $this->surferHandle,
        'givenname' => $this->surferGivenname,
        'surname' => $this->surferSurname,
        'avatar' => PapayaUtilStringXml::escapeAttribute($this->surferAvatar),
        'page-link' => $this->surferPageLink
      )
    );
  }

}