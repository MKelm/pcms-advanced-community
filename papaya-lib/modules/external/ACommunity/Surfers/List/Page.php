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
class ACommunitySurfersListPage extends base_content {

  /**
   * Use a advanced community parameter group name
   * @var string
   */
  public $paramName = 'acslp';

  /**
   * Edit fields
   * @var array $editFields
   */
  public $editFields = array(
    'avatar_size' => array(
      'Avatar Size', 'isNum', TRUE, 'input', 30, '', 60
    ),
    'avatar_resize_mode' => array(
      'Avatar Resize Mode', 'isAlpha', TRUE, 'translatedcombo',
       array(
         'abs' => 'Absolute', 'max' => 'Maximum', 'min' => 'Minimum', 'mincrop' => 'Minimum cropped'
       ), '', 'mincrop'
    ),
    'display_mode' => array(
      'Display Mode', 'isAlpha', TRUE, 'translatedcombo',
       array(
         'contacts_and_requests' => 'Surfer contacts and contact requests of current surfer',
         'lastaction' => 'Surfers by last action Time',
         'registration' => 'Surfers by registration Time'
       ), '', 'contacts_and_requests'
    ),
    'timeframe' => array(
      'Timeframe in days', 'isNum', TRUE, 'input', 30,
      'Get surfers by last action or registration time in a specified timeframe.', 365
    ),
    'limit' => array(
      'Limit', 'isNum', TRUE, 'input', 30,
      "Note: The contacts display mode has three lists for contacts,
      received contact requests and sent contact requests.",
      5
    ),
    'Captions',
    'caption_contacts' => array(
      'Contacts', 'isNoHTML', TRUE, 'input', 200, '', 'Contacts'
    ),
    'caption_own_contact_requests' => array(
      'Sent contact requests', 'isNoHTML', TRUE, 'input', 200, '', 'Sent contact requests'
    ),
    'caption_contact_requests' => array(
      'Received contact requests', 'isNoHTML', TRUE, 'input', 200, '', 'Received contact requests'
    ),
    'caption_last_action' => array(
      'Last Action', 'isNoHTML', TRUE, 'input', 200, '', 'Last action'
    ),
    'caption_registration' => array(
      'Registered Since', 'isNoHTML', TRUE, 'input', 200, '', 'Registered sine'
    ),
    'Command Captions',
    'caption_command_accept_contact_request' => array(
      'Accept contact request', 'isNoHTML', TRUE, 'input', 200, '', 'Accept contact request'
    ),
    'caption_command_decline_contact_request' => array(
      'Decline contact request', 'isNoHTML', TRUE, 'input', 200, '', 'Decline contact request'
    ),
    'caption_command_remove_contact_request' => array(
      'Remove contact request', 'isNoHTML', TRUE, 'input', 200, '', 'Remove contact request'
    ),
    'caption_command_remove_contact' => array(
      'Remove contact', 'isNoHTML', TRUE, 'input', 200, '', 'Remove contact'
    ),
    'Messages',
    'message_empty_list' => array(
      'No Entries', 'isNoHTML', TRUE, 'input', 200, '', 'No entries.'
    )
  );

  /**
   * List object
   * @var ACommunitySurfersList
   */
  protected $_list = NULL;

  /**
   * Set surfer ressource data to load corresponding surfer
   */
  public function setRessourceData() {
    $this->surfersList()->data()->ressource('surfer', $this);
  }

  /**
  * Get (and, if necessary, initialize) the ACommunitySurfersList object
  *
  * @return ACommunitySurfersList $list
  */
  public function surfersList(ACommunitySurfersList $list = NULL) {
    if (isset($list)) {
      $this->_list = $list;
    } elseif (is_null($this->_list)) {
      include_once(dirname(__FILE__).'/../List.php');
      $this->_list = new ACommunitySurfersList();
      $this->_list->parameterGroup($this->paramName);
      $this->_list->data()->setPluginData(
        $this->data,
        array(
          'caption_last_action', 'caption_registration',
          'caption_contacts', 'caption_own_contact_requests', 'caption_contact_requests',
          'caption_command_accept_contact_request', 'caption_command_decline_contact_request',
          'caption_command_remove_contact_request', 'caption_command_remove_contact'
        ),
        array('message_empty_list')
      );
      $this->_list->data()->languageId = $this->papaya()->request->languageId;
    }
    return $this->_list;
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
    return $this->surfersList()->getXml();
  }

}