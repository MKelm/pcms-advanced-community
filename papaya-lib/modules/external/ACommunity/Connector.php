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
   * Page deletion object
   * @var ACommunityPageDeletion
   */
  protected $_pageDeletion = NULL;
  
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
   * Page deletion object
   * 
   * @param ACommunityPageDeletion $deletion
   * @return ACommunityPageDeletion
   */
  public function pageDeletion(ACommunityPageDeletion $deletion = NULL) {
    if (isset($deletion)) {
      $this->_pageDeletion = $deletion;
    } elseif (is_null($this->_pageDeletion)) {
      include_once(dirname(__FILE__).'/Page/Deletion.php');
      $this->_pageDeletion = new ACommunityPageDeletion();
    }
    return $this->_pageDeletion;
  }
  
  /**
   * Action dispatcher function to delete surfer dependend data
   * 
   * @param string $surferId
   */
  public function onDeleteSurfer($surferId) {
    $this->surferDeletion()->setDeletedSurferInPageComments($surferId);
    $this->surferDeletion()->deleteSurferComments($surferId);
    $this->surferDeletion()->deleteSurferGalleries($surferId);
  }
  
  /**
   * Action dispatcher function to delete pages' dependend data
   * 
   * Note: You have to add an action dispatcher call in base_topic_edit->destroy() 
   * to make onDeletePages available for dispatching. See base_topic_edit_destroy_replacement.txt 
   * for a replacement of the whole destroy() method which contains a valid call.
   * 
   * @param array $pageIds
   */
  public function onDeletePages($pageIds) {
    $this->pageDeletion()->deletePageComments($pageIds);
  }
  
}
