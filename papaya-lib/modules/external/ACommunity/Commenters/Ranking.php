<?php
/**
 * Advanced community commenters ranking
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
require_once(dirname(__FILE__).'/../Ui/Content/Object.php');

/**
 * Advanced community  commenters ranking
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityCommentersRanking extends ACommunityUiContentObject {

  /**
   * Max limit of commenters
   * @var integer
   */
  public $commentersLimit = 0;
  
  /**
   * Caption for comments amount
   * @var string
   */
  public $commentsAmountCaption = '';
  
  /**
   * Surfer avatar size
   * @var integer
   */
  public $surferAvatarSize = 60;
  
  /**
   * Commenters ranking database records
   * @var object
   */
  protected $_commentersRanking = NULL;
  
  /**
   * A list of surfer ids used by ranking list
   * @var array
   */
  protected $_surferIds = array();
  
  /**
   * A list of surfer handles used by ranking list
   * @var array
   */
  protected $_surferHandles = NULL;
  
  /**
   * A list of surfer avatars used by ranking list
   * @var array
   */
  protected $_surferAvatars = NULL;
  
  /**
  * Create dom node structure of the given object and append it to the given xml
  * element node.
  *
  * @param PapayaXmlElement $parent
  */
  public function appendTo(PapayaXmlElement $parent) {
    $ranking = $parent->appendElement('acommunity-commenters-ranking');
    $this->commentersRanking()->load(array('deleted_surfer' => 0), $this->commentersLimit);
    $commentersRanking = $this->commentersRanking()->toArray();
    if (!empty($commentersRanking)) {
      $this->_surferIds = array_flip(array_keys($commentersRanking));
      $surferHandles = $this->surferHandles();
      $surferAvatars = $this->surferAvatars();
      foreach ($commentersRanking as $id => $commenter) {
        $ranking->appendElement(
          'commenter', 
          array(
            'surfer_handle' => $surferHandles[$id], 
            'surfer_avatar' => $surferAvatars[$id],
            'comments_amount' => $commenter['comments_amount'],
            'comments_amount_caption' => $this->commentsAmountCaption
          )
        );
      }
    }
  }
  
  /**
  * Access to the commenters ranking database records data
  *
  * @param ACommunityContentCommentersRanking $comments
  * @return ACommunityContentCommentersRanking
  */
  public function commentersRanking(
           ACommunityContentCommentersRanking $commentersRanking = NULL
         ) {
    if (isset($commentersRanking)) {
      $this->_commentersRanking = $commentersRanking;
    } elseif (is_null($this->_commentersRanking)) {
      include_once(dirname(__FILE__).'/../Content/Commenters/Ranking.php');
      $this->_commentersRanking = new ACommunityContentCommentersRanking();
      $this->_commentersRanking->papaya($this->papaya());
    }
    return $this->_commentersRanking;
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
            $this->_surferHandles[$surferId] = NULL;
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
        $surferAvatars = $this->communityConnector()->getAvatar(
          $surferIds, $this->surferAvatarSize
        );
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

}
