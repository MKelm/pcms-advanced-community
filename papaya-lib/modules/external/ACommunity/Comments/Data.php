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
 * Advanced community comments data class to handle all sorts of related data
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityCommentsData extends PapayaObject {
  
  /**
   * Owner object
   * @var ACommunityComments
   */
  public $owner = NULL;
  
  /**
   * Current comments ressource by type and id
   * @var array
   */
  protected $_ressource = NULL;
  
  /**
   * Ressource parameters
   * @var array
   */
  protected $_ressourceParameters = array();
  
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
  protected $_handleDeletedSurfer = NULL;
  
  /**
   * A list of surfer ids used by comments list
   * @var array
   */
  protected $_surferIds = array();
  
  /**
   * A list of surfer handles used by comments list
   * @var array
   */
  protected $_surferHandles = NULL;
  
  /**
   * A list of surfer avatars used by comments list
   * @var array
   */
  protected $_surferAvatars = NULL;
  
  /**
   * A list of captions to be used
   * @var array
   */
  public $captions = array();
  
  /**
   * A list of messages to be used
   * @var array
   */
  public $messages = array();
  
  /**
   * Buffer for current voting cookie data
   * @var array
   */
  protected $_votingCookie = NULL;
  
  /**
   * Contains comments list data 
   * @var array
   */
  public $_commentsList = NULL;
  
  /**
   * Contains links to be used
   * @var array
   */
  public $_links = NULL;
  
  /**
   * Current language id
   * @var integer
   */
  public $languageId = 0;
  
  /**
   * Community connector
   * @var connector_surfers
   */
  protected $_communityConnector = NULL;
  
  /**
   * Page and sub-page parameters to use in sub-objects' links
   * @var array
   */
  protected $_referenceParameters = NULL;
  
  /**
  * Reference object to create urls
  * @var PapayaUiReference
  */
  protected $_reference = NULL;
  
  /**
  * Current surfer id
  * @var string
  */
  protected $_surferId = NULL;
  
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
   * Set data by plugin object
   * 
   * @param array $data
   */
  public function setPluginData($data) {
    $this->paging['comments_per_page'] = $data['comments_per_page'];
    if (isset($data['comments_per_comment'])) {
      $this->paging['comments_per_comment'] = $data['comments_per_comment'];
    } else {
      $this->paging['comments_per_comment'] = NULL;
    }
    $this->_handleDeletedSurfer = $data['handle_deleted_surfer'];
    $mergeCaptions = array('caption_dialog_text', 'caption_dialog_button');
    foreach ($mergeCaptions as $mergeCaption) {
      if (isset($data[$mergeCaption])) {
        $this->captions[$mergeCaption] = $data[$mergeCaption];
      }
    }
    $mergeMessages = array('message_dialog_input_error');
    foreach ($mergeMessages as $mergeMessage) {
      if (isset($data[$mergeMessage])) {
        $this->messages[$mergeMessage] = $data[$mergeMessage];
      }
    }
  }
  
  /**
   * Set/get data of current comments ressource by type and id
   * 
   * @param string $type
   * @param integer|string $id
   */
  public function ressource($type = NULL, $id = NULL) {
    if (isset($type) && isset($id)) {
      $this->_ressource['type'] = $type;
      switch ($type) {
        case 'surfer':
          $id = $this->communityConnector()->getIdByHandle($id);
          break;
      }
      $this->_ressource['id'] = $id;
    }
    return $this->_ressource;
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
   * Set/get surfer handles depending on loaded surfer ids
   * 
   * @var array $surferHandles
   * @return array
   */
  public function surferHandles($surferHandles = NULL) {
    if (isset($surferHandles)) {
      $this->_surferHandles = $surferHandles;
    } elseif (is_null($surferHandles)) {
      $this->_surferHandles = array();
      if (!empty($this->_surferIds)) {
        $surferIds = array_keys($this->_surferIds);
        $surferHandles = $this->communityConnector()->getHandleById($surferIds);
        foreach ($surferIds as $surferId) {
          if (!empty($surferHandles[$surferId])) {
            $this->_surferHandles[$surferId] = $surferHandles[$surferId];
          } else {
            $this->_surferHandles[$surferId] = $this->_handleDeletedSurfer;
          }
        }
      }
    }
    return $this->_surferHandles;
  }
  
  /**
   * Set/get surfer avatars depending on loaded surfer ids
   * 
   * @var array $surferHandles
   * @return array
   */
  public function surferAvatars($surferAvatars = NULL) {
    if (isset($surferAvatars)) {
      $this->_surferAvatars = $surferAvatars;
    } elseif (is_null($surferAvatars)) {
      $this->_surferAvatars = array();
      if (!empty($this->_surferIds)) {
        $surferIds = array_keys($this->_surferIds);
        $surferAvatars = $this->communityConnector()->getAvatar($surferIds);
        foreach ($surferIds as $surferId) {
          if (!empty($surferAvatars[$surferId])) {
            $this->_surferAvatars[$surferId] = $surferAvatars[$surferId];
          } else {
            $this->_surferAvatars[$surferId] = NULL;
          }
        }
      }
    }
    return $this->_surferAvatars;
  }
  
  /**
   * Get/set links depending on loaded comments
   * 
   * @param array $links
   * @return array
   */
  public function links($links = NULL) {
    if (isset($links)) {
      $this->_links = $links;
    } elseif (is_null($this->_links)) {
      $this->_links = array('comment_links' => array());
      $votingCookieData = $this->votingCookie();
      $commentsList = $this->commentsList();
      $this->_getCommentLinks($this->_links, $commentsList, $votingCookieData);
    }
    return $this->_links;
  }
  
  /**
   * Get comments links
   * 
   * @param reference $links
   * @param array $commentsList
   * @param array $votingCookieData
   */
  protected function _getCommentLinks(&$links, $commentsList, $votingCookieData) {
    if (!empty($commentsList['data'])) {
      foreach ($commentsList['data'] as $id => $comment) {

        $links['comment_links'][$id]['reply'] = NULL;
        if (isset($comment['childs'])) {
          $currentSurfer = $this->communityConnector()->getCurrentSurfer();
          if ($currentSurfer->isValid) {
            $reference = clone $this->reference();
            $reference->setParameters(
              array(
                'command' => 'reply',
                'comment_id' => $id
              ),
              $this->owner->parameterGroup()
            );
            $links['comment_links'][$id]['reply'] = $reference->getRelative();
          }
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
          $links['comment_links'][$id]['vote_up'] = $reference->getRelative();
           
          $reference = clone $this->reference();
          $reference->setParameters(
            array(
              'command' => 'vote_down',
              'comment_id' => $id
            ),
            $this->owner->parameterGroup()
          );
          $links['comment_links'][$id]['vote_down'] = $reference->getRelative();
        } else {
          $links['comment_links'][$id]['vote_up'] = NULL;
          $links['comment_links'][$id]['vote_down'] = NULL;
        }
        
        if (isset($comment['childs'])) {
          $this->_getCommentLinks($links, $comment['childs'], $votingCookieData);
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
      $listData['data'][$id] = $comment;
      $this->_surferIds[$comment['surfer_id']] = 1;
    }
    return $listData;
  }
  
  /**
   * Get/ set reference parameters
   * 
   * @param array $parameters
   * @retunr array
   */
  public function referenceParameters($parameters = NULL) {
    if (isset($parameters)) {
      $this->_referenceParameters = $parameters;
    } elseif (is_null($this->_referenceParameters)) {
      $this->_referenceParameters = array();
      foreach ($this->owner->parameters() as $name => $value) {
        if (preg_match('~comments_page|comment_([0-9]+)_page~i', $name)) {
          $this->_referenceParameters[$name] = $value;
        }
      }
    }
    return $this->_referenceParameters;
  }
  
  /**
   * Set ressource parameters for use in reference object
   * 
   * @param string $parameterGroup
   * @param array $parameters
   * @return array
   */
  public function ressourceParameters($parameterGroup = NULL, $parameters = NULL) {
    if (isset($parameterGroup) && isset($parameters)) {
      $this->_ressourceParameters[$parameterGroup] = $parameters;
    }
    return $this->_ressourceParameters;
  }
  
  /**
  * The basic reference object used by the subobjects to create urls.
  *
  * @param PapayaUiReference $reference
  * @return PapayaUiReference
  */
  public function reference(PapayaUiReference $reference = NULL) {
    if (isset($reference)) {
      $this->_reference = $reference;
    } elseif (is_null($this->_reference)) {
      $this->_reference = new PapayaUiReference();
      $this->_reference->papaya($this->papaya());
      $this->_reference->setParameters(
        $this->referenceParameters(), $this->owner->parameterGroup()
      );
      foreach ($this->ressourceParameters() as $parameterGroup => $parameters) {
        $this->_reference->setParameters(
          $parameters, $parameterGroup
        );
      }
    }
    return $this->_reference;
  }
  
  /**
  * Get/set current surfer id
  *
  * @param string $surferId
  * @return string
  */
  public function surferId($surferId = NULL) {
    if (isset($surferId)) {
      $this->_surferId = $surferId;
    } elseif (is_null($this->_surferId)) {
      $currentSurfer = $this->communityConnector()->getCurrentSurfer();
      $this->_surferId = $currentSurfer->surfer['surfer_id'];
    }
    return $this->_surferId;
  }
  
}
