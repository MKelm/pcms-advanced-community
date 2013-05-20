<?php
/**
 * Advanced community surfers list data class to handle all sorts of related data
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
 * Advanced community surfers list data class to handle all sorts of related data
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunitySurfersListData extends ACommunityUiContentData {

  /**
   * Surfers data
   * @var array
   */
  public $surfers = NULL;

  /**
   * Avatar size
   * @var integer
   */
  protected $_avatarSize = 0;

  /**
   * Avatar resize mode
   * @var string
   */
  protected $_avatarResizeMode = 'mincrop';

  /**
   * Order surfer by last action time or registration time
   * @var string
   */
  protected $_orderByMode = NULL;

  /**
   * Get surfer actions or registrations in a specified timeframe
   * @var string
   */
  protected $_timeframe = NULL;

  /**
   * Surfers limit
   * @var string
   */
  protected $_limit = NULL;

  /**
   * Set data by plugin object
   *
   * @param array $data
   * @param array $captionNames
   * @param array $messageNames
   */
  public function setPluginData($data, $captionNames = array(), $messageNames = array()) {
    $this->_avatarSize = (int)$data['avatar_size'];
    $this->_avatarResizeMode = $data['avatar_resize_mode'];
    $this->_orderByMode = $data['order_by_mode'];
    $this->_timeframe = $data['timeframe'];
    $this->_limit = $data['limit'];
    parent::setPluginData($data, $captionNames, $messageNames);
  }

  /**
   * Intitialize surfer data
   */
  public function initialize() {
    $timeframe = 60 * 60 * 24 * $this->_timeframe;
    if ($this->_orderByMode == 'lastaction') {
      $surfers = $this->owner->communityConnector()->getLastActiveSurfers(
        $timeframe, $this->_limit
      );
      if (!empty($surfers)) {
        $surfers = reset($surfers);
      }
    } else {
      $surfers = $this->owner->communityConnector()->getLatestRegisteredSurfers(
        time() - $timeframe, $this->_limit
      );
    }

    $this->surfers = array();
    if (!empty($surfers)) {
      foreach ($surfers as $surfer) {
        $this->surfers[] = array(
          'handle' => $surfer['surfer_handle'],
          'givenname' => $surfer['surfer_givenname'],
          'surname' => $surfer['surfer_surname'],
          'last_action' => !empty($surfer['surfer_lastaction']) ?
            date('Y-m-d H:i:s', $surfer['surfer_lastaction']) : NULL,
          'registration' => !empty($surfer['surfer_registration']) ?
            date('Y-m-d H:i:s', $surfer['surfer_registration']) : NULL,
          'avatar' => $this->owner->communityConnector()->getAvatar(
            $surfer['surfer_id'], $this->_avatarSize, TRUE, $this->_avatarResizeMode
          ),
          'page_link' => $this->owner->acommunityConnector()->getSurferPageLink($surfer['surfer_id'])
        );
      }
    }
  }

}