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
   * List with video links
   * @var array
   */
  protected $_videoLinks = array();

  /**
   * Use session values to save thumbnail links' image urls and media file ids
   * @var PapayaSession
   */
  protected $_session = NULL;

  /**
   * A session ident to get session values from, a md5 hash of a special ressource definition
   * e.g. "request_the_special_content:surfer_SURFERID"
   */
  protected $_sessionIdentThumbnailLinks = NULL;

  /**
   * A session ident to get session values from, a md5 hash of a special ressource definition
   * e.g. "request_the_special_content:surfer_SURFERID"
   */
  protected $_sessionIdentVideoLinks = NULL;

  /**
   * Create object and store options to match additional character groups
   *
   * @param integer $options
   */
  public function __construct(
                    $options, $ressource = NULL, $session = NULL,
                    $sessionIdentThumbnailLinks = NULL, $sessionIdentVideoLinks = NULL
                  ) {
    parent::__construct($options);
    $this->_ressource = $ressource;
    $this->_session = $session;
    $this->_sessionIdentThumbnailLinks = $sessionIdentThumbnailLinks;
    $this->_sessionIdentVideoLinks = $sessionIdentVideoLinks;
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
   * Get video links
   *
   * @return array
   */
  public function videoLinks() {
    return $this->_videoLinks;
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
    if (!empty($this->_session->values[$this->_sessionIdentThumbnailLinks])) {
      // add the rest of all thumbnail links from session to thumbnail links array, final run
      $sessionValues = $this->_session->values[$this->_sessionIdentThumbnailLinks];
      $this->_thumbnailLinks = array_merge(
        $this->_thumbnailLinks, array_values($sessionValues)
      );
      // remove all session values left
      $this->_removeSessionValueLink(TRUE, TRUE, 'thumbnail_links');
    }
    if (count($this->_thumbnailLinks) > 0) {
      $result .= '<text-thumbnail-links>';
      foreach ($this->_thumbnailLinks as $link) {
        $result .= $link;
      }
      $result .= '</text-thumbnail-links>';
    }
    if (!empty($this->_session->values[$this->_sessionIdentVideoLinks])) {
      // add the rest of all video links from session to video links array, final run
      $sessionValues = $this->_session->values[$this->_sessionIdentVideoLinks];
      $this->_videoLinks = array_merge(
        $this->_videoLinks, array_values($sessionValues)
      );
      // remove all session values left
      $this->_removeSessionValueLink(TRUE, TRUE, 'video_links');
    }
    if (count($this->_videoLinks) > 0) {
      $result .= '<text-video-links>';
      foreach ($this->_videoLinks as $link) {
        $result .= $link;
      }
      $result .= '</text-video-links>';
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
    if ($this->_textOptions['thumbnails'] == 1 && $this->_sessionIdentThumbnailLinks != NULL) {
      $imagePattern = '~.jpg|.jpeg|.gif|.png~i';
      preg_match($imagePattern, $match[1], $imageMatches);
      if (!empty($imageMatches[0])) {
        $this->addThumbnailLink($match[1], TRUE);
      }
    }
    if ($this->_textOptions['videos'] == 1 && $this->_sessionIdentVideoLinks != NULL) {
      $this->addVideoLink($match[1], TRUE);
    }
    $urlToShow = $match[1];
    if (strlen($urlToShow) > $this->_urlLength) {
      $urlToShow = substr($match[1], 0, $this->_urlLength - 3).'...';
    }
    return sprintf('<a href="%s">%s</a>', $match[1], $urlToShow);
  }

  /**
   * An method to add a thumbnail by an image url for the messages/comments output.
   * The comments/messages page supports an ajax request for single thumbnail link requests used
   * by the javascript extension for dynamic url detection on user input. Session values are
   * supported to create one media file per image url only on multiple ajax requests.
   * The session values have a unique identifier for each registered surfer and selected ressource.
   * This solution allows user tracking extensions to avoid spam.
   *
   * @param string $imageUrl
   * @param boolean $removeDetectedSessionValues
   */
  public function addThumbnailLink($imageUrl, $removeDetectedSessionValues = FALSE) {
    // detect thumbnail links by session values to avoid duplicate media db files
    list($thumbnailLink, $removeSessionValue) = $this->_getSessionValueLink(
      $imageUrl, 'thumbnail_links', $removeDetectedSessionValues
    );
    $fileId = NULL;
    if (!isset($thumbnailLink)) {
      $fileId = $this->_addExternalImageToMediaDb(
        $imageUrl, $this->_textOptions['thumbnails_folder']
      );
      if (isset($fileId)) {
        // create link with media image tag for thumbnail creation, first run
        $thumbnailLink = sprintf(
          '<a href="%s" title="%s">%s</a>',
          PapayaUtilStringXml::escapeAttribute($imageUrl),
          PapayaUtilStringXml::escapeAttribute($imageUrl),
          PapayaUtilStringPapaya::getImageTag(
            $fileId, $this->_textOptions['thubmnails_size'], $this->_textOptions['thubmnails_size'],
            '', $this->_textOptions['thubmnails_resize_mode']
          )
        );
      }
    }
    if (isset($thumbnailLink)) {
      $this->_setSessionValueLink(
        $fileId, $removeSessionValue, $imageUrl, $thumbnailLink, 'thumbnail_links'
      );
      $this->_thumbnailLinks[] = $thumbnailLink;
    }
    $this->_removeSessionValueLink($removeSessionValue, $imageUrl, 'thumbnail_links');
  }

  /**
   * Video links to create embedded video elemenets, e.g. iframes
   *
   * @param string $videoUrl
   * @param boolean $removeDetectedSessionValues
   */
  public function addVideoLink($videoUrl, $removeDetectedSessionValues = FALSE) {
    list($hoster, $id) = $this->_getVideoHosterAndId($videoUrl);
    if (!empty($hoster) && !empty($id)) {
      list($videoLink, $removeSessionValue) = $this->_getSessionValueLink(
        $videoUrl, 'video_links', $removeDetectedSessionValues
      );
      $created = NULL;
      if (!isset($videoLink)) {
        switch ($hoster) {
          case 'vimeo':
            $apiData = unserialize(file_get_contents(
              sprintf('http://vimeo.com/api/v2/video/%s.php', $id)
            ));
            $previewImageUrl = $apiData[0]['thumbnail_large'];
            $videoTitle = (string)$apiData[0]['title'];
            break;
          case 'youtube':
            $previewImageUrl = sprintf(
              'http://img.youtube.com/vi/%s/maxresdefault.jpg', $id
            );
            // some youtube videos does not have a max resulution image, fallback first video thumbnail
            $fh = @fopen($previewImageUrl, 'r');
            if (!$fh) {
              $previewImageUrl = sprintf('http://i.ytimg.com/vi/%s/0.jpg', $id);
            } else {
              fclose($fh);
            }
            $apiData = simplexml_load_string(file_get_contents(
              sprintf('http://gdata.youtube.com/feeds/api/videos/%s?fields=title', $id)
            ));
            $videoTitle = (string)$apiData[0]->title;
            break;
        }
        $fileId = $this->_addExternalImageToMediaDb(
          $previewImageUrl, $this->_textOptions['videos_thumbnails_folder']
        );
        if (isset($fileId)) {
          $videoLink = sprintf(
            '<video-link src="%s" hoster="%s" id="%s" title="%s" title-js-escaped="%s" width="%d" height="%d">%s</video-link>',
            PapayaUtilStringXml::escapeAttribute($videoUrl),
            $hoster,
            $id,
            PapayaUtilStringXml::escapeAttribute($videoTitle),
            str_replace('&', '&amp;', PapayaUtilStringXml::escapeAttribute($videoTitle)),
            $this->_textOptions['videos_width'],
            $this->_textOptions['videos_height'],
            PapayaUtilStringPapaya::getImageTag(
              $fileId, $this->_textOptions['videos_width'], $this->_textOptions['videos_height'],
              '', 'mincrop'
            )
          );
          $created = TRUE;
        }
      }
      if (isset($videoLink)) {
        $this->_setSessionValueLink(
          $created, $removeSessionValue, $videoUrl, $videoLink, 'video_links'
        );
        $this->_videoLinks[] = $videoLink;
      }
      $this->_removeSessionValueLink($removeSessionValue, $videoUrl, 'video_links');
    }
  }

  /**
   * Helper method to get a video hoster and video id by an url
   *
   * @param string $url
   * @return array [hoster / id]
   */
  protected function _getVideoHosterAndId($url) {
    $videoPattern = '~youtube\.com/watch\?(.*)?v=([a-zA-Z0-9\-_]+)|vimeo\.com/([0-9]+)~i';
    preg_match($videoPattern, $url, $videoMatches);
    if (!empty($videoMatches[3])) {
      // vimeo id
      return array('vimeo', $videoMatches[3]);
    } elseif (!empty($videoMatches[2])) {
      // youtube id
      return array('youtube', $videoMatches[2]);
    }
    return array(NULL, NULL);
  }

  /**
   * Helper method to add linked image or video preview image from url to media db
   * for thumbnail output.
   *
   * @param string $imageUrl
   * @param integer $mediaDBFolderId
   * @return string|NULL $fileId
   */
  protected function _addExternalImageToMediaDb($imageUrl, $mediaDBFolderId) {
    $fileId = NULL;
    $contents = file_get_contents($imageUrl);
    if (!empty($contents)) {
      $path = tempnam('/tmp', 'acommunity-extended-text-image-');
      $handle = fopen($path, "a");
      fwrite($handle, $contents);
      fclose($handle);
      if (getimagesize($path) != FALSE) {
        $fileName = substr($path, strlen($path) - 6); // get the random chars of tempnam as name
        if (!empty($this->_ressource)) {
          $fileName = $fileName.':'.$this->_ressource;
        }
        $fileId = $this->mediaDbEdit()->addFile($path, $fileName, $mediaDBFolderId, '');
      }
      if (file_exists($path)) {
        unlink($path);
      }
    }
    return $fileId;
  }


  /**
   * Helper method for add methods to get an existing session value by ident, type and url
   *
   * @param string $url
   * @param string $type
   * @param boolean $removeDetected
   * @return array [link value from session / remove session value flag]
   */
  protected function _getSessionValueLink($url, $type, $removeDetected) {
    $sessionIdent = $type == 'thumbnail_links' ?
      $this->_sessionIdentThumbnailLinks : $this->_sessionIdentVideoLinks;
    if (isset($this->_session->values[$sessionIdent][$url])) {
      $link = $this->_session->values[$sessionIdent][$url];
      if ($removeDetected == TRUE) {
        // remove links in the filter process, that's the final run
        return array($link, TRUE);
      }
      return array($link, FALSE);
    }
    return array(NULL, NULL);
  }

  /**
   * Helper method for add methods to set a session value by url, type and link
   *
   * @param mixed $valueToCheck a value that has to exist
   * @param boolean $removeValue remove session value flag, has to be NULL or FALSE
   * @param string $url
   * @param string $link
   * @param string $type
   */
  protected function _setSessionValueLink($valueToCheck, $removeValue, $url, $link, $type) {
    if (isset($valueToCheck) && (!isset($removeValue) || $removeValue == FALSE)) {
      $sessionIdent = $type == 'thumbnail_links' ?
        $this->_sessionIdentThumbnailLinks : $this->_sessionIdentVideoLinks;
      // session values insert on file creation, that's the first run
      if (!isset($this->_session->values[$sessionIdent])) {
        $this->_session->values[$sessionIdent] = array();
      }
      $this->_session->values[$sessionIdent] = array_merge(
        $this->_session->values[$sessionIdent], array($url => $link)
      );
    }
  }

  /**
   * Helper method for add methods to remove a session value by url, type
   * To avoid duplications in links insertion by session values in the filter method
   * The filter method uses this method to remove all session value of a type
   *
   * @param $removeValue remove session value flag, has to be true
   * @param string $url set to TRUE to remove all urls of a type
   * @param string $type
   */
  protected function _removeSessionValueLink($removeValue, $url, $type) {
    if (isset($removeValue) && $removeValue == TRUE) {
      $sessionIdent = $type == 'thumbnail_links' ?
        $this->_sessionIdentThumbnailLinks : $this->_sessionIdentVideoLinks;
      if ($url === TRUE) {
        unset($this->_session->values[$sessionIdent]);
      } else {
        $sessionValues = $this->_session->values[$sessionIdent];
        unset($sessionValues[$url]);
        $this->_session->values[$sessionIdent] = $sessionValues;
      }
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