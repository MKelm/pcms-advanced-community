<?php
/**
 * Advanced community surfer data class to handle all sorts of related data
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
require_once(dirname(__FILE__).'/../Ui/Content/Data.php');

/**
 * Advanced community surfer data class to handle all sorts of related data
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunitySurferData extends ACommunityUiContentData {

  /**
   * Surfer base details
   * @var array
   */
  public $surferBaseDetails = array();

  /**
   * Further surfer details by data classes/groups
   * @var array
   */
  public $surferDetails = array();

  /**
   * Current contact status with status value, commands and command links
   * @var array
   */
  public $contact = array();

  /**
   * Perform changes to contact data
   * @var ACommunitySurferContactChanges
   */
  protected $_contactChanges = NULL;

  /**
   * Link to send a message to surfer
   * @var string
   */
  public $sendMessageLink = NULL;

  /**
   * Dynamic data categories to load
   * @var array
   */
  protected $_dynamicDataCategories = NULL;

  /**
   * Mode status to get different outputs, surfer-details and surfer-bar
   * @var string
   */
  public $mode = 'surfer-details';

  /**
   * Set data by plugin object
   *
   * @param array $data
   * @param array $captionNames
   * @param array $messageNames
   */
  public function setPluginData($data, $captionNames = array(), $messageNames = array()) {
    if (isset($data['title_gender_male']) && isset($data['title_gender_female'])) {
      $this->_surferGenderTitles = array(
        'm' => $data['title_gender_male'],
        'f' => $data['title_gender_female']
      );
    }
    $this->_surferAvatarSize = (int)$data['avatar_size'];
    $this->_surferAvatarResizeMode = $data['avatar_resize_mode'];
    $this->_dynamicDataCategories = isset($data['dynamic_data_categories']) ?
      $data['dynamic_data_categories'] : NULL;
    parent::setPluginData($data, $captionNames, $messageNames);
  }

  /**
   * Intitialize surfer data
   */
  public function initialize() {
    $ressource = $this->owner->ressource();
    $this->surferBaseDetails = $this->getSurfer($ressource->id, NULL, NULL, TRUE);

    if ($this->mode == 'surfer-details') {
      if ($ressource->validSurfer !== 'is_selected') {
        $this->sendMessageLink = $this->owner->acommunityConnector()->getMessagesPageLink(
          $ressource->id
        );
      }

      $this->surferDetails = array();
      $details = $this->owner->communityConnector()->getProfileData($ressource->id);
      if (!empty($details)) {
        $groupIds = $this->owner->communityConnector()->getProfileDataClasses();
        foreach ($groupIds as $groupId) {
          if (in_array($groupId, $this->_dynamicDataCategories)) {
            $groupCaptions = $this->owner->communityConnector()->getProfileDataClassTitles($groupId);
            if (!empty($groupCaptions[$this->languageId])) {
              $this->surferDetails[$groupId] = array(
                'caption' => $groupCaptions[$this->languageId],
                'details' => array()
              );
              $detailNames = $this->owner->communityConnector()->getProfileFieldNames($groupId);
              foreach ($detailNames as $detailName) {
                $this->surferDetails[$groupId]['details'][$detailName] = NULL;
                $detailCaptions = $this->owner->communityConnector()->getProfileFieldTitles($detailName);
                if (!empty($detailCaptions[$this->languageId])) {
                  $this->surferDetails[$groupId]['details'][$detailName] = array(
                    'caption' => $detailCaptions[$this->languageId],
                    'value' => isset($details[$detailName]) ? $details[$detailName] : NULL
                  );
                }
              }
            }
          }
        }
      }

      if ($ressource->validSurfer !== 'is_selected') {
        $currentSurferId = $this->currentSurferId();
        if (!empty($currentSurferId)) {
          $contactStatus = 'none';
          $commandNames = array('request_contact');
          $isContact = $this->owner->communityConnector()->isContact(
            $currentSurferId, $ressource->id, FALSE, TRUE
          );
          switch ($isContact) {
            case SURFERCONTACT_PENDING + SURFERCONTACT_OWNREQUEST:
              $contactStatus = 'own_pending';
              $commandNames = array('remove_contact_request');
              break;
            case SURFERCONTACT_PENDING:
              $contactStatus = 'pending';
              $commandNames = array('accept_contact_request', 'decline_contact_request');
              break;
            case SURFERCONTACT_DIRECT:
              $contactStatus = 'direct';
              $commandNames = array('remove_contact');
              break;
            default:
          }

          $this->contact = array(
            'status' => $contactStatus,
            'commands' => array()
          );
          foreach ($commandNames as $commandName) {
            $reference = clone $this->reference();
            $reference->setParameters(
              array('command' => $commandName), $this->owner->parameterGroup()
            );
            $this->contact['commands'][$commandName] = $reference->getRelative();
          }
        }
      }
    }
  }

  /**
  * Perform changes to contact data
  *
  * @param ACommunitySurferContactChanges $changes
  * @return ACommunitySurferContactChanges
  */
  public function contactChanges(ACommunitySurferContactChanges $changes = NULL) {
    if (isset($changes)) {
      $this->_changes = $changes;
    } elseif (is_null($this->_changes)) {
      include_once(dirname(__FILE__).'/Contact/Changes.php');
      $this->_changes = new ACommunitySurferContactChanges();
      $this->_changes->papaya($this->papaya());
      $this->_changes->owner = $this->owner;
      $this->_changes->data = $this;
    }
    return $this->_changes;
  }

}