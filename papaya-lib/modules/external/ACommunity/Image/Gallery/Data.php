<?php
/**
 * Advanced community image gallery data class to handle all sorts of related data
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
 * Advanced community surfer gallery data class to handle all sorts of related data
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityImageGalleryData extends ACommunityUiContentData {

  /**
   * Ressource needs active surfer
   * @var boolean
   */
  protected $_ressourceNeedsActiveSurfer = FALSE;

  /**
   * Gallery database record
   *
   * @var ACommunityContentSurferGallery
   */
  protected $_gallery = NULL;

  /**
   * Surfer galleries database records
   * @var object
   */
  protected $_galleries = NULL;

  /**
   * Group database record
   * @var object
   */
  protected $_group = NULL;

  /**
   * Media db edit object
   * @var object
   */
  protected $_mediaDBEdit = NULL;

  /**
   * Status of group owner
   * @var boolean
   */
  protected $_surferIsGroupOwner = NULL;

  /**
   * Detects if the current active surfer is the owner of the selected group
   *
   * @return bolean
   */
  public function surferIsGroupOwner() {
    if (is_null($this->_surferIsGroupOwner)) {
      $ressource = $this->ressource();
      if ($ressource['type'] == 'group' && !empty($ressource['id'])) {
        $this->group()->load($ressource['id']);
        $group = $this->group()->toArray();
        if (isset($group['owner'])) {
          $this->_surferIsGroupOwner = $this->currentSurferId() == $group['owner'];
        }
      }
    }
    return $this->_surferIsGroupOwner;
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
      include_once(dirname(__FILE__).'/../../Content/Group.php');
      $this->_group = new ACommunityContentGroup();
      $this->_group->papaya($this->papaya());
    }
    return $this->_group;
  }

  /**
  * Access to the image gallery database record data
  *
  * @param ACommunityContentImageGallery $gallery
  * @return ACommunityContentImageGallery
  */
  public function gallery(ACommunityContentImageGallery $gallery = NULL) {
    if (isset($gallery)) {
      $this->_gallery = $gallery;
    } elseif (is_null($this->_gallery)) {
      include_once(dirname(__FILE__).'/../../Content/Image/Gallery.php');
      $this->_gallery = new ACommunityContentImageGallery();
      $this->_gallery->papaya($this->papaya());
    }
    return $this->_gallery;
  }

  /**
  * Access to the image galleries database records data
  *
  * @param ACommunityContentImageGalleries $galleries
  * @return ACommunityContentImageGalleries
  */
  public function galleries(ACommunityContentImageGalleries $galleries = NULL) {
    if (isset($galleries)) {
      $this->_galleries = $galleries;
    } elseif (is_null($this->_galleries)) {
      include_once(dirname(__FILE__).'/../../Content/Image/Galleries.php');
      $this->_galleries = new ACommunityContentImageGalleries();
      $this->_galleries->papaya($this->papaya());
    }
    return $this->_galleries;
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