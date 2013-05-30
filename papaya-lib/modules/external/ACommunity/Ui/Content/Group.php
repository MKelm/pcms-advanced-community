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
  * Is public flag
  *
  * @var string
  */
  protected $_isPublic = NULL;

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
   * Edit link
   * @var string
   */
  protected $_editLink = NULL;

  /**
   * Edit link caption
   * @var string
   */
  protected $_editLinkCaption = NULL;

  /**
   * Accept invitation link
   * @var string
   */
  protected $_acceptInvitationLink = NULL;

  /**
   * Accept invitation link caption
   * @var string
   */
  protected $_acceptInvitationLinkCaption = NULL;

  /**
   * Decline invitation link
   * @var string
   */
  protected $_declineInvitationLink = NULL;

  /**
   * Decline invitation link caption
   * @var string
   */
  protected $_declineInvitationLinkCaption = NULL;

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
    'isPublic' => array('_isPublic', '_isPublic'),
    'deleteLink' => array('_deleteLink', '_deleteLink'),
    'deleteLinkCaption' => array('_deleteLinkCaption', '_deleteLinkCaption'),
    'editLink' => array('_editLink', '_editLink'),
    'editLinkCaption' => array('_editLinkCaption', '_editLinkCaption'),
    'acceptInvitationLink' => array('_acceptInvitationLink', '_acceptInvitationLink'),
    'acceptInvitationLinkCaption' => array('_acceptInvitationLinkCaption', '_acceptInvitationLinkCaption'),
    'declineInvitationLink' => array('_declineInvitationLink', '_declineInvitationLink'),
    'declineInvitationLinkCaption' => array('_declineInvitationLinkCaption', '_declineInvitationLinkCaption'),
    'pageLink' => array('_pageLink', '_pageLink')
  );

  /**
  * Create object and store intialization values.
  *
  * @param integer $id
  * @param string $text
  * @param string $surferHandle
  * @param integer $time
  * @param string $pageLink
  * @param integer $isPublic
  */
  public function __construct($id, $title, $time, $image, $pageLink, $isPublic) {
    $this->id = $id;
    $this->title = $title;
    $this->time = $time;
    $this->image = $image;
    $this->pageLink = $pageLink;
    $this->isPublic = $isPublic;
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
    $attributes = array(
      'id' => (int)$this->id,
      'title' => PapayaUtilStringXml::escapeAttribute($this->title),
      'time' => $this->time,
      'image' => PapayaUtilStringXml::escapeAttribute($this->image),
      'page-link' => PapayaUtilStringXml::escapeAttribute($this->pageLink)
    );
    if (!is_null($this->isPublic)) {
      $attributes['is-public'] = (int)$this->isPublic;
    }
    $message = $parent->appendElement('group', $attributes);
    if (isset($this->_deleteLink) || isset($this->_editLink) ||
        isset($this->_acceptInvitationLink) || isset($this->_declineInvitationLink)) {
      $commands = $message->appendElement('commands');
      if (isset($this->_editLink)) {
        $commands->appendElement(
          'edit',
          array('caption' => PapayaUtilStringXml::escapeAttribute($this->editLinkCaption)),
          PapayaUtilStringXml::escape($this->editLink)
        );
      }
      if (isset($this->_deleteLink)) {
        $commands->appendElement(
          'delete',
          array('caption' => PapayaUtilStringXml::escapeAttribute($this->deleteLinkCaption)),
          PapayaUtilStringXml::escape($this->deleteLink)
        );
      }
      if (isset($this->_acceptInvitationLink)) {
        $commands->appendElement(
          'accept-invitation',
          array('caption' => PapayaUtilStringXml::escapeAttribute($this->acceptInvitationLinkCaption)),
          PapayaUtilStringXml::escape($this->acceptInvitationLink)
        );
      }
      if (isset($this->_declineInvitationLink)) {
        $commands->appendElement(
          'decline-invitation',
          array('caption' => PapayaUtilStringXml::escapeAttribute($this->declineInvitationLinkCaption)),
          PapayaUtilStringXml::escape($this->declineInvitationLink)
        );
      }
    }
  }
}