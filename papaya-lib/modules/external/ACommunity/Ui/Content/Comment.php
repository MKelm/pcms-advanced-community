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
  * Surfer
  * 
  * @var string
  */
  protected $_surferHandle = NULL;
  
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
  * Link to vote up
  * 
  * @var string
  */
  protected $_linkVoteUp = NULL;
  
  /**
  * Link to vote down
  * 
  * @var string
  */
  protected $_linkVoteDown = NULL;
  
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
    'surferHandle' => array('_surferHandle', '_surferHandle'),
    'surferAvatar' => array('_surferAvatar', '_surferAvatar'),
    'surferPageLink' => array('_surferPageLink', '_surferPageLink'),
    'time' => array('_time', 'setTime'),
    'votesScore' => array('_votesScore', '_votesScore'),
    'linkReply' => array('_linkReply', '_linkReply'),
    'linkVoteUp' => array('_linkVoteUp', '_linkVoteUp'),
    'linkVoteDown' => array('_linkVoteDown', '_linkVoteDown'),
    'subComments' => array('subComments', 'subComments')
  );
  
  /**
  * Create object and store intialization values.
  *
  * @param integer $id
  * @param string $text
  * @param string $surferId
  * @param integer $time
  * @param integer $votesScore
  */
  public function __construct($id, $text, $surferHandle, $time, $votesScore) {
    $this->id = $id;
    $this->text = $text;
    $this->surferHandle = $surferHandle;
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
        'surfer_handle' => $this->surferHandle,
        'surfer_avatar' => $this->_surferAvatar,
        'surfer_page_link' => $this->_surferPageLink,
        'time' => $this->time,
        'votes_score' => $this->votesScore
      )
    );
    include_once(
      $this->papaya()->options->get('PAPAYA_INCLUDE_PATH', '/').
      'system/sys_base_object.php'
    );
    $text = $comment->appendElement('text');
    $text->appendXml(
      base_object::getXHTMLString($this->text, TRUE)
    );
    
    $links = $comment->appendElement('links');
    if (!is_null($this->linkReply)) {
      $links->appendElement('link', array('name' => 'reply'), $this->linkReply);
    }
    if (!is_null($this->linkVoteUp)) {
      $links->appendElement('link', array('name' => 'vote_up'), $this->linkVoteUp);
    }
    if (!is_null($this->linkVoteDown)) {
      $links->appendElement('link', array('name' => 'vote_down'), $this->linkVoteDown);
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
