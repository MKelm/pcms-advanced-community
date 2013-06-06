<?php
/**
 * Advanced community surfer groups page
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
require_once(dirname(__FILE__).'/../../Groups/Page.php');

/**
 * Advanced community surfer groups page
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunitySurferGroupsPage extends ACommunityGroupsPage {

  /**
   * Contains groups of current active surfer only
   * @var boolean
   */
  protected $_showOwnGroups = TRUE;

  /**
   * Edit fields
   * @var array $editFields
   */
  public $editFields = array(
    'image_size' => array(
      'Image Size', 'isNum', TRUE, 'input', 30, '', 60
    ),
    'image_resize_mode' => array(
      'Image Resize Mode', 'isAlpha', TRUE, 'translatedcombo',
       array(
         'abs' => 'Absolute', 'max' => 'Maximum', 'min' => 'Minimum', 'mincrop' => 'Minimum cropped'
       ), '', 'mincrop'
    ),
    'groups_per_row' => array(
      'Groups Per Row', 'isNum', TRUE, 'input', 30, '', 3
    ),
    'groups_per_page' => array(
      'Groups Per Page', 'isNum', TRUE, 'input', 30, '', 12
    ),
    'group_images_folder' => array(
      'Group Images Folder', 'isNum', TRUE, 'mediafolder', 30, '', NULL
    ),
    'Mode Captions',
    'caption_mode_groups' => array(
      'Groups', 'isNoHTML', TRUE, 'input', 200, '', 'Groups'
    ),
    'caption_mode_invitations' => array(
      'Invitations', 'isNoHTML', TRUE, 'input', 200, '', 'Invitations'
    ),
    'caption_mode_requests' => array(
      'Invitations', 'isNoHTML', TRUE, 'input', 200, '', 'Requests'
    ),
    'Command Captions',
    'caption_command_accept_invitation' => array(
      'Accept Invitation', 'isNoHTML', TRUE, 'input', 200, '', 'Accept'
    ),
    'caption_command_decline_invitation' => array(
      'Decline Invitation', 'isNoHTML', TRUE, 'input', 200, '', 'Decline'
    ),
    'caption_command_add' => array(
      'Navigation Add Group', 'isNoHTML', TRUE, 'input', 200, '', 'Add group'
    ),
    'caption_command_edit' => array(
      'Edit Group', 'isNoHTML', TRUE, 'input', 200, '', 'Edit'
    ),
    'caption_command_edit_group' => array(
      'Navigation Edit Group', 'isNoHTML', TRUE, 'input', 200, '', 'Edit group'
    ),
    'caption_command_delete' => array(
      'Remove Group', 'isNoHTML', TRUE, 'input', 200, '', 'Remove'
    ),
    'caption_command_remove_membership' => array(
      'Remove Membership', 'isNoHTML', TRUE, 'input', 200, '', 'Remove'
    ),
    'caption_command_remove_request' => array(
      'Remove Request', 'isNoHTML', TRUE, 'input', 200, '', 'Remove'
    ),
    'Dialog Captions',
    'caption_dialog_is_public' => array(
      'Is Public', 'isNoHTML', TRUE, 'input', 200, '', 'Is public?'
    ),
    'caption_dialog_is_public_yes' => array(
      'Is Public Yes', 'isNoHTML', TRUE, 'input', 200, '', 'Yes'
    ),
    'caption_dialog_is_public_no' => array(
      'Is Public No', 'isNoHTML', TRUE, 'input', 200, '', 'No'
    ),
    'caption_dialog_handle' => array(
      'Handle', 'isNoHTML', TRUE, 'input', 200, '', 'Handle'
    ),
    'caption_dialog_title' => array(
      'Title', 'isNoHTML', TRUE, 'input', 200, '', 'Title'
    ),
    'caption_dialog_description' => array(
      'Description', 'isNoHTML', TRUE, 'input', 200, '', 'Description'
    ),
    'caption_dialog_image' => array(
      'Image', 'isNoHTML', TRUE, 'input', 200, '', 'Image'
    ),
    'caption_dialog_button_add' => array(
      'Add Button', 'isNoHTML', TRUE, 'input', 200, '', 'Add'
    ),
    'caption_dialog_button_edit' => array(
      'Edit Button', 'isNoHTML', TRUE, 'input', 200, '', 'Edit'
    ),
    'Error Messages',
    'message_access_denied' => array(
      'Access denied', 'isNoHTML', TRUE, 'input', 200, '', 'Groups overview access denied.'
    ),
    'message_no_groups' => array(
      'No Groups', 'isNoHTML', TRUE, 'input', 200, '', 'No groups.'
    ),
    'message_no_invitations' => array(
      'No Invitations', 'isNoHTML', TRUE, 'input', 200, '', 'No invitations.'
    ),
    'message_no_requests' => array(
      'No Requests', 'isNoHTML', TRUE, 'input', 200, '', 'No requests.'
    ),
    'message_failed_to_execute_command' => array(
      'Failed To Execute Command', 'isNoHTML', TRUE, 'input', 200, '', 'Failed to execute command.'
    ),
    'message_dialog_error_handle_duplicate' => array(
      'Dialog Handle Duplicate Error', 'isNoHTML', TRUE, 'input', 200, '',
      'The group handle exists already, please choose another one.'
    ),
    'message_dialog_input_error' => array(
      'Dialog Input Error', 'isNoHTML', TRUE, 'input', 200, '',
      'Invalid input. Please check the field(s) "%s".'
    ),
    'message_dialog_error_no_folder' => array(
      'Dialog No Folder Error', 'isNoHTML', TRUE, 'input', 200, '',
      'Could not find images folder.'
    ),
    'message_dialog_error_upload' => array(
      'Dialog Upload Error', 'isNoHTML', TRUE, 'input', 200, '',
      'Upload error.'
    ),
    'message_dialog_error_file_extension' => array(
      'Dialog File Extension Error', 'isNoHTML', TRUE, 'input', 200, '',
      'Wrong file extension.'
    ),
    'message_dialog_error_file_type' => array(
      'Dialog File Type Error', 'isNoHTML', TRUE, 'input', 200, '',
      'Wrong file type.'
    ),
    'message_dialog_error_media_db' => array(
      'Dialog Media DB Error', 'isNoHTML', TRUE, 'input', 200, '',
      'Could not comple upload process in Media DB.'
    )
  );

  /**
   * Names of caption data
   * @var array
   */
  protected $_captionNames = array(
    'caption_dialog_is_public', 'caption_dialog_title', 'caption_dialog_description',
    'caption_dialog_image', 'caption_dialog_is_public_yes', 'caption_dialog_is_public_no',
    'caption_dialog_button_add', 'caption_command_delete', 'caption_command_add',
    'caption_dialog_button_edit', 'caption_dialog_handle', 'caption_command_edit',
    'caption_mode_groups', 'caption_mode_invitations', 'caption_command_edit_group',
    'caption_command_accept_invitation', 'caption_command_decline_invitation',
    'caption_command_remove_request', 'caption_mode_requests', 'caption_command_remove_membership'
  );

  /**
   * Names of message data
   * @var array
   */
  protected $_messageNames = array(
    'message_no_groups', 'message_dialog_input_error', 'message_dialog_error_no_folder',
    'message_dialog_error_upload', 'message_no_invitations',
    'message_dialog_error_file_extension', 'message_dialog_error_file_type',
    'message_dialog_error_media_db', 'message_dialog_error_handle_duplicate',
    'message_failed_to_execute_command', 'message_no_requests', 'message_access_denied'
  );
}