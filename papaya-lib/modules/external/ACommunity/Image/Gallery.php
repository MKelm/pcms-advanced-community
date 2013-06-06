<?php
/**
 * Advanced community image gallery
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
 * MediaImageGallery class to extend
 */
require_once(PAPAYA_INCLUDE_PATH.'modules/free/thumbs/Image/Gallery.php');

/**
 * Advanced community surfer gallery
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityImageGallery extends MediaImageGallery {

  /**
   * Id of current file id in enlarge view
   * @var string
   */
  public $currentFileId = NULL;

  /**
   * Comments data
   * @var ACommunityCommentsData
   */
  protected $_data = NULL;

  /**
   * Ui content object for further methods
   * @var ACommunityUiContent
   */
  protected $_uiContent = NULL;

  /**
   * Module object
   * @var object
   */
  public $module = NULL;

  /**
   * Check url file name in for page modules and return new url if the current file name is invalid
   *
   * @param base_content $pageModule
   * @param string $currentFileName
   * @param string $outputMode
   * @param string $pageNamePostfix
   * @param string $handle
   * @return string|FALSE
   */
  public function checkURLFileName(
           $pageModule, $currentFileName, $outputMode, $pageNamePostfix, $handle = NULL
         ) {
    return $this->uiContent()->checkURLFileName(
      $pageModule, $currentFileName, $outputMode, $pageNamePostfix, $handle
    );
  }

  /**
   * Initialize properties by module configuration data and data mode (all or teaser)
   */
  public function initialize($module, $data, $dataMode = 'all') {
    parent::initialize($module, $data, $dataMode);
    $command = $this->parameters()->get('command', NULL);
    if ($command == 'delete_image') {
      $fileId = $this->parameters()->get('id', NULL);
      if (!empty($fileId)) {
        $ressource = $this->ressource();
        if ($ressource->validSurfer === 'is_selected' || $ressource->validSurfer === 'is_owner' ||
            $this->data()->surferIsModerator()) {

          if ($this->data()->mediaDBEdit()->deleteFile($fileId)) {
            $folderId = $this->parameters()->get('folder_id', 0);
            if (!($folderId > 0)) {
              $lastChangeRessource = $ressource->type.'_gallery_images:folder_base:'.
                $ressource->type.'_'.$ressource->id;
            } else {
              $lastChangeRessource = $ressource->type.'_gallery_images:folder_'.$folderId.':'.
                $ressource->type.'_'.$ressource->id;
            }
            return $this->data()->setLastChangeTime($lastChangeRessource);
          }
        }
      }
      return FALSE;
    }
  }

  /**
   * Load gallery images / thumbnails by media db and folder properties
   */
  public function load() {
    parent::load();
    if ($this->_options['enlarge'] == 1 && count($this->_folder['files']) == 1) {
      $this->currentFileId = reset(array_keys($this->_folder['files']));
    }
  }

  /**
   * Create dom node structure of the given object and append it to the given xml
   * element node.
   *
   * @param PapayaXmlElement $parent
   */
  public function appendTo(PapayaXmlElement $parent) {
    if (isset($this->ressource()->id)) {
      parent::appendTo($parent);
    } else {
      $parent->appendElement(
        'message',
        array('type' => 'error', 'use-language-text' => 'yes'),
       'GROUP_GALLERY_ACCESS_DENIED'
      );
    }
  }

  /**
   * Append image or image thumbnail by current file id to parent element
   *
   * @param PapayaXmlElement $parent
   * @param integer $currentFileId
   * @param integer $fileOffset of current file in folder
   * @param boolean $thumbnail
   */
  protected function _appendImageTo(
              PapayaXmlElement $parent, $fileId, $fileOffset = 0, $thumbnail = FALSE
            ) {
    parent::_appendImageTo($parent, $fileId, $fileOffset, $thumbnail);
    if ($thumbnail == TRUE && $this->_options['lightbox'] == 1) {
      // file description for lighbox extension
      $fileDescription = !empty($this->_folder['translations'][$fileId]['file_description']) ?
        $this->_folder['translations'][$fileId]['file_description'] : NULL;
      $parent->appendElement(
        'image-description',
        array(),
        strip_tags(str_replace(array("\r\n", "\r", "\n"), " ", $fileDescription))
      );
      // image extras link to get comments in lightbox extension
      $link = $this->acommunityConnector()->getCommentsPageLink(
        $this->data()->languageId, 'image', $fileId
      );
      $parent->appendElement(
        'image-extras-link', array(), PapayaUtilStringXml::escape($link)
      );
    }
    $ressource = $this->ressource();
    if ($thumbnail == TRUE &&
        ($ressource->validSurfer === 'is_selected' || $ressource->validSurfer === 'is_owner' ||
         $this->data()->surferIsModerator())) {
      $reference = clone $this->reference();
      $reference->setParameters(
        array(
          'offset' => $this->parameters()->get('offset', NULL),
          'command' => 'delete_image',
          'id' => $fileId
        ),
        $this->parameterGroup()
      );
      $parent->appendElement('delete-image', array('href' => $reference->getRelative()));
    }
  }

  /**
   * Get/set gallery data
   *
   * @param ACommunitySurferGalleryData $data
   * @return ACommunitySurferGalleryData
   */
  public function data(ACommunitySurferGalleryData $data = NULL) {
    if (isset($data)) {
      $this->_data = $data;
    } elseif (is_null($this->_data)) {
      include_once(dirname(__FILE__).'/Gallery/Data.php');
      $this->_data = new ACommunityImageGalleryData();
      $this->_data->papaya($this->papaya());
      $this->_data->owner = $this;
    }
    return $this->_data;
  }

  /**
   * Get/set ui content object
   *
   * @param ACommunityUiContent $content
   * @return ACommunityUiContent
   */
  public function uiContent(ACommunityUiContent $content = NULL) {
    if (isset($content)) {
      $this->_uiContent = $content;
    } elseif (is_null($this->_uiContent)) {
      include_once(dirname(__FILE__).'/../Ui/Content.php');
      $this->_uiContent = new ACommunityUiContent();
      $this->_uiContent->papaya($this->papaya());
      $this->_uiContent->parameterGroup($this->parameterGroup());
      $this->_uiContent->data($this->data());
      $this->_uiContent->module = $this->module;
    }
    return $this->_uiContent;
  }

  /**
   * Get/set community connector
   *
   * @param object $connector
   * @return object
   */
  public function communityConnector(connector_surfers $connector = NULL) {
    return $this->uiContent()->communityConnector($connector);
  }

  /**
   * Get/set advanced community connector
   *
   * @param object $connector
   * @return object
   */
  public function acommunityConnector(ACommunityConnector $connector = NULL) {
    return $this->uiContent()->acommunityConnector($connector);
  }

  /**
   * Get / set ressource of current request.
   *
   * @param ACommunityUiContentRessource $ressource
   * @return ACommunityUiContentRessource
   */
  public function ressource(ACommunityUiContentRessource $ressource = NULL) {
    return $this->uiContent()->ressource($ressource);
  }
}