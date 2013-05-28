<?php
/**
 * Advanced community group data class to handle all sorts of related data
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
require_once(dirname(__FILE__).'/../Ui/Content/Data.php');

/**
 * Advanced community group data class to handle all sorts of related data
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityGroupData extends ACommunityUiContentData {

  /**
   * Group title
   * @var string
   */
  public $title = NULL;

  /**
   * Group creation time
   * @var string
   */
  public $time = NULL;

  /**
   * Group text contains description
   * @var string
   */
  public $text = NULL;

  /**
   * Group image
   * @var string
   */
  public $image = NULL;

  /**
   * Image size
   * @var integer
   */
  protected $_imageThumbnailSize = NULL;

  /**
   * Image resize mode
   * @var string
   */
  protected $_imageThumbnailResizeMode = NULL;

  /**
   * Group database record
   * @var object
   */
  protected $_group = NULL;

  /**
   * Set data by plugin object
   *
   * @param array $data
   * @param array $captionNames
   * @param array $messageNames
   */
  public function setPluginData($data, $captionNames = array(), $messageNames = array()) {
    $this->_imageThumbnailSize = (int)$data['image_size'];
    $this->_imageThumbnailResizeMode = $data['image_resize_mode'];
    parent::setPluginData($data, $captionNames, $messageNames);
  }

  /**
  * Access to group database record data
  *
  * @param ACommunityContentGroup $group
  * @return ACommunityContentGroup
  */
  public function group(ACommunityContentGroup $group = NULL) {
    if (isset($group)) {
      $this->_group = $group;
    } elseif (is_null($this->_group)) {
      include_once(dirname(__FILE__).'/../Content/Group.php');
      $this->_group = new ACommunityContentGroup();
      $this->_group->papaya($this->papaya());
    }
    return $this->_group;
  }

  /**
   * Intitialize surfer data
   */
  public function initialize() {
    $ressource = $this->ressource();
    if (!empty($ressource)) {
      $this->group()->load($ressource['id']);
      $group = $this->group()->toArray();
      if (!empty($group['title'])) {
        $this->title = $group['title'];
        $this->time = date('Y-m-d H:i:s', $group['time']);
        $this->text = $group['description'];
        if (empty($group['image'])) {
          $group['image'] = $this->owner->acommunityConnector()->getGroupsDefaultImageId();
        }
        include_once(PAPAYA_INCLUDE_PATH.'system/base_thumbnail.php');
        $thumbnail = new base_thumbnail;
        $this->image = 'media.thumb.'.$thumbnail->getThumbnail(
          $group['image'], NULL, $this->_imageThumbnailSize, $this->_imageThumbnailSize,
          $this->_imageThumbnailResizeMode
        );
      }
    }
  }
}