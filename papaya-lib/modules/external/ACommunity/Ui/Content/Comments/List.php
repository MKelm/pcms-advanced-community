<?php
/**
 * Advanced community ui content discussion
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
 * Advanced community ui content discussion
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityUiContentCommentsList extends PapayaUiControl {

  /**
  * Object buffer for comments
  *
  * @var ACommunityUiContentComments
  */
  protected $_comments = NULL;

  /**
  * Comments data
  * @var ACommunityCommentsData
  */
  protected $_data = NULL;

  /**
  * Declared public properties, see property annotaiton of the class for documentation.
  *
  * @var array
  */
  protected $_declaredProperties = array(
    'comments' => array('comments', 'comments')
  );

  /**
   * Get/set comments data
   *
   * @param ACommunityCommentsData $data
   * @return ACommunityCommentsData
   */
  public function data(ACommunityCommentsData $data = NULL) {
    if (isset($data)) {
      $this->_data = $data;
    }
    return $this->_data;
  }

  /**
  * Fill comments list with comments data
  */
  private function fill() {
    $commentsList = $this->data()->commentsList();
    if (count($commentsList['data']) > 0) {
      $this->comments()->absCount = $commentsList['abs_count'];
      $commandLinks = $this->data()->commandLinks();
      foreach ($commentsList['data'] as $id => $commentData) {
        if (isset($commentData['childs'])) {
          include_once(dirname(__FILE__).'/../Comment.php');
          $comment = new ACommunityUiContentComment(
            $commentData['id'],
            $commentData['text'],
            $commentData['time'],
            $commentData['votes_score']
          );
          $comment->surferName = $commentData['surfer']['name'];
          $comment->surferPageLink = $commentData['surfer']['page_link'];
          $comment->surferAvatar = $commentData['surfer']['avatar'];

          if (isset($commandLinks[$id]['reply'])) {
            $comment->linkReply = $commandLinks[$id]['reply'];
            $comment->linkReplyCaption = $this->data()->captions['command_reply'];
          }
          if (isset($commandLinks[$id]['delete'])) {
            $comment->linkDelete = $commandLinks[$id]['delete'];
            $comment->linkDeleteCaption = $this->data()->captions['command_delete'];
          }
          if (isset($commandLinks[$id]['vote_up'])) {
            $comment->linkVoteUp = $commandLinks[$id]['vote_up'];
            $comment->linkVoteUpCaption = $this->data()->captions['command_vote_up'];
          }
          if (isset($commandLinks[$id]['vote_down'])) {
            $comment->linkVoteDown = $commandLinks[$id]['vote_down'];
            $comment->linkVoteDownCaption = $this->data()->captions['command_vote_down'];
          }

          if (!empty($commentData['childs']['data']) &&
              !empty($this->data()->paging['comments_per_comment'])) {
            include_once(dirname(__FILE__).'/../Comments.php');
            $subComments = new ACommunityUiContentComments($comment);
            $subComments->papaya($this->papaya());
            $subComments->pagingParameterGroup = $this->data()->owner->parameterGroup();
            $subComments->pagingItemsPerPage = (int)$this->data()->paging['comments_per_comment'];
            $subComments->pagingParameterName = sprintf(
              'comment_%d_page', $commentData['id']
            );
            $subComments->pagingGetRelativeUrls = !$this->data()->absoluteReferenceUrl;
            $subComments->reference($this->data()->reference());
            $subComments->absCount = $commentData['childs']['abs_count'];

            foreach ($commentData['childs']['data'] as $subCommentId => $subCommentData) {
              $subComment = new ACommunityUiContentComment(
                $subCommentData['id'],
                $subCommentData['text'],
                $subCommentData['time'],
                $subCommentData['votes_score']
              );
              $subComment->surferName = $subCommentData['surfer']['name'];
              $subComment->surferPageLink = $subCommentData['surfer']['page_link'];
              $subComment->surferAvatar = $subCommentData['surfer']['avatar'];

              if (isset($commandLinks[$subCommentId]['delete'])) {
                $subComment->linkDelete = $commandLinks[$subCommentId]['delete'];
                $subComment->linkDeleteCaption = $this->data()->captions['command_delete'];
              }
              if (isset($commandLinks[$subCommentId]['vote_up'])) {
                $subComment->linkVoteUp = $commandLinks[$subCommentId]['vote_up'];
                $subComment->linkVoteUpCaption = $this->data()->captions['command_vote_up'];
              }
              if (isset($commandLinks[$subCommentId]['vote_down'])) {
                $subComment->linkVoteDown = $commandLinks[$subCommentId]['vote_down'];
                $subComment->linkVoteDownCaption = $this->data()->captions['command_vote_down'];
              }

              $subComments[] = $subComment;
            }

            $comment->subComments($subComments);
          }

          $this->comments[] = $comment;
        }
      }
    }
  }

  /**
  * Append discussion output to parent element.
  *
  * @param PapayaXmlElement $parent
  */
  public function appendTo(PapayaXmlElement $parent) {
    $this->fill();
    $this->comments()->appendTo($parent);
  }

  /**
  * The list of comments
  *
  * @param ACommunityUiContentComments $comments
  */
  public function comments(ACommunityUiContentComments $comments = NULL) {
    if (isset($comments)) {
      $this->_comments = $comments;
    } elseif (is_null($this->_comments)) {
      include_once(dirname(__FILE__).'/../Comments.php');
      $this->_comments = new ACommunityUiContentComments($this);
      $this->_comments->papaya($this->papaya());
      $this->_comments->pagingParameterGroup = $this->data()->owner->parameterGroup();
      $this->_comments->pagingItemsPerPage = (int)$this->data()->paging['comments_per_page'];
      $this->_comments->pagingGetRelativeUrls = !$this->data()->absoluteReferenceUrl;
      $this->_comments->reference($this->data()->reference());
    }
    return $this->_comments;
  }

}
