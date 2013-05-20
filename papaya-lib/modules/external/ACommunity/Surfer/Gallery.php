<?php
/**
 * Advanced community gallery
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
require_once(dirname(__FILE__).'/../Ui/Content.php');

/**
 * Advanced community gallery
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunitySurferGallery extends ACommunityUiContent {

  /**
   * Comments data
   * @var ACommunityCommentsData
   */
  protected $_data = NULL;

  /**
   * Get/set comments data
   *
   * @param ACommunitySurferGalleryData $data
   * @return ACommunitySurferGalleryData
   */
  public function data(ACommunitySurferGalleryData $data = NULL) {
    if (isset($data)) {
      $this->_data = $data;
    } elseif (is_null($this->_data)) {
      include_once(dirname(__FILE__).'/Gallery/Data.php');
      $this->_data = new ACommunitySurferGalleryData();
      $this->_data->papaya($this->papaya());
      $this->_data->owner = $this;
    }
    return $this->_data;
  }
}