<?php
/**
 * Advanced community groups data class to handle all sorts of related data
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
 * Advanced community groups data class to handle all sorts of related data
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityGroupsData extends ACommunityUiContentData {

  /**
   * Data to display paging
   * @var array
   */
  public $pagingItemsPerPage = 0;

  /**
   * Contains groups list data
   * @var array
   */
  protected $_groupsList = NULL;

  /**
   * Contains command links by group id to be used
   * @var array
   */
  protected $_commandLinks = NULL;

  /**
   * Groups database records
   * @var object
   */
  protected $_groups = NULL;

  /**
   * Group database record
   * @var object
   */
  protected $_group = NULL;

  /**
   * A regular expression to filter reference parameters
   * @var string
   */
  protected $_referenceParametersExpression = 'groups_page';

  /**
   * Contains current groups onwer status to load and manage owned groups only
   * @var boolean
   */
  protected $_surferIsGroupsOwner = FALSE;

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
   * Media folder id for group images
   * @var integer
   */
  public $groupImagesFolderId = NULL;

  /**
   * Media db edit object
   * @var object
   */
  protected $_mediaDBEdit = NULL;

  /**
   * Check if the current active surfer is the owner of groups
   *
   * @return boolean
   */
  public function surferIsGroupsOwner($surferIsGroupsOwner = NULL) {
    if (isset($surferIsGroupsOwner)) {
      $this->_surferIsGroupsOwner = $surferIsGroupsOwner;
    }
    return $this->_surferIsGroupsOwner;
  }

  /**
   * Set data by plugin object
   *
   * @param array $data
   * @param array $captionNames
   * @param array $messageNames
   */
  public function setPluginData($data, $captionNames = array(), $messageNames = array()) {
    $this->pagingItemsPerPage = $data['groups_per_page'];
    $this->groupsPerRow = $data['groups_per_row'];
    $this->groupImagesFolderId = $data['group_images_folder'];
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
  * Access to the groups database records data
  *
  * @param ACommunityContentGroups $groups
  * @return ACommunityContentGroups
  */
  public function groups(ACommunityContentGroups $groups = NULL) {
    if (isset($groups)) {
      $this->_groups = $groups;
    } elseif (is_null($this->_groups)) {
      include_once(dirname(__FILE__).'/../Content/Groups.php');
      $this->_groups = new ACommunityContentGroups();
      $this->_groups->papaya($this->papaya());
    }
    return $this->_groups;
  }

  /**
   * Get/set command links depending on loaded groups
   *
   * @param array $links
   * @return array
   */
  public function commandLinks($links = NULL) {
    if (isset($links)) {
      $this->_commandLinks = $links;
    } elseif (is_null($this->_commandLinks)) {
      $this->_commandLinks = array();
      $groupsList = $this->groupsList();
      $this->_getCommandLinks($this->_commandLinks, $groupsList);
    }
    return $this->_commandLinks;
  }

  /**
   * Get command links by loaded groups list
   *
   * @param reference $links
   * @param array $groupsList
   */
  protected function _getCommandLinks(&$links, $groupsList) {
    if (!empty($groupsList['data'])) {
      if ($this->surferIsModerator() || $this->surferIsGroupsOwner()) {
        foreach ($groupsList['data'] as $id => $group) {
          $links[$id]['delete'] = NULL;
          $reference = clone $this->reference();
          $reference->setParameters(
            array(
              'command' => 'delete_group',
              'group_id' => $id
            ),
            $this->owner->parameterGroup()
          );
          $links[$id]['delete'] = $reference->getRelative();
        }
      }
    }
  }

  /**
   * Get/set groups list data
   *
   * @param array $list
   * @return array
   */
  public function groupsList($list = NULL) {
    if (isset($list)) {
      $this->_groupsList = $list;
    } elseif (is_null($this->_groupsList)) {
      $this->groupsList = array();
      $this->_getGroupsList($this->_groupsList);
    }
    return $this->_groupsList;
  }

  /**
   * Get groups list by parameters and groups database records
   *
   * @param reference $listData
   */
  protected function _getGroupsList(&$listData) {
    if ($this->surferIsGroupsOwner()) {
      $groupsFilter = array('owner' => $this->currentSurferId());
    } else {
      $groupsFilter = array('public' => 1);
    }
    $page = $this->owner->parameters()->get('groups_page', 0);
    $this->groups()->load(
      $groupsFilter,
      $this->pagingItemsPerPage,
      ($page > 0) ? ($page - 1) * $this->pagingItemsPerPage : 0
    );
    $listData['abs_count'] = (int)$this->groups()->absCount();
    $listData['data'] = $this->groups()->toArray();

    $defaultImageId = $this->owner->acommunityConnector()->getGroupsDefaultImageId();
    include_once(PAPAYA_INCLUDE_PATH.'system/base_thumbnail.php');
    $thumbnail = new base_thumbnail;
    foreach ($listData['data'] as $key => $values) {
      if (empty($values['image'])) {
        $values['image'] = $defaultImageId;
      }
      if (!empty($values['image'])) {
        $listData['data'][$key]['image'] = 'media.thumb.'.$thumbnail->getThumbnail(
          $values['image'], NULL, $this->_imageThumbnailSize, $this->_imageThumbnailSize,
          $this->_imageThumbnailResizeMode
        );
      }
      $listData['data'][$key]['page_link'] = $this->owner->acommunityConnector()
        ->getGroupPageLink($values['id']);
    }
    return $listData;
  }

  /**
   * Media DB Edit to save image uploads
   *
   * @param base_mediadb_edit $mediaDBEdit
   * @return base_mediadb_edit
   */
  public function mediaDBEdit(base_mediadb_edit $mediaDBEdit = NULL) {
    if (isset($mediaDBEdit)) {
      $this->_mediaDBEdit = $mediaDBEdit;
    } elseif (is_null($this->_mediaDBEdit)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_mediadb_edit.php');
      $this->_mediaDBEdit = new base_mediadb_edit();
    }
    return $this->_mediaDBEdit;
  }
}