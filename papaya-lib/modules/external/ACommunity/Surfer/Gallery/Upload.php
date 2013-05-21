<?php
/**
 * Advanced community surfer gallery upload
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
 * Advanced community surfer gallery upload
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunitySurferGalleryUpload extends ACommunityUiContent {

  /**
   * Ui content surfer gallery upload dialog
   * @var ACommunityUiContentSurferGalleryUploadDialog
   */
  protected $_uiContentUploadDialog = NULL;

  /**
   * Get/set surfer gallery upload data
   *
   * @param ACommunitySurferGalleryUploadData $data
   * @return ACommunitySurferGalleryUploadData
   */
  public function data(ACommunitySurferGalleryUploadData $data = NULL) {
    if (isset($data)) {
      $this->_data = $data;
    } elseif (is_null($this->_data)) {
      include_once(dirname(__FILE__).'/Upload/Data.php');
      $this->_data = new ACommunitySurferGalleryUploadData();
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
    $upload = $parent->appendElement('acommunity-surfer-gallery-upload');
    $ressource = $this->data()->ressource();
    if (!empty($ressource)) {
      $this->uiContentUploadDialog()->appendTo($upload);
      $errorMessage = $this->uiContentUploadDialog()->errorMessage();
      if (!empty($errorMessage)) {
        $upload->appendElement(
          'dialog-message', array('type' => 'error'), $errorMessage
        );
      }
    }
  }

  /**
  * Access to the ui content surfer gallery upload dialog control
  *
  * @param ACommunityUiContentSurferGalleryUploadDialog $uiContentUploadDialog
  * @return ACommunityUiContentSurferGalleryUploadDialog
  */
  public function uiContentUploadDialog(
           ACommunityUiContentSurferGalleryUploadDialog $uiContentUploadDialog = NULL
         ) {
    if (isset($uiContentUploadDialog)) {
      $this->_uiContentUploadDialog = $uiContentUploadDialog;
    } elseif (is_null($this->_uiContentUploadDialog)) {
      include_once(dirname(__FILE__).'/../../Ui/Content/Surfer/Gallery/Upload/Dialog.php');
      $this->_uiContentUploadDialog = new ACommunityUiContentSurferGalleryUploadDialog();
      $this->_uiContentUploadDialog->data($this->data());
      $this->_uiContentUploadDialog->parameters($this->parameters());
      $this->_uiContentUploadDialog->parameterGroup($this->parameterGroup());
    }
    return $this->_uiContentUploadDialog;
  }

}
