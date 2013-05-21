<?php
/**
 * Advanced community surfer gallery
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
class ACommunitySurferGallery extends MediaImageGallery {

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
    $fileId = $this->parameters()->get('id', NULL);
    if ($command == 'delete_image' && !empty($fileId)) {
      $this->data()->mediaDBEdit()->deleteFile($fileId);
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
    if ($thumbnail == TRUE && $this->data()->ressourceIsActiveSurfer) {
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
      $this->_data = new ACommunitySurferGalleryData();
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
}