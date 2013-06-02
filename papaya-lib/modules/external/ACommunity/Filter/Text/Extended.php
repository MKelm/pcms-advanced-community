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
   * A ressource to indentify image thumbnails by ressource
   * @var string
   */
  protected $_ressource = NULL;

  /**
   * List with thumbnail links
   * @var array
   */
  protected $_thumbnailLinks = array();

  /**
   * Create object and store options to match additional character groups
   *
   * @param integer $options
   */
  public function __construct($options, $ressource = NULL) {
    parent::__construct($options);
    $this->_ressource = $ressource;
  }

  /**
  * The filter function is used to read an input value if it is valid.
  *
  * @param string $value
  * @return string
  */
  public function filter($value) {
    $value = parent::filter($value);
    $this->_textOptions = $this->acommunityConnector()->getTextOptions();
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
        $contents = file_get_contents($match[1]);
        if (!empty($contents)) {
          $path = tempnam('/tmp', 'acommunity-text-thumbnail-');
          $handle = fopen($path, "a");
          fwrite($handle, $contents);
          fclose($handle);
          if (!empty($this->_ressource)) {
            $fileName = $path.':'.$this->_ressource;
          } else {
            $fileName = $path;
          }
          $fileId = $this->mediaDbEdit()->addFile(
            $path, $fileName, $this->_textOptions['thumbnails_folder'], ''
          );
          unlink($path);

          $this->_thumbnailLinks[] = sprintf(
            '<a href="%s" title="%s">%s</a>',
            $match[1], $match[1],
            PapayaUtilStringPapaya::getImageTag(
              $fileId, $this->_textOptions['thubmnails_size'], $this->_textOptions['thubmnails_size'],
              '', $this->_textOptions['thubmnails_resize_mode']
            )
          );
        }
      }
    }
    $urlToShow = $match[1];
    if (strlen($urlToShow) > $this->_urlLength) {
      $urlToShow = substr($match[1], 0, $this->_urlLength - 3).'...';
    }
    return sprintf('<a href="%s">%s</a>', $match[1], $urlToShow);
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