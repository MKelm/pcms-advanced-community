<?php
/**
 * Advanced community filter text extended
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
 * Advanced community  filter text extended
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityFilterTextExtended extends PapayaFilterText {

  /**
  * Pattern to extract urls
  *
  * Simplified pattern of orginal LinkifyURL-pattern to support not-delimeted urls only
  *
  * @var string
  */
  protected $_urlPattern = '/# Rev:20100913_0900 github.com\/jmrware\/LinkifyURL
      # Alternative 5: URL not delimited by (), [], {} or <>.
      ( \b                     # $1: Other non-delimited URL.
        (?:ht|f)tps?:\/\/      # Required literal http, https, ftp or ftps prefix.
        [a-z0-9\-._~!$\'()*+,;=:\/?#[\]@%]+ # All URI chars except "&" (normal*).
        (?:                    # Either on a "&" or at the end of URI.
          (?!                  # Allow a "&" char only if not start of an...
            &(?:gt|\#0*62|\#x0*3e);                  # HTML ">" entity, or
          | &(?:amp|apos|quot|\#0*3[49]|\#x0*2[27]); # a [&\'"] entity if
            [.!&\',:?;]?        # followed by optional punctuation then
            (?:[^a-z0-9\-._~!$&\'()*+,;=:\/?#[\]@%]|$)  # a non-URI char or EOS.
          ) &                  # If neg-assertion true, match "&" (special).
          [a-z0-9\-._~!$\'()*+,;=:\/?#[\]@%]* # More non-& URI chars (normal*).
        )*                     # Unroll-the-loop (special normal*)*.
        [a-z0-9\-_~$()*+=\/#[\]@%]  # Last char can\'t be [.!&\',;:?]
      )                        # End $1. Other non-delimited URL.
    /imx';

  /**
   * Length of url to show
   * @var integer
   */
  protected $_urlLength = 80;

  /**
   * Media db edit object
   * @var object
   */
  protected $_mediaDBEdit = NULL;

  /**
   * Advanced Community connector
   * @var ACommunityConnector
   */
  protected $_acommunityConnector = NULL;

  /**
   * Text options
   * @var array
   */
  protected $_textOptions = NULL;

  /**
   * A ressource string to indentify image thumbnails by ressource.
   * You can use properties from the content ressource object to get a valid ressource string like:
   * "ressourceType_ressourceId"
   * Ressources with the same type should have an additional prefix in the ressource string,
   * e.g. 'messages:surfer_suferId' / 'comments:surfer_surferId'
   * @var string
   */
  protected $_ressource = NULL;

  /**
   * List with thumbnail links
   * @var array
   */
  protected $_thumbnailLinks = array();

  /**
   * Use session values to save thumbnail links' image urls and media file ids
   * @var PapayaSession
   */
  protected $_session = NULL;

  /**
   * Create object and store options to match additional character groups
   *
   * @param integer $options
   */
  public function __construct($options, $ressource = NULL, $session = NULL) {
    parent::__construct($options);
    $this->_ressource = $ressource;
    $this->_session = $session;
    $this->_textOptions = $this->acommunityConnector()->getTextOptions();
  }

  /**
   * Get thumbnail links
   *
   * @return array
   */
  public function thumbnailLinks() {
    return $this->_thumbnailLinks;
  }

  /**
  * The filter function is used to read an input value if it is valid.
  *
  * @param string $value
  * @return string
  */
  public function filter($value) {
    $value = parent::filter($value);
    $result = sprintf('<text-raw>%s</text-raw>', PapayaUtilStringXml::escape($value));
    $result .= sprintf(
      '<text>%s</text>',
      preg_replace_callback(
        $this->_urlPattern,
        array($this, 'callbackReplaceUrls'),
        base_object::getXHTMLString($value, TRUE)
      )
    );

    if (count($this->_thumbnailLinks) > 0) {
      $result .= '<text-thumbnail-links>';
      foreach ($this->_thumbnailLinks as $link) {
        $result .= $link;
      }
      $result .= '</text-thumbnail-links>';
    }
    return $result;
  }

  /**
   * Replace urls with links and replace images with embedded thumbnails
   *
   * @param array $match
   * @return string
   */
  public function callbackReplaceUrls($match) {
    if ($this->_textOptions['thumbnails'] == 1) {
      $imagePattern = '~.jpg|.jpeg|.gif|.png~i';
      preg_match($imagePattern, $match[1], $imageMatches);

      if (!empty($imageMatches[0])) {
        $this->addThumbnailLink($match[1]);
      }
    }
    $urlToShow = $match[1];
    if (strlen($urlToShow) > $this->_urlLength) {
      $urlToShow = substr($match[1], 0, $this->_urlLength - 3).'...';
    }
    return sprintf('<a href="%s">%s</a>', $match[1], $urlToShow);
  }

  /**
   * An method to add a thumbnail by an image url for the messages/comments output.
   * The comments page supports an ajax request for single thumbnail link requests used
   * by the javascript extension for dynamic url detection on user input. Session values are
   * supported to create one media file per image url only.
   *
   * @param string $imageUrl
   */
  public function addThumbnailLink($imageUrl) {
    // session values detection
    if (isset($this->_session->values['thumbnail_link_media_ids'][$imageUrl])) {
      $fileId = $this->_session->values['thumbnail_link_media_ids'][$imageUrl];
      $detected = $fileId;
    }
    if (!isset($fileId)) {
      $contents = file_get_contents($imageUrl);
      if (!empty($contents)) {
        $path = tempnam('/tmp', 'acommunity-text-thumbnail-');
        $handle = fopen($path, "a");
        fwrite($handle, $contents);
        fclose($handle);
        if (getimagesize($path) != FALSE) {
          if (!empty($this->_ressource)) {
            $fileName = $path.':'.$this->_ressource;
          } else {
            $fileName = $path;
          }
          $fileId = $this->mediaDbEdit()->addFile(
            $path, $fileName, $this->_textOptions['thumbnails_folder'], ''
          );
          // session values insert
          if (!is_array($this->_session->values['thumbnail_link_media_ids'])) {
            $this->_session->values['thumbnail_link_media_ids'] = array();
          }
          $this->_session->values['thumbnail_link_media_ids'] = array_merge(
            $this->_session->values['thumbnail_link_media_ids'], array($imageUrl => $fileId)
          );
        }
        if (file_exists($path)) {
          unlink($path);
        }
      }
    }
    if (isset($fileId)) {
      $this->_thumbnailLinks[] = sprintf(
        '<a href="%s" title="%s">%s</a>',
        $imageUrl, $imageUrl,
        PapayaUtilStringPapaya::getImageTag(
          $fileId, $this->_textOptions['thubmnails_size'], $this->_textOptions['thubmnails_size'],
          '', $this->_textOptions['thubmnails_resize_mode']
        )
      );
    }
    if (isset($detected) && $fileId == $detected) {
      $ids = $this->_session->values['thumbnail_link_media_ids'];
      unset($ids[$imageUrl]);
      $this->_session->values['thumbnail_link_media_ids'] = $ids;
    }
  }

  /**
   * Media DB Edit to save image thumbnails for links
   *
   * @param base_mediadb_edit $mediaDBEdit
   * @return base_mediadb_edit
   */
  public function mediaDBEdit(base_mediadb_edit $mediaDBEdit = NULL) {
    if (isset($mediaDBEdit)) {
      $this->_mediaDBEdit = $mediaDBEdit;
    } elseif (is_null($this->_mediaDBEdit)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_mediadb_edit.php');
      $this->_mediaDBEdit = new base_mediadb_edit();
    }
    return $this->_mediaDBEdit;
  }

  /**
   * Get/set advanced community connector
   *
   * @param object $connector
   * @return object
   */
  public function acommunityConnector(ACommunityConnector $connector = NULL) {
    if (isset($connector)) {
      $this->_acommunityConnector = $connector;
    } elseif (is_null($this->_acommunityConnector)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
      $this->_acommunityConnector = base_pluginloader::getPluginInstance(
        '0badeb14ea2d41d5bcfd289e9d190534', $this
      );
    }
    return $this->_acommunityConnector;
  }
}