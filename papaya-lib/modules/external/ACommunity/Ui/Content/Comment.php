<?php
/**
 * Advanced community ui content discussion comment
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
 * Advanced community ui content discussion comment
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityUiContentComment extends PapayaUiControlCollectionItem {

  /**
  * Id
  *
  * @var integer
  */
  protected $_id = NULL;

  /**
  * Surfer name
  *
  * @var string
  */
  protected $_surferName = NULL;

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
  * Votes score
  *
  * @var integer
  */
  protected $_votesScore = NULL;

  /**
  * Link to reply
  *
  * @var string
  */
  protected $_linkReply = NULL;

  /**
  * Link to reply caption
  *
  * @var string
  */
  protected $_linkReplyCaption = NULL;

  /**
  * Link to delete
  *
  * @var string
  */
  protected $_linkDelete = NULL;

  /**
  * Link to delete caption
  *
  * @var string
  */
  protected $_linkDeleteCaption = NULL;

  /**
  * Link to vote up
  *
  * @var string
  */
  protected $_linkVoteUp = NULL;

  /**
  * Link to vote up caption
  *
  * @var string
  */
  protected $_linkVoteUpCaption = NULL;

  /**
  * Link to vote down
  *
  * @var string
  */
  protected $_linkVoteDown = NULL;

  /**
  * Link to vote down caption
  *
  * @var string
  */
  protected $_linkVoteDownCaption = NULL;

  /**
  * Sub comments
  *
  * @var ACommunityUiContentComments
  */
  protected $_subComments = NULL;

  /**
  * Allow to assign the internal (protected) variables using a public property
  *
  * @var array
  */
  protected $_declaredProperties = array(
    'id' => array('_id', '_id'),
    'text' => array('_text', '_text'),
    'surferName' => array('_surferName', '_surferName'),
    'surferAvatar' => array('_surferAvatar', '_surferAvatar'),
    'surferPageLink' => array('_surferPageLink', '_surferPageLink'),
    'time' => array('_time', 'setTime'),
    'votesScore' => array('_votesScore', '_votesScore'),
    'linkReply' => array('_linkReply', '_linkReply'),
    'linkReplyCaption' => array('_linkReplyCaption', '_linkReplyCaption'),
    'linkDelete' => array('_linkDelete', '_linkDelete'),
    'linkDeleteCaption' => array('_linkDeleteCaption', '_linkDeleteCaption'),
    'linkVoteUp' => array('_linkVoteUp', '_linkVoteUp'),
    'linkVoteUpCaption' => array('_linkVoteUpCaption', '_linkVoteUpCaption'),
    'linkVoteDown' => array('_linkVoteDown', '_linkVoteDown'),
    'linkVoteDownCaption' => array('_linkVoteDownCaption', '_linkVoteDownCaption'),
    'subComments' => array('subComments', 'subComments')
  );

  /**
  * Create object and store intialization values.
  *
  * @param integer $id
  * @param string $text
  * @param integer $time
  * @param integer $votesScore
  */
  public function __construct($id, $text, $time, $votesScore) {
    $this->id = $id;
    $this->text = $text;
    $this->time = $time;
    $this->votesScore = $votesScore;
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
  * @param ACommunityUiContentComments $comments
  * @return ACommunityUiContentComments
  */
  public function collection(ACommunityUiContentComments $comments = NULL) {
    return parent::collection($comments);
  }

  /**
  * Append entry item xml to parent xml element.
  *
  * @param PapayaXmlElement $parent
  */
  public function appendTo(PapayaXmlElement $parent) {
    $comment = $parent->appendElement(
      'comment',
      array(
        'id' => $this->id,
        'time' => $this->time,
        'votes_score' => $this->votesScore
      )
    );
    include_once(
      $this->papaya()->options->get('PAPAYA_INCLUDE_PATH', '/').
      'system/sys_base_object.php'
    );
    $comment->appendXml($this->text);

    $comment->appendElement(
      'surfer',
      array(
        'name' => $this->surferName,
        'avatar' => PapayaUtilStringXml::escapeAttribute($this->surferAvatar),
        'page-link' => PapayaUtilStringXml::escapeAttribute($this->surferPageLink)
      )
    );

    $links = $comment->appendElement('command-links');
    if (!is_null($this->linkReply)) {
      $links->appendElement(
        'link', array('name' => 'reply', 'caption' => $this->linkReplyCaption),
        PapayaUtilStringXml::escape($this->linkReply)
      );
    }
    if (!is_null($this->linkDelete)) {
      $links->appendElement(
        'link', array('name' => 'delete', 'caption' => $this->linkDeleteCaption),
        PapayaUtilStringXml::escape($this->linkDelete)
      );
    }
    if (!is_null($this->linkVoteUp)) {
      $links->appendElement(
        'link', array('name' => 'vote_up', 'caption' => $this->linkVoteUpCaption),
        PapayaUtilStringXml::escape($this->linkVoteUp)
      );
    }
    if (!is_null($this->linkVoteDown)) {
      $links->appendElement(
        'link', array('name' => 'vote_down', 'caption' => $this->linkVoteDownCaption),
        PapayaUtilStringXml::escape($this->linkVoteDown)
      );
    }

    $this->subComments()->appendTo($comment);
  }

  public function subComments(ACommunityUiContentComments $subComments) {
    if (isset($subComments)) {
      $this->_subComments = $subComments;
    } elseif (is_null($this->_subComments)) {
      include_once(dirname(__FILE__).'/Comments.php');
      $this->_subComments = new ACommunityUiContentComments();
      $this->_subComments->papaya($this->papaya());
    }
    return $this->_subComments;
  }

}
