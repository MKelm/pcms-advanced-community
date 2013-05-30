<?php
/**
 * Advanced community comments data class to handle all sorts of related data
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
 * Base ui content data object
 */
require_once(dirname(__FILE__).'/../Ui/Content/Data/Group/Surfer/Relations.php');

/**
 * Advanced community comments data class to handle all sorts of related data
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityCommentsData extends ACommunityUiContentDataGroupSurferRelations {

  /**
   * Data to display paging
   * @var array
   */
  public $paging = array();

  /**
   * Current mode to display a list or a ranking of comments
   * @var string
   */
  public $mode = 'list';

  /**
   * Handle of deleted surfer to replace surfer ids
   * @var string
   */
  protected $_deletedSurferHandle = NULL;

  /**
   * Buffer for current voting cookie data
   * @var array
   */
  protected $_votingCookie = NULL;

  /**
   * Contains comments list data
   * @var array
   */
  protected $_commentsList = NULL;

  /**
   * Contains command links by comment id to be used
   * @var array
   */
  protected $_commandLinks = NULL;

  /**
   * Comments database records
   * @var object
   */
  protected $_comments = NULL;

  /**
   * Comment database record
   * @var object
   */
  protected $_comment = NULL;

  /**
   * A regular expression to filter reference parameters
   * @var string
   */
  protected $_referenceParametersExpression = 'comments_page|comment_([0-9]+)_page';

  /**
   * Flag of surfer group access for group ressources
   * @var boolean
   */
  public $surferHasGroupAccess = FALSE;

  /**
   * Check if the current active surfer is the owner of the current ressource
   *
   * @return boolean
   */
  public function surferIsRessourceOwner() {
    $ressource = $this->ressource();
    if ($ressource['type'] == 'surfer' && $this->ressourceIsActiveSurfer) {
      return TRUE;
    } elseif ($ressource['type'] == 'image') {
      $ressourceParameters = reset($this->ressourceParameters());
      if (!empty($ressourceParameters)) {
        if (!empty($ressourceParameters['surfer_handle'])) {
          $ownerHandle = $this->owner->communityConnector()->getHandleById($this->currentSurferId());
          if ($ownerHandle == $ressourceParameters['surfer_handle']) {
            return TRUE;
          }
        } elseif (!empty($ressourceParameters['group_handle'])) {
          $groupId = $this->owner->acommunityConnector()->getGroupIdByHandle(
            $ressourceParameters['group_handle']
          );
          if ($groupId > 0 && $this->surferHasStatus($groupId, 'is_owner', 1)) {
            return TRUE;
          }
        }
      }
    } elseif ($ressource['type'] == 'group' && $this->surferHasStatus(NULL, 'is_owner', 1)) {
      return TRUE;
    }
    return FALSE;
  }


  /**
   * Set data by plugin object
   *
   * @param array $data
   * @param array $captionNames
   * @param array $messageNames
   */
  public function setPluginData($data, $captionNames = array(), $messageNames = array()) {
    $this->paging['comments_per_page'] = $data['comments_per_page'];
    if (isset($data['comments_per_comment'])) {
      $this->paging['comments_per_comment'] = $data['comments_per_comment'];
    } else {
      $this->paging['comments_per_comment'] = NULL;
    }
    $this->_deletedSurferHandle = $data['deleted_surfer_handle'];
    $this->_surferAvatarSize = (int)$data['avatar_size'];
    $this->_surferAvatarResizeMode = $data['avatar_resize_mode'];
    parent::setPluginData($data, $captionNames, $messageNames);
  }

  /**
   * Set/get voting cookie data
   *
   * @return array
   */
  public function votingCookie($data = NULL) {
    if (isset($data)) {
      setcookie('papayaACommunityCommentsVoting', serialize($data));
      $this->_votingCookie = $data;
    } elseif (is_null($this->_votingCookie)) {
      if (!empty($_COOKIE['papayaACommunityCommentsVoting'])) {
        $this->_votingCookie = unserialize($_COOKIE['papayaACommunityCommentsVoting']);
      } else {
        $this->_votingCookie = array();
      }
    }
    return $this->_votingCookie;
  }

  /**
  * Access to comment database record data
  *
  * @param ACommunityContentComment $comment
  * @return ACommunityContentComment
  */
  public function comment(ACommunityContentComment $comment = NULL) {
    if (isset($comment)) {
      $this->_comment = $comment;
    } elseif (is_null($this->_comment)) {
      include_once(dirname(__FILE__).'/../Content/Comment.php');
      $this->_comment = new ACommunityContentComment();
      $this->_comment->papaya($this->papaya());
    }
    return $this->_comment;
  }

  /**
  * Access to the comments database records data
  *
  * @param ACommunityContentComments $comments
  * @return ACommunityContentComments
  */
  public function comments(ACommunityContentComments $comments = NULL) {
    if (isset($comments)) {
      $this->_comments = $comments;
    } elseif (is_null($this->_comments)) {
      include_once(dirname(__FILE__).'/../Content/Comments.php');
      $this->_comments = new ACommunityContentComments();
      $this->_comments->papaya($this->papaya());
      if ($this->mode == 'ranking') {
        $this->_comments->setRankingOrder();
      }
    }
    return $this->_comments;
  }

  /**
   * Get/set command links depending on loaded comments
   *
   * @param array $links
   * @return array
   */
  public function commandLinks($links = NULL) {
    if (isset($links)) {
      $this->_commandLinks = $links;
    } elseif (is_null($this->_commandLinks)) {
      $this->_commandLinks = array();
      if ($this->mode == 'list') {
        $votingCookieData = $this->votingCookie();
        $commentsList = $this->commentsList();
        $this->_getCommandLinks($this->_commandLinks, $commentsList, $votingCookieData);
      }
    }
    return $this->_commandLinks;
  }

  /**
   * Get command links by loaded comments list
   *
   * @param reference $links
   * @param array $commentsList
   * @param array $votingCookieData
   */
  protected function _getCommandLinks(&$links, $commentsList, $votingCookieData) {
    if (!empty($commentsList['data'])) {
      $surferIsModerator = $this->surferIsModerator();
      $surferIsRessourceOwner = $this->surferIsRessourceOwner();
      foreach ($commentsList['data'] as $id => $comment) {

        $links[$id]['reply'] = NULL;
        if (isset($comment['childs'])) {
          if ($this->papaya()->surfer->isValid) {
            $reference = clone $this->reference();
            $reference->setParameters(
              array(
                'command' => 'reply',
                'comment_id' => $id
              ),
              $this->owner->parameterGroup()
            );
            $links[$id]['reply'] = $reference->getRelative();
          }
        }
        if ($surferIsModerator || $surferIsRessourceOwner) {
          $reference = clone $this->reference();
          $reference->setParameters(
            array(
              'command' => 'delete',
              'comment_id' => $id
            ),
            $this->owner->parameterGroup()
          );
          $links[$id]['delete'] = $reference->getRelative();
        }

        if (empty($votingCookieData[$id])) {
          $reference = clone $this->reference();
          $reference->setParameters(
            array(
              'command' => 'vote_up',
              'comment_id' => $id
            ),
            $this->owner->parameterGroup()
          );
          $links[$id]['vote_up'] = $reference->getRelative();

          $reference = clone $this->reference();
          $reference->setParameters(
            array(
              'command' => 'vote_down',
              'comment_id' => $id
            ),
            $this->owner->parameterGroup()
          );
          $links[$id]['vote_down'] = $reference->getRelative();
        } else {
          $links[$id]['vote_up'] = NULL;
          $links[$id]['vote_down'] = NULL;
        }

        if (isset($comment['childs'])) {
          $this->_getCommandLinks($links, $comment['childs'], $votingCookieData);
        }
      }
    }
  }

  /**
   * Get/set comments list data
   *
   * @param array $list
   * @return array
   */
  public function commentsList($list = NULL) {
    if (isset($list)) {
      $this->_commentsList = $list;
    } elseif (is_null($this->_commentsList)) {
      $this->_commentsList = array();
      $this->_getCommentsList($this->_commentsList);
    }
    return $this->_commentsList;
  }

  /**
   * Get comments list by parameters and comment database records
   *
   * @param reference $listData
   * @param integer $parentId
   */
  protected function _getCommentsList(&$listData, $parentId = 0) {
    $commentsFilter = array(
      'parent_id' => $parentId,
      'language_id' => $this->languageId
    );
    $ressourceData = $this->ressource();
    if (!empty($ressourceData)) {
      $commentsFilter['ressource_type'] = $ressourceData['type'];
      $commentsFilter['ressource_id'] = $ressourceData['id'];
    }

    if ($parentId == 0) {
      $page = $this->owner->parameters()->get('comments_page', 0);
      $pagingLimit = $this->paging['comments_per_page'];
    } else {
      $page = $this->owner->parameters()->get(
        sprintf('comment_%d_page', $parentId), 0
      );
      $pagingLimit = $this->paging['comments_per_comment'];
    }
    $this->comments()->load(
      $commentsFilter,
      $pagingLimit,
      ($page > 0) ? ($page - 1) * $pagingLimit: 0
    );

    $listData['abs_count'] = (int)$this->comments()->absCount();
    $listData['data'] = array();

    $comments = $this->comments()->toArray();
    foreach ($comments as $id => $comment) {
      if ($parentId == 0) {
        $comment['childs'] = array();
        $this->_getCommentsList($comment['childs'], $comment['id']);
      }
      $comment['surfer'] = $this->getSurfer($comment['surfer_id'], $this->_deletedSurferHandle);
      $listData['data'][$id] = $comment;
    }
    return $listData;
  }
}