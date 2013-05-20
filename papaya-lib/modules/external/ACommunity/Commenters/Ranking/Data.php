<?php
/**
 * Advanced community commenters ranking data class to handle all sorts of related data
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
require_once(dirname(__FILE__).'/../../Ui/Content/Data.php');

/**
 * Advanced community commenters ranking data class to handle all sorts of related data
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityCommentersRankingData extends ACommunityUiContentData {

  /**
   * Limit to show commenters
   * @var integer
   */
  public $commentersLimit = NULL;

  /**
   * Commenters ranking database records
   * @var object
   */
  protected $_commentersRanking = NULL;

  /**
   * Set data by plugin object
   *
   * @param array $data
   * @param array $captionNames
   * @param array $messageNames
   */
  public function setPluginData($data, $captionNames = array(), $messageNames = array()) {
    $this->commentersLimit = $data['commenters_limit'];
    $this->_surferAvatarSize = (int)$data['avatar_size'];
    $this->_surferAvatarResizeMode = $data['avatar_resize_mode'];
    parent::setPluginData($data, $captionNames, $messageNames);
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
      include_once(dirname(__FILE__).'/../../Content/Commenters/Ranking.php');
      $this->_commentersRanking = new ACommunityContentCommentersRanking();
      $this->_commentersRanking->papaya($this->papaya());
    }
    return $this->_commentersRanking;
  }

}