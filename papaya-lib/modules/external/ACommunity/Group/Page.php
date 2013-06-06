<?php
/**
 * Advanced community group page
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
 * Advanced community group page
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityGroupPage extends base_content implements PapayaPluginCacheable {

  /**
   * Use a advanced community parameter group name
   * @var string
   */
  public $paramName = 'acg';

  /**
  * Content edit fields
  * @var array $editFields
  */
  public $editFields = array(
    'image_size' => array(
      'Avatar Size', 'isNum', TRUE, 'input', 30, '', 160
    ),
    'image_resize_mode' => array(
      'Avatar Resize Mode', 'isAlpha', TRUE, 'translatedcombo',
       array(
         'abs' => 'Absolute', 'max' => 'Maximum', 'min' => 'Minimum', 'mincrop' => 'Minimum cropped'
       ), '', 'mincrop'
    ),
    'Captions',
    'caption_time' => array(
      'Exists Sine', 'isNoHTML', TRUE, 'input', 200, '', 'Exists since'
    ),
    'Link Captions',
    'caption_link_request_membership' => array(
      'Request Membership', 'isNoHTML', TRUE, 'input', 200, '', 'Request membership'
    ),
    'caption_link_remove_membership_request' => array(
      'Remove Membership Request', 'isNoHTML', TRUE, 'input', 200, '', 'Remove membership request'
    ),
    'caption_link_accept_membership_invitation' => array(
      'Accept Membership Invitation', 'isNoHTML', TRUE, 'input', 200, '', 'Accept membership invitation'
    ),
    'caption_link_decline_membership_invitation' => array(
      'Decline Membership Invitation', 'isNoHTML', TRUE, 'input', 200, '', 'Decline membership invitation'
    ),
    'caption_link_invite_surfers' => array(
      'Invite Surfers', 'isNoHTML', TRUE, 'input', 200, '', 'Invite surfers'
    ),
    'caption_link_member' => array(
      'Member', 'isNoHTML', TRUE, 'input', 200,
      'Link to own groups page, with members mode.', '%d member'
    ),
    'caption_link_members' => array(
      'Members', 'isNoHTML', TRUE, 'input', 200,
      'Link to own groups page, with members mode.', '%d members'
    ),
    'caption_link_membership_request' => array(
      'Membership Request', 'isNoHTML', TRUE, 'input', 200,
      'Link to own groups page, with membership requests mode.', '%d membership request'
    ),
    'caption_link_membership_requests' => array(
      'Membership Requests', 'isNoHTML', TRUE, 'input', 200,
      'Link to own groups page, with membership requests mode.', '%d membership requests'
    ),
    'caption_link_membership_invitation' => array(
      'Membership Invitation', 'isNoHTML', TRUE, 'input', 200,
      'Link to own groups page, with membership invitations mode.', '%d membership invitation'
    ),
    'caption_link_membership_invitations' => array(
      'Membership Invitations', 'isNoHTML', TRUE, 'input', 200,
      'Link to own groups page, with membership requests mode.', '%d membership invitations'
    ),
    'caption_link_owner' => array(
      'Owner', 'isNoHTML', TRUE, 'input', 200,
      'Link to owner page.', 'Owner %s'
    ),
    'Message',
    'message_access_denied' => array(
      'Access denied', 'isNoHTML', TRUE, 'input', 200, '', 'Group access denied, please use another group.'
    ),
    'message_failed_to_execute_command' => array(
      'Failed To Execute Command', 'isNoHTML', TRUE, 'input', 200, '', 'Failed to execute command.'
    )
  );

  /**
   * Group object
   * @var ACommunityGroup
   */
  protected $_group = NULL;

  /**
   * Cache definition
   * @var PapayaCacheIdentifierDefinition
   */
  protected $_cacheDefiniton = NULL;

  /**
   * Current ressource
   * @var ACommunityUiContentRessource
   */
  protected $_ressource = NULL;

  /**
   * Define the cache definition for output.
   *
   * @see PapayaPluginCacheable::cacheable()
   * @param PapayaCacheIdentifierDefinition $definition
   * @return PapayaCacheIdentifierDefinition
   */
  public function cacheable(PapayaCacheIdentifierDefinition $definition = NULL) {
    if (isset($definition)) {
      $this->_cacheDefiniton = $definition;
    } elseif (NULL == $this->_cacheDefiniton) {
      $definitionValues = array('acommunity_group_page');
      $ressource = $this->setRessourceData();
      if (isset($ressource->id)) {
        $command = $this->group()->parameters()->get('command', NULL);
        if (empty($command)) {
          include_once(dirname(__FILE__).'/../Cache/Identifier/Values.php');
          $values = new ACommunityCacheIdentifierValues();
          $definitionValues[] = $ressource->type;
          $definitionValues[] = $ressource->id;
          $definitionValues[] = $values->lastChangeTime('group:group_'.$ressource->id);
          $definitionValues[] = $values->lastChangeTime('group:memberships:group_'.$ressource->id);
          if ($ressource->validSurfer === 'is_owner') {
            $definitionValues[] = $values->lastChangeTime(
              'group:membership_requests:group_'.$ressource->id
            );
            $definitionValues[] = $values->lastChangeTime(
              'group:membership_invitations:group_'.$ressource->id
            );
          } elseif ($ressource->validSurfer === 'is_member') {
            $currentSurferId = $this->group()->data()->currentSurferId();
            $definitionValues[] = $values->lastChangeTime(
              'request:surfer_'.$currentSurferId.':group_'.$ressource->id
            );
            $definitionValues[] = $values->lastChangeTime(
              'invitation:group_'.$ressource->id.':surfer_'.$currentSurferId
            );
          }
        } else {
          $this->_cacheDefiniton = new PapayaCacheIdentifierDefinitionBoolean(FALSE);
        }
      }
      if (is_null($this->_cacheDefiniton)) {
        $this->_cacheDefiniton = new PapayaCacheIdentifierDefinitionGroup(
          new PapayaCacheIdentifierDefinitionValues($definitionValues),
          new PapayaCacheIdentifierDefinitionPage()
        );
      }
    }
    return $this->_cacheDefiniton;
  }

  /**
   * Check url name to fix wrong page names
   *
   * @param string $currentFileName
   * @param string $outputMode
   */
  public function checkURLFileName($currentFileName, $outputMode) {
    $this->setRessourceData();
    return $this->group()->checkURLFileName(
      $this, $currentFileName, $outputMode, 's-page'
    );
  }

  /**
   * Set group ressource data to load corresponding group
   */
  public function setRessourceData() {
    if (is_null($this->_ressource)) {
      $ressource = $this->group()->ressource();
      $ressource->set('group', array('group' => 'group_handle'), array('group' => 'group_handle'));
      $this->_ressource = $ressource;
    }
    return $this->_ressource;
  }

  /**
  * Get (and, if necessary, initialize) the ACommunityGroup object
  *
  * @return ACommunityGroup $group
  */
  public function group(ACommunityGroup $group = NULL) {
    if (isset($group)) {
      $this->_group = $group;
    } elseif (is_null($this->_group)) {
      include_once(dirname(__FILE__).'/../Group.php');
      $this->_group = new ACommunityGroup();
      $this->_group->module = $this;
      $this->_group->parameterGroup($this->paramName);
      $this->_group->data()->languageId = $this->papaya()->request->languageId;
    }
    return $this->_group;
  }

  /**
  * Get parsed data
  *
  * @return string $result
  */
  function getParsedData() {
    $this->initializeParams();
    $this->setRessourceData();
    $this->setDefaultData();
    $captionNames = array(
      'caption_time', 'caption_link_request_membership', 'caption_link_remove_membership_request',
      'caption_link_accept_membership_invitation', 'caption_link_invite_surfers',
      'caption_link_membership_request', 'caption_link_membership_requests',
      'caption_link_membership_invitation', 'caption_link_membership_invitations',
      'caption_link_member', 'caption_link_members', 'caption_link_owner',
      'caption_link_decline_membership_invitation'
    );
    $this->group()->data()->setPluginData(
      $this->data, $captionNames,
      array('message_access_denied', 'message_failed_to_execute_command')
    );
    return $this->group()->getXml();
  }
}