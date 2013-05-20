<?php
/**
 * Advanced community surfer contacts page
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
require_once(dirname(__FILE__).'/../../Surfers/Page.php');

/**
 * Advanced community surfer contacts page
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunitySurferContactsPage extends ACommunitySurfersPage {

  /**
   * Use a advanced community parameter group name
   * @var string
   */
  public $paramName = 'acscp';

  /**
   * Display mode
   * @var string
   */
  protected $_displayMode = 'contacts_and_requests';

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
    'limit' => array(
      'Limit', 'isNum', TRUE, 'input', 30,
      "Note: The page has three lists for contacts,
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
   * Check url name to fix wrong page names
   *
   * @param string $currentFileName
   * @param string $outputMode
   */
  public function checkURLFileName($currentFileName, $outputMode) {
    $this->setRessourceData();
    return $this->surfers()->checkURLFileName($this, $currentFileName, $outputMode, 's-contacts');
  }
}