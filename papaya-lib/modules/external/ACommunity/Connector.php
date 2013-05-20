<?php
/**
 * Advanced community connector
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
 * Advanced community connector
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityConnector extends base_connector {
  
  /**
   * Surfer deletion object
   * @var ACommunitySurferDeletion
   */
  protected $_surferDeletion = NULL;
  
  /**
   * Surfer deletion object
   * 
   * @param ACommunitySurferDeletion $deletion
   * @return ACommunitySurferDeletion
   */
  public function surferDeletion(ACommunitySurferDeletion $deletion = NULL) {
    if (isset($deletion)) {
      $this->_surferDeletion = $deletion;
    } elseif (is_null($this->_surferDeletion)) {
      include_once(dirname(__FILE__).'/Surfer/Deletion.php');
      $this->_surferDeletion = new ACommunitySurferDeletion();
    }
    return $this->_surferDeletion;
  }
  
  /**
   * Action dispatcher function to delete surfer dependend data
   * 
   * @param string $surferId
   */
  public function onDeleteSurfer($surferId) {
    $this->surferDeletion()->setDeletedSurferInComments($surferId);
    $this->surferDeletion()->deleteSurferGalleries($surferId);
  }
  
}
