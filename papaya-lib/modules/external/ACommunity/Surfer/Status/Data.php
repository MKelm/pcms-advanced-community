<?php
/**
 * Advanced community surfer status data class to handle all sorts of related data
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
 * Base ui content data object
 */
require_once(dirname(__FILE__).'/../../Ui/Content/Data.php');

/**
 * Advanced community surfer status data class to handle all sorts of related data
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunitySurferStatusData extends ACommunityUiContentData {

  /**
   * Surfer data
   * @var array
   */
  public $surfer = NULL;

  /**
   * Avatar size
   * @var integer
   */
  protected $_avatarSize = 0;

  /**
   * Avatar resize mode
   * @var string
   */
  protected $_avatarResizeMode = 'mincrop';

  /**
   * Page Id to redirect after logout
   * @var integer
   */
  protected $_logoutRedirectionPageId = NULL;

  /**
   * Set data by plugin object
   *
   * @param array $data
   * @param array $captionNames
   * @param array $messageNames
   */
  public function setPluginData($data, $captionNames = array(), $messageNames = array()) {
    $this->_avatarSize = (int)$data['avatar_size'];
    $this->_avatarResizeMode = $data['avatar_resize_mode'];
    $this->_logoutRedirectionPageId = $data['logout_redirection_page_id'];
    parent::setPluginData($data, $captionNames, $messageNames);
  }

  /**
   * Intitialize surfer data
   */
  public function initialize() {
    $ressource = $this->ressource();
    if (!empty($ressource['id'])) {
      $baseSurfer = new base_surfer();
      $logoutParameters = array($baseSurfer->logformVar.'_logout' => 1);
      if ($this->_logoutRedirectionPageId > 0) {
        $baseObject = new base_object();
        $logoutParameters[$baseSurfer->logformVar.'_redirection'] =
          $baseObject->getWebLink($this->_logoutRedirectionPageId);
      }
      $reference = clone $this->reference();
      $reference->setParameters(array($logoutParameters), NULL);
      $logoutLink = $reference->getRelative();

      $surfer = $this->owner->communityConnector()->loadSurfer($ressource['id']);
      $this->surfer = array(
        'handle' => $surfer['surfer_handle'],
        'givenname' => $surfer['surfer_givenname'],
        'surname' => $surfer['surfer_surname'],
        'avatar' => $this->owner->communityConnector()->getAvatar(
          $ressource['id'], $this->_avatarSize, TRUE, $this->_avatarResizeMode
        ),
        'page-link' => $this->owner->acommunityConnector()->getSurferPageLink($ressource['id']),
        'edit-link' => $this->owner->acommunityConnector()->getSurferEditorPageLink($surfer['surfer_handle']),
        'logout-link' => $logoutLink
      );
      unset($surfer);
    }

    $loginLink = $this->owner->acommunityConnector()->getSurferLoginPageLink();
    $registrationLink = $this->owner->acommunityConnector()->getSurferRegistrationPageLink();
    $simpleTemplate = new base_simpletemplate();

    $this->messages['no_login'] = $simpleTemplate->parse(
      $this->messages['no_login'],
      array(
        'login_link' => sprintf(
          '<a href="%s">%s</a>', $loginLink, $this->captions['login_link']
        ),
        'registration_link' => sprintf(
          '<a href="%s">%s</a>', $registrationLink, $this->captions['registration_link']
        )
      )
    );
  }

}
