<?php
/**
 * Advanced community surfer page
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
* Basic class page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

/**
 * Advanced community surfer page
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunitySurferPage extends base_content {

  /**
   * Use a advanced community parameter group name
   * @var string
   */
  public $paramName = 'acs';

  /**
  * Content edit fields
  * @var array $editFields
  */
  public $editFields = array(
    'avatar_size' => array(
      'Avatar Size', 'isNum', TRUE, 'input', 30, '', 160
    ),
    'avatar_resize_mode' => array(
      'Avatar Resize Mode', 'isAlpha', TRUE, 'translatedcombo',
       array(
         'abs' => 'Absolute', 'max' => 'Maximum', 'min' => 'Minimum', 'mincrop' => 'Minimum cropped'
       ), '', 'mincrop'
    ),
    'Titles',
    'title_gender_male' => array(
      'Gender Male', 'isNoHTML', TRUE, 'input', 200, '', 'Male'
    ),
    'title_gender_female' => array(
      'Gender Female', 'isNoHTML', TRUE, 'input', 200, '', 'Female'
    ),
    'Captions',
    'caption_base_details' => array(
      'Base Details', 'isNoHTML', TRUE, 'input', 200, '', 'Base'
    ),
    'caption_surfer_handle' => array(
      'Surfer Handle', 'isNoHTML', TRUE, 'input', 200, '', 'Handle'
    ),
    'caption_surfer_givenname' => array(
      'Surfer Givenname', 'isNoHTML', TRUE, 'input', 200, '', 'Givenname'
    ),
    'caption_surfer_surname' => array(
      'Surfer Surname', 'isNoHTML', TRUE, 'input', 200, '', 'Surname'
    ),
    'caption_surfer_email' => array(
      'Surfer E-Mail', 'isNoHTML', TRUE, 'input', 200, '', 'E-Mail'
    ),
    'caption_surfer_gender' => array(
      'Surfer Gender', 'isNoHTML', TRUE, 'input', 200, '', 'Gender'
    ),
    'caption_surfer_avatar' => array(
      'Surfer Avatar', 'isNoHTML', TRUE, 'input', 200, '', 'Avatar'
    ),
    'caption_surfer_lastlogin' => array(
      'Surfer Last Login', 'isNoHTML', TRUE, 'input', 200, '', 'Last login'
    ),
    'caption_surfer_lastaction' => array(
      'Surfer Last Action', 'isNoHTML', TRUE, 'input', 200, '', 'Last action'
    ),
    'caption_surfer_registration' => array(
      'Surfer Registration', 'isNoHTML', TRUE, 'input', 200, '', 'Registration'
    ),
    'caption_surfer_group' => array(
      'Surfer Group', 'isNoHTML', TRUE, 'input', 200, '', 'Group'
    ),
    'caption_send_message' => array(
      'Send message', 'isNoHTML', TRUE, 'input', 200, '', 'Send message'
    ),
    'Contact Status Captions',
    'caption_contact_status_none' => array(
      'Is no contact', 'isNoHTML', TRUE, 'input', 200, '', 'Request contact'
    ),
    'caption_contact_status_direct' => array(
      'Is contact', 'isNoHTML', TRUE, 'input', 200, '', 'Contact accepted'
    ),
    'caption_contact_status_pending' => array(
      'Is request', 'isNoHTML', TRUE, 'input', 200, '', 'Contact request pending'
    ),
    'caption_contact_status_own_pending' => array(
      'Is own request', 'isNoHTML', TRUE, 'input', 200, '', 'Own contact request pending'
    ),
    'Command Captions',
    'caption_command_request_contact' => array(
      'Request contact', 'isNoHTML', TRUE, 'input', 200, '', 'Request'
    ),
    'caption_command_accept_contact_request' => array(
      'Accept contact request', 'isNoHTML', TRUE, 'input', 200, '', 'Accept'
    ),
    'caption_command_decline_contact_request' => array(
      'Decline contact request', 'isNoHTML', TRUE, 'input', 200, '', 'Decline'
    ),
    'caption_command_remove_contact_request' => array(
      'Remove contact request', 'isNoHTML', TRUE, 'input', 200, '', 'Remove contact request'
    ),
    'caption_command_remove_contact' => array(
      'Remove contact', 'isNoHTML', TRUE, 'input', 200, '', 'Remove contact'
    ),
    'Message',
    'message_no_surfer' => array(
      'No Surfer', 'isNoHTML', TRUE, 'input', 200, '', 'No surfer selected.'
    )
  );

  /**
   * Surfer object
   * @var ACommunitySurfer
   */
  protected $_surfer = NULL;

  /**
   * Set surfer ressource data to load corresponding surfer
   */
  public function setRessourceData() {
    $this->surfer()->data()->ressource('surfer', $this, array('surfer' => 'surfer_handle'));
  }

  /**
  * Get (and, if necessary, initialize) the ACommunitySurfer object
  *
  * @return ACommunitySurfer $surfer
  */
  public function surfer(ACommunityComments $surfer = NULL) {
    if (isset($surfer)) {
      $this->_surfer = $surfer;
    } elseif (is_null($this->_surfer)) {
      include_once(dirname(__FILE__).'/../Surfer.php');
      $this->_surfer = new ACommunitySurfer();
      $this->_surfer->parameterGroup($this->paramName);
      $captionNames = array(
        'caption_base_details',
        'caption_surfer_handle', 'caption_surfer_givenname', 'caption_surfer_surname',
        'caption_surfer_email', 'caption_surfer_gender', 'caption_surfer_avatar',
        'caption_surfer_lastlogin', 'caption_surfer_lastaction', 'caption_surfer_registration',
        'caption_surfer_group', 'caption_send_message',
        'caption_contact_status_none', 'caption_contact_status_direct', 'caption_contact_status_pending',
        'caption_contact_status_own_pending', 'caption_command_request_contact',
        'caption_command_accept_contact_request', 'caption_command_decline_contact_request',
        'caption_command_remove_contact_request', 'caption_command_remove_contact'
      );
      $this->_surfer->data()->setPluginData($this->data, $captionNames, array('message_no_surfer'));
      $this->_surfer->data()->languageId = $this->papaya()->request->languageId;
    }
    return $this->_surfer;
  }


  /**
  * Get parsed data
  *
  * @return string $result
  */
  function getParsedData() {
    $this->setDefaultData();
    $this->initializeParams();
    $this->setRessourceData();
    return $this->surfer()->getXml();
  }

}
