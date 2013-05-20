<?php
/**
 * Advanced community surfer status box
 *
 * Offers status information of logged in user and links to certain surfer pages
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
 * Basic box class
 */
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');

/**
 * Advanced community surfer status box
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunitySurferStatusBox extends base_actionbox {

  /**
   * Parameter prefix name
   * @var string $paramName
   */
  public $paramName = 'acs';

  /**
   * Edit fields
   * @var array $editFields
   */
  public $editFields = array(
    'logout_redirection_page_id' => array(
      'Logout Redirection Page', 'isNum', TRUE, 'pageid', 30, '', 0
    ),
    'avatar_size' => array(
      'Avatar Size', 'isNum', TRUE, 'input', 30, '', 40
    ),
    'avatar_resize_mode' => array(
      'Avatar Resize Mode', 'isAlpha', TRUE, 'translatedcombo',
       array(
         'abs' => 'Absolute', 'max' => 'Maximum', 'min' => 'Minimum', 'mincrop' => 'Minimum cropped'
       ), '', 'mincrop'
    ),
    'Captions',
    'caption_login_link' => array(
      'Login Link', 'isNoHTML', TRUE, 'input', 200, 'For placeholder {%LOGIN_LINK%}', 'login'
    ),
    'caption_registration_link' => array(
      'Registration Link', 'isNoHTML', TRUE, 'input', 200,
      'For placeholder {%REGISTRATION_LINK%}', 'register'
    ),
    'caption_edit_link' => array(
      'Edit Link', 'isNoHTML', TRUE, 'input', 200, 'Caption for edit surfer link.', 'Edit'
    ),
    'caption_logout_link' => array(
      'Logout Link', 'isNoHTML', TRUE, 'input', 200, 'Caption for logout surfer link.', 'Logout'
    ),
    'Messages',
    'message_no_login' => array(
      'No Login', 'isNoHTML', TRUE, 'input', 200, '',
      'Get involved, {%LOGIN_LINK%} or {%REGISTRATION_LINK%}.'
    )
  );

  /**
   * Status object
   * @var ACommunitySurferStatus
   */
  protected $_status = NULL;

  /**
   * Get ressource data to load corresponding comments
   * Overwrite this method for customized ressources
   */
  public function setRessourceData() {
    $this->status()->data()->ressource('surfer', $this);
  }

  /**
  * Get (and, if necessary, initialize) the ACommunitySurferStatus object
  *
  * @return ACommunitySurferStatus $status
  */
  public function status(ACommunitySurferStatus $status = NULL) {
    if (isset($status)) {
      $this->_status = $status;
    } elseif (is_null($this->_status)) {
      include_once(dirname(__FILE__).'/../Status.php');
      $this->_status = new ACommunitySurferStatus();
      $this->_status->parameterGroup($this->paramName);
      $this->_status->data()->setPluginData(
        $this->data,
        array(
          'caption_login_link', 'caption_registration_link',
          'caption_edit_link', 'caption_logout_link'
        ),
        array('message_no_login')
      );
      $this->_status->data()->languageId = $this->papaya()->request->languageId;
    }
    return $this->_status;
  }

  /**
   * Get parsed data
   *
   * @return string $result XML
   */
  public function getParsedData() {
    $this->setDefaultData();
    $this->initializeParams();
    $this->setRessourceData();
    return $this->status()->getXml();
  }

}
