<?php
/**
 * Advanced community surfer gallery folders
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
require_once(dirname(__FILE__).'/../../Ui/Content/Object.php');

/**
 * Advanced community surfer gallery folders
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunitySurferGalleryFolders extends ACommunityUiContentObject {
    
  /**
   * Surfer gallery folders data
   * @var ACommunitySurferGalleryFoldersData
   */
  protected $_data = NULL;
  
  /**
   * Ui content surfer gallery folder dialog
   * @var ACommunityUiContentSurferGalleryFolderDialog
   */
  protected $_uiContentFolderDialog = NULL;

  /**
   * Gallery deletion object
   * @var ACommunitySurferGalleryDeletion
   */
  protected $_galleryDeletion = NULL;
  
  /**
   * Get/set surfer gallery folders data
   *
   * @param ACommunitySurferGalleryFoldersData $data
   * @return ACommunitySurferGalleryFoldersData
   */
  public function data(ACommunitySurferGalleryFoldersData $data = NULL) {
    if (isset($data)) {
      $this->_data = $data;
    } elseif (is_null($this->_data)) {
      include_once(dirname(__FILE__).'/Folders/Data.php');
      $this->_data = new ACommunitySurferGalleryFoldersData();
      $this->_data->papaya($this->papaya());
      $this->_data->owner = $this;
    }
    return $this->_data;
  }
  
  /**
  * Create dom node structure of the given object and append it to the given xml
  * element node.
  *
  * @param PapayaXmlElement $parent
  */
  public function appendTo(PapayaXmlElement $parent) {
    $galleryFolders = $parent->appendElement('acommunity-surfer-gallery-folders');
    $ressource = $this->data()->ressource();
    if (!empty($ressource)) {
      
      if ($this->data()->ressourceIsActiveSurfer) {
        $command = $this->parameters()->get('command', NULL);
        switch ($command) {
          case 'add_folder':
            $this->uiContentFolderDialog()->appendTo($galleryFolders);
            $errorMessage = $this->uiContentFolderDialog()->errorMessage();
            if (!empty($errorMessage)) {
              $galleryFolders->appendElement(
                'dialog-message', array('type' => 'error'), $errorMessage
              );
            }
            break;
          case 'delete_folder':
            $folderId = $this->parameters()->get('folder_id', NULL);
            if (!empty($folderId)) {
              $this->galleryDeletion()->deleteSurferGalleryByFolderId($folderId);
            }
            break;
        }
      }
        
      $this->data()->loadFolders();
      $folders = $galleryFolders->appendElement('folders');
      foreach ($this->data()->folders as $id => $folder) {
        $folders->appendElement(
          'folder', 
          array(
            'id' => $id,
            'name' => $folder['name'], 
            'href' => PapayaUtilStringXml::escapeAttribute($folder['href']),
            'selected' => $folder['selected']
          )
        );
      }
      
      if ($this->data()->ressourceIsActiveSurfer) {
        $this->data()->loadCommandLinks();
        $commandLinks = $galleryFolders->appendElement('command-links');
        foreach ($this->data()->commandLinks as $command => $link) {
          $href = NULL;
          if (is_array($link['href'])) {
            foreach ($link['href'] as $folderId => $linkHref) {
              $commandLinks->appendElement(
                'command-link', 
                array('name' => $command, 'caption' => $link['caption'], 'folder_id' => $folderId),
                PapayaUtilStringXml::escape($linkHref)
              );
            }
          } else {
            $commandLinks->appendElement(
              'command-link', 
              array('name' => $command, 'caption' => $link['caption']),
              PapayaUtilStringXml::escape($link['href'])
            );
          }
        }
      }
    }
  }
  
  /**
  * Access to the ui content surfer gallery folder dialog control
  *
  * @param ACommunityUiContentSurferGalleryFolderDialog $uiContentFolderDialog
  * @return ACommunityUiContentSurferGalleryFolderDialog
  */
  public function uiContentFolderDialog(ACommunityUiContentSurferGalleryFolderDialog $uiContentFolderDialog = NULL) {
    if (isset($uiContentFolderDialog)) {
      $this->_uiContentFolderDialog = $uiContentFolderDialog;
    } elseif (is_null($this->_uiContentFolderDialog)) {
      include_once(dirname(__FILE__).'/../../Ui/Content/Surfer/Gallery/Folder/Dialog.php');
      $this->_uiContentFolderDialog = new ACommunityUiContentSurferGalleryFolderDialog(
        $this->data()->gallery()
      );
      $this->_uiContentFolderDialog->data($this->data());
      $this->_uiContentFolderDialog->parameters($this->parameters());
      $this->_uiContentFolderDialog->parameterGroup($this->parameterGroup());
    }
    return $this->_uiContentFolderDialog;
  }

  /**
  * Set/get gallery deletion object
  *
  * @return ACommunitySurferGalleryDeletion
  */
  public function galleryDeletion(ACommunitySurferGalleryDeletion $galleryDeletion = NULL) {
    if (isset($galleryDeletion)) {
      $this->_galleryDeletion = $galleryDeletion;
    } elseif (is_null($this->_galleryDeletion)) {
      include_once(dirname(__FILE__).'/Deletion.php');
      $this->_galleryDeletion = new ACommunitySurferGalleryDeletion();
      $this->_galleryDeletion->papaya($this->papaya());
    }
    return $this->_galleryDeletion;
  }
  
}
