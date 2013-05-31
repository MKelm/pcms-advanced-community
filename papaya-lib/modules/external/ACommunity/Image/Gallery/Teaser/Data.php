<?php
/**
 * Advanced community image gallery teaser data class to handle all sorts of related data
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

// Gallery data object
require_once(dirname(__FILE__).'/../Data.php');

/**
 * Advanced community image gallery teaser data class to handle all sorts of related data
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityImageGalleryTeaserData extends ACommunityImageGalleryData {

  /**
   * Thumbnail amount
   * @var integer
   */
  public $thumbnailAmount = 4;

  /**
   * Thumbnail size
   * @var integer
   */
  public $thumbnailSize = 100;

  /**
   * Thubmnail resize mode
   * @var string
   */
  public $thumbnailResizeMode = 'mincrop';

  /**
   * Flag of surfer group access for group ressources
   * @var boolean
   */
  public $surferHasGroupAccess = FALSE;

  /**
   * Set data by plugin object
   *
   * @param array $data
   * @param array $captionNames
   * @param array $messageNames
   */
  public function setPluginData($data, $captionNames = array(), $messageNames = array()) {
    $this->thumbnailAmount = (int)$data['thumbnail_amount'];
    $this->thumbnailSize = (int)$data['thumbnail_size'];
    $this->thumbnailResizeMode = trim($data['thumbnail_resize_mode']);
    parent::setPluginData($data, $captionNames, $messageNames);
  }
}