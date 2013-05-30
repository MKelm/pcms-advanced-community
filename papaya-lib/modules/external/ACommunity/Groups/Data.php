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
   * Group surfer relation database record
   * @var object
   */
  protected $_groupSurferRelation = NULL;

  /**
   * Group surfer relations database records
   * @var object
   */
  protected $_groupSurferRelations = NULL;

  /**
   * A regular expression to filter reference parameters
   * @var string
   */
  protected $_referenceParametersExpression = 'groups_page|mode';

  /**
   * Contains flag to show groups of the current active surfer only
   * @var boolean
   */
  protected $_showOwnGroups = FALSE;

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
   * Contains surfer status by group id
   * @var array
   */
  protected $_surferStatus = NULL;

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
    $this->groupImagesFolderId = isset($data['group_images_folder']) ?
      $data['group_images_folder'] : NULL;
    $this->_imageThumbnailSize = (int)$data['image_size'];
    $this->_imageThumbnailResizeMode = $data['image_resize_mode'];
    parent::setPluginData($data, $captionNames, $messageNames);
  }

  /**
   * Flag to show own groups only
   *
   * @return boolean
   */
  public function showOwnGroups($showOwnGroups = NULL) {
    if (isset($showOwnGroups)) {
      $this->_showOwnGroups = $showOwnGroups;
    }
    return $this->_showOwnGroups;
  }

  /**
   * Detects if the current active surfer is the owner of the selected group
   *
   * @param integer $groupId
   * @param string $name of status
   * @param integer $value of status
   * @return bolean
   */
  public function surferHasStatus($groupId, $name, $value) {
    if (is_null($this->_surferStatus[$groupId])) {
      $this->groupSurferRelations()->load(
        array('id' => $groupId, 'surfer_id' => $this->currentSurferId())
      );
      $this->_surferStatus[$groupId] = reset($this->groupSurferRelations()->toArray());
      if (empty($this->_surferStatus[$groupId])) {
        $this->_surferStatus[$groupId] = FALSE;
      }
    }
    if (!empty($this->_surferStatus[$groupId])) {
      return $this->_surferStatus[$groupId][$name] == $value;
    }
    return FALSE;
  }

  /**
  * Access to group surfer relation database record data
  *
  * @param ACommunityContentGroupSurferRelation $group
  * @return ACommunityContentGroupSurferRelation
  */
  public function groupSurferRelation(
           ACommunityContentGroupSurferRelation $groupSurferRelation = NULL
         ) {
    if (isset($groupSurferRelation)) {
      $this->_groupSurferRelation = $groupSurferRelation;
    } elseif (is_null($this->_groupSurferRelation)) {
      include_once(dirname(__FILE__).'/../Content/Group/Surfer/Relation.php');
      $this->_groupSurferRelation = new ACommunityContentGroupSurferRelation();
      $this->_groupSurferRelation->papaya($this->papaya());
    }
    return $this->_groupSurferRelation;
  }

  /**
  * Access to group surfer relations database records data
  *
  * @param ACommunityContentGroupSurferRelations $group
  * @return ACommunityContentGroupSurferRelations
  */
  public function groupSurferRelations(
           ACommunityContentGroupSurferRelations $groupSurferRelations = NULL
         ) {
    if (isset($groupSurferRelations)) {
      $this->_groupSurferRelations = $groupSurferRelations;
    } elseif (is_null($this->_groupSurferRelations)) {
      include_once(dirname(__FILE__).'/../Content/Group/Surfer/Relations.php');
      $this->_groupSurferRelations = new ACommunityContentGroupSurferRelations();
      $this->_groupSurferRelations->papaya($this->papaya());
    }
    return $this->_groupSurferRelations;
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
      $groupSurferRelations = $this->groupSurferRelations();
      $mode = $this->owner->parameters()->get('mode', NULL);
      foreach ($groupsList['data'] as $id => $group) {
        if ((!$this->showOwnGroups() && $this->surferIsModerator()) ||
            ($this->showOwnGroups() && $mode != 'invitations' &&
             isset($groupSurferRelations[$id]) && !empty($groupSurferRelations[$id]['is_owner']))) {
          $reference = clone $this->reference();
          $reference->setParameters(
            array(
              'command' => 'delete_group',
              'group_handle' => $group['handle']
            ),
            $this->owner->parameterGroup()
          );
          $links[$id]['delete'] = $reference->getRelative();
        }
        if ($this->showOwnGroups() && $mode != 'invitations' &&
            isset($groupSurferRelations[$id]) && !empty($groupSurferRelations[$id]['is_owner'])) {
          $reference = clone $this->reference();
          $reference->setParameters(
            array('command' => 'edit_group', 'group_handle' => $group['handle']),
            $this->owner->parameterGroup()
          );
          $links[$id]['edit']= $reference->getRelative();
        }
        if ($this->showOwnGroups() && $mode == 'invitations') {
          $reference = clone $this->reference();
          $reference->setParameters(
            array('command' => 'accept_invitation', 'group_handle' => $group['handle']),
            $this->owner->parameterGroup()
          );
          $links[$id]['accept_invitation']= $reference->getRelative();
          $reference = clone $this->reference();
          $reference->setParameters(
            array('command' => 'decline_invitation', 'group_handle' => $group['handle']),
            $this->owner->parameterGroup()
          );
          $links[$id]['decline_invitation']= $reference->getRelative();
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
    $page = $this->owner->parameters()->get('groups_page', 0);
    $groupSurferRelations = array();
    if ($this->showOwnGroups()) {
      $mode = $this->owner->parameters()->get('mode', NULL);
      if ($mode == 'invitations') {
        $filter = array(
          'surfer_id' => $this->currentSurferId(), 'surfer_status_pending' => 2
        );
      } else {
        $filter = array(
          'surfer_id' => $this->currentSurferId(), 'surfer_status_pending' => 0,
          'include_owner_status' => 1
        );
      }
      $this->groupSurferRelations()->load(
        $filter,
        $this->pagingItemsPerPage,
        ($page > 0) ? ($page - 1) * $this->pagingItemsPerPage : 0
      );
      $groupSurferRelations = $this->groupSurferRelations()->toArray();
      $groupsFilter = array('id' => array_keys($groupSurferRelations));
    } else {
      $groupsFilter = array('public' => 1);
    }
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
      if (!($mode == 'invitations' && $values['public'] == 0)) {
        $listData['data'][$key]['page_link'] = $this->owner->acommunityConnector()
          ->getGroupPageLink($values['handle']);
      }
      if (!$this->showOwnGroups()) {
        // the public state in public display mode is needless
        unset($listData['data'][$key]['public']);
      }
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