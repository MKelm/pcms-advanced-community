<?php
/**
 * Advanced community image gallery folders data class to handle all sorts of related data
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
 * Advanced community image gallery upload data class to handle all sorts of related data
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityImageGalleryFoldersData extends ACommunityImageGalleryData {

  /**
   * Loaded folders
   * @var array
   */
  public $folders = NULL;

  /**
   * Id of base folder to use as parent id for sub folders
   * @var integer
   */
  protected $_baseFolderId = NULL;

  /**
   * Loaded command links
   * @var array
   */
  public $commandLinks = NULL;

  /**
   * Get base folder id
   *
   * @return integer
   */
  public function getBaseFolderId() {
    if (is_null($this->_baseFolderId)) {
      $ressource = $this->ressource('ressource');
      $this->galleries()->load(
        array(
          'ressource_type' => $ressource->type,
          'ressource_id' => $ressource->id,
          'parent_folder_id' => 0
        ), 1
      );
      $gallery = reset($this->galleries()->toArray());
      $this->_baseFolderId = (int)$gallery['folder_id'];
    }
    return $this->_baseFolderId;
  }

  /**
   * Load folders by ressource with internal links
   */
  public function loadFolders() {
    $this->folders = array();
    $ressource = $this->ressource('ressource');
    $this->galleries()->load(
      array('ressource_type' => $ressource->type, 'ressource_id' => $ressource->id)
    );
    $galleries = $this->galleries()->toArray();
    foreach ($galleries as $gallery) {
      $this->folders[$gallery['folder_id']] = array();
    }

    $command = $this->owner->parameters()->get('command', NULL);
    $folderId = $this->owner->parameters()->get('folder_id', NULL);
    if (!isset($command) && isset($folderId)) {
      $selectedFolderId = (int)$folderId;
    } else {
      $selectedFolderId = 0;
    }

    $folders = $this->mediaDBEdit()->getFolders($this->languageId, array_keys($this->folders));
    if (!empty($folders)) {
      foreach ($folders as $folder) {
        $reference = clone $this->reference();
        $selected = 0;
        if ($folder['folder_name'] == $ressource->type.'_'.$ressource->id) {
          $name = $this->captions['base_folder'];
          $href = $reference->getRelative();
          if ($selectedFolderId == 0) {
            $selected = 1;
          }
        } else {
          $name = $folder['folder_name'];
          $reference->setParameters(
            array('folder_id' => $folder['folder_id']), $this->owner->parameterGroup()
          );
          $href = $reference->getRelative();
          if ($selectedFolderId == $folder['folder_id']) {
            $selected = 1;
          }
        }
        $this->folders[$folder['folder_id']] = array(
          'name' => $name, 'href' => $href, 'selected' => $selected
        );
      }
    } else {
      $this->folders = NULL;
    }
  }

  /**
   * Load command links by loaded folders
   */
  public function loadCommandLinks() {
    $this->commandLinks = array();
    $deleteFolderLinks = array(
      'caption' => $this->captions['delete_folder'],
      'href' => array()
    );
    foreach ($this->folders as $folderId => $folder) {
      if ($folder['name'] != $this->captions['base_folder']) {
        $reference = clone $this->reference();
        $reference->setParameters(
          array('command' => 'delete_folder', 'folder_id' => $folderId),
          $this->owner->parameterGroup()
        );
        $deleteFolderLinks['href'][$folderId] = $reference->getRelative();
      }
    }
    $this->commandLinks['delete_folder'] = $deleteFolderLinks;
    $reference = clone $this->reference();
    $reference->setParameters(
      array('command' => 'add_folder'), $this->owner->parameterGroup()
    );
    $this->commandLinks['add_folder'] = array(
      'caption' => $this->captions['add_folder'],
      'href' => $reference->getRelative()
    );
  }
}