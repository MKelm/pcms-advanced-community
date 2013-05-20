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
 * Advanced community surfer data class to handle all sorts of related data
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunitySurferData extends PapayaObject {
  
  /**
   * Owner object
   * @var ACommunitySurferGalleryUpload
   */
  public $owner = NULL;
  
  /**
   * A list of captions to be used
   * @var array
   */
  public $captions = array();
  
  /**
   * A list of messages to be used
   * @var array
   */
  public $messages = array();
  
  /**
   * Gender titles
   * @var array
   */
  protected $_genderTitles = array();
  
  /**
   * Current language id
   * @var integer
   */
  public $languageId = 0;
  
  /**
  * Reference object to create urls
  * @var PapayaUiReference
  */
  protected $_reference = NULL;
  
  /**
   * Current comments ressource by type and id
   * @var array
   */
  protected $_ressource = NULL;
  
  /**
   * Ressource parameters
   * @var array
   */
  protected $_ressourceParameters = array();
  
  /**
   * Ressource is active surfer
   * @var boolean
   */
  public $ressourceIsActiveSurfer = FALSE;
  
  /**
   * Surfer base details
   * @var array
   */
  public $surferBaseDetails = array();
  
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
   * Set data by plugin object
   * 
   * @param array $data
   * @param array $captionNames
   * @param array $messageNames
   */
  public function setPluginData($data, $captionNames = array(), $messageNames = array()) {
    foreach ($captionNames as $name) {
      if (isset($data[$name])) {
        $newName = str_replace('caption_', '', $name);
        $this->captions[$newName] = $data[$name];
      }
    }
    foreach ($messageNames as $name) {
      if (isset($data[$name])) {
        $newName = str_replace('message_', '', $name);
        $this->messages[$newName] = $data[$name];
      }
    }
    $this->_genderTitles = array(
      'm' => $data['title_gender_male'],
      'f' => $data['title_gender_female']
    );
    $this->_avatarSize = (int)$data['avatar_size'];
    $this->_avatarResizeMode = $data['avatar_resize_mode'];
  }
  
  /**
   * Set/get data of current ressource by type and id
   * 
   * @param string $type
   * @param string $handle
   */
  public function ressource($type = 'surfer', $handle = NULL) {
    if (isset($type)) {
      if (isset($handle)) {
        $id = $this->owner->communityConnector()->getIdByHandle($handle);
      }
      $currentSurfer = $this->owner->communityConnector()->getCurrentSurfer();
      if (empty($handle) && !empty($currentSurfer->surfer['surfer_id']) && $currentSurfer->isValid) {
        $id = $currentSurfer->surfer['surfer_id'];
        $this->ressourceIsActiveSurfer = TRUE;
      } elseif (isset($id)) {
        $this->ressourceIsActiveSurfer = 
          $id == $currentSurfer->surfer['surfer_id'] && $currentSurfer->isValid;
      }
      if (isset($type) && isset($id)) {
        $this->_ressource = array(
         'type' => $type,
         'id' => $id
        );
      }
    }
    return $this->_ressource;
  }
  
  /**
   * Set ressource parameters for use in reference object
   * 
   * @param string $parameterGroup
   * @param array $parameters
   * @return array
   */
  public function ressourceParameters($parameterGroup = NULL, $parameters = NULL) {
    if (isset($parameterGroup) && isset($parameters)) {
      $this->_ressourceParameters[$parameterGroup] = $parameters;
    }
    return $this->_ressourceParameters;
  }
  
  /**
   * Intitialize surfer data
   */
  public function initialize() {
    $ressource = $this->ressource();
    $surferDetails = $this->owner->communityConnector()->loadSurfer($ressource['id']);
    
    $this->surferBaseDetails = array(
      'handle' => $surferDetails['surfer_handle'],
      'givenname' => $surferDetails['surfer_givenname'],
      'surname' => $surferDetails['surfer_surname'],
      'email' => $surferDetails['surfer_email'],
      'gender' => $this->_genderTitles[$surferDetails['surfer_gender']],
      'avatar' => $this->owner->communityConnector()->getAvatar(
        $ressource['id'], $this->_avatarSize, TRUE, $this->_avatarResizeMode
      ),
      'lastlogin' => date('Y-m-d H:i:s', $surferDetails['surfer_lastlogin']),
      'lastaction' => date('Y-m-d H:i:s', $surferDetails['surfer_lastaction']),
      'registration' => date('Y-m-d H:i:s', $surferDetails['surfer_registration']),
      'group' => $surferDetails['surfergroup_title']
    );
    unset($surferDetails);
    
    $this->surferDetails = array();
    $details = $this->owner->communityConnector()->getProfileData($ressource['id']);
    if (!empty($details)) {
      $groupIds = $this->owner->communityConnector()->getProfileDataClasses();
      foreach ($groupIds as $groupId) {
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
  
  
  /**
  * The basic reference object used by the subobjects to create urls.
  *
  * @param PapayaUiReference $reference
  * @return PapayaUiReference
  */
  public function reference(PapayaUiReference $reference = NULL) {
    if (isset($reference)) {
      $this->_reference = $reference;
    } elseif (is_null($this->_reference)) {
      $this->_reference = new PapayaUiReference();
      $this->_reference->papaya($this->papaya());
      foreach ($this->ressourceParameters() as $parameterGroup => $parameters) {
        $this->_reference->setParameters(
          $parameters, $parameterGroup
        );
      }
    }
    return $this->_reference;
  }
}
