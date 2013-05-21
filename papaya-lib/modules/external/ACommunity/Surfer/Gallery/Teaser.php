<?php
/**
 * Advanced community surfer gallery teaser
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
 * Advanced community surfer gallery teaser
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunitySurferGalleryTeaser extends ACommunityUiContent {

  /**
   * Get/set surfer gallery teaser data
   *
   * @param ACommunitySurferGalleryTeaserData $data
   * @return ACommunitySurferGalleryTeaserData
   */
  public function data(ACommunitySurferGalleryTeaserData $data = NULL) {
    if (isset($data)) {
      $this->_data = $data;
    } elseif (is_null($this->_data)) {
      include_once(dirname(__FILE__).'/Teaser/Data.php');
      $this->_data = new ACommunitySurferGalleryTeaserData();
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
    $galleryTeaser = $parent->appendElement('acommunity-surfer-gallery-teaser');
    $ressource = $this->data()->ressource();
    if (!empty($ressource)) {
      $this->data()->galleries()->load(array('surfer_id' => $ressource['id'], 'parent_folder_id' => 0));
      $gallery = reset($this->data()->galleries()->toArray());
      $images = NULL;
      if (!empty($gallery)) {
        $files = $this->data()->mediaDB()->getFiles($gallery['folder_id'], $this->data()->thumbnailAmount);
        if (!empty($files)) {
          foreach ($files as $file) {
            include_once(PAPAYA_INCLUDE_PATH.'system/base_thumbnail.php');
            $thumbnail = new base_thumbnail;
            $images[] = 'media.thumb.'.$thumbnail->getThumbnail(
              $file['file_id'], NULL, $this->data()->thumbnailSize, $this->data()->thumbnailSize,
              $this->data()->thumbnailResizeMode
            );
          }
        }
      }
      if (empty($images) && $this->data()->ressourceIsActiveSurfer) {
        $galleryTeaser->appendElement(
          'add-new-images-link',
          array('href' => $this->acommunityConnector()->getSurferGalleryPageLink($ressource['id'])),
          $this->data()->captions['add_new_images_link']
        );
      } else {
        $galleryImages = $galleryTeaser->appendElement('images');
        foreach ($images as $image) {
          $galleryImages->appendElement('image', array('src' => $image));
        }
        $galleryTeaser->appendElement(
          'more-images-link',
          array('href' => $this->acommunityConnector()->getSurferGalleryPageLink($ressource['id'])),
          $this->data()->captions['more_images_link']
        );
      }
    }
  }
}
