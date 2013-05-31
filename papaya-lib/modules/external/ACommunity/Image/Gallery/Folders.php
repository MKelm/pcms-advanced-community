<?php
/**
 * Advanced community image gallery folders
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
require_once(dirname(__FILE__).'/../../Ui/Content.php');

/**
 * Advanced community image gallery folders
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityImageGalleryFolders extends ACommunityUiContent {

  /**
   * Ui content surfer gallery folder dialog
   * @var ACommunityUiContentSurferGalleryFolderDialog
   */
  protected $_uiContentFolderDialog = NULL;

  /**
   * Gallery deletion object
   * @var ACommunityImageGalleryDeletion
   */
  protected $_galleryDeletion = NULL;

  /**
   * Get/set image gallery folders data
   *
   * @param ACommunityImageGalleryFoldersData $data
   * @return ACommunityImageGalleryFoldersData
   */
  public function data(ACommunityImageGalleryFoldersData $data = NULL) {
    if (isset($data)) {
      $this->_data = $data;
    } elseif (is_null($this->_data)) {
      include_once(dirname(__FILE__).'/Folders/Data.php');
      $this->_data = new ACommunityImageGalleryFoldersData();
      $this->_data->papaya($this->papaya());
      $this->_data->owner = $this;
    }
    return $this->_data;
  }

  /**
   * Perform commands for gallery owners only
   */
  protected function _performCommands() {
    $command = $this->parameters()->get('command', NULL);
    switch ($command) {
      case 'add_folder':
        $dom = new PapayaXmlDocument();
        $dom->appendElement('dialog');
        $this->uiContentFolderDialog()->appendTo($dom->documentElement);
        $removeDialog = $this->parameters()->get('remove_dialog', 0);
        if (empty($removeDialog)) {
          $xml = '';
          foreach ($dom->documentElement->childNodes as $node) {
            $xml .= $node->ownerDocument->saveXml($node);
          }
          $galleryFolders->appendXml($xml);
          $errorMessage = $this->uiContentFolderDialog()->errorMessage();
          if (!empty($errorMessage)) {
            $galleryFolders->appendElement(
              'dialog-message', array('type' => 'error'), $errorMessage
            );
          }
        }
        break;
      case 'delete_folder':
        $folderId = $this->parameters()->get('folder_id', NULL);
        if (!empty($folderId)) {
          if ($this->galleryDeletion()->deleteGalleryByFolderId($folderId)) {
            $ressource = $this->data()->ressource('ressource');
            return $this->data()->setLastChangeTime(
              $ressource->type.'_gallery_folders:'.$ressource->type.'_'.$ressource->id
            );
          }
        }
        break;
      default:
        if (!empty($command)) {
          return FALSE;
        }
        break;
    }
  }

  /**
  * Create dom node structure of the given object and append it to the given xml
  * element node.
  *
  * @param PapayaXmlElement $parent
  */
  public function appendTo(PapayaXmlElement $parent) {
    $galleryFolders = $parent->appendElement('acommunity-image-gallery-folders');
    $ressource = $this->data()->ressource('ressource');
    if (isset($ressource->id) &&
      ($ressource->type != 'group' || $this->data()->surferHasGroupAccess())) {

      if (($ressource->type == 'surfer' && $ressource->isActiveSurfer) ||
          ($ressource->type == 'group' &&
           $this->data()->surferHasStatus($ressource->id, 'is_owner', 1))) {
        $this->_performCommands();
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

      if (($ressource->type == 'surfer' && $ressource->isActiveSurfer) ||
          ($ressource->type == 'group' &&
           $this->data()->surferHasStatus($ressource->id, 'is_owner', 1))) {
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
  * Access to the ui content image gallery folder dialog control
  *
  * @param ACommunityUiContentImageGalleryFolderDialog $uiContentFolderDialog
  * @return ACommunityUiContentImageGalleryFolderDialog
  */
  public function uiContentFolderDialog(ACommunityUiContentImageGalleryFolderDialog $uiContentFolderDialog = NULL) {
    if (isset($uiContentFolderDialog)) {
      $this->_uiContentFolderDialog = $uiContentFolderDialog;
    } elseif (is_null($this->_uiContentFolderDialog)) {
      include_once(dirname(__FILE__).'/../../Ui/Content/Image/Gallery/Folder/Dialog.php');
      $this->_uiContentFolderDialog = new ACommunityUiContentImageGalleryFolderDialog(
        $this->data()->gallery()
      );
      $this->_uiContentFolderDialog->data($this->data());
      $this->_uiContentFolderDialog->parameters($this->parameters());
      $this->_uiContentFolderDialog->parameterGroup($this->parameterGroup());
    }
    return $this->_uiContentFolderDialog;
  }

  /**
  * Set/get image gallery deletion object
  *
  * @return ACommunityImageGalleryDeletion
  */
  public function galleryDeletion(ACommunityImageGalleryDeletion $galleryDeletion = NULL) {
    if (isset($galleryDeletion)) {
      $this->_galleryDeletion = $galleryDeletion;
    } elseif (is_null($this->_galleryDeletion)) {
      include_once(dirname(__FILE__).'/Deletion.php');
      $this->_galleryDeletion = new ACommunityImageGalleryDeletion();
      $this->_galleryDeletion->papaya($this->papaya());
    }
    return $this->_galleryDeletion;
  }
}