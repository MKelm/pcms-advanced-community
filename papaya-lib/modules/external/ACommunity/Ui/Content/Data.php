<?php
/**
 * Advanced community content data object to handle plugin data and more
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
 * Class for changeable content data
 */
require_once(dirname(__FILE__).'/Data/Last/Change.php');

/**
 * Advanced community content data object to handle plugin data and more
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityUiContentData extends ACommunityUiContentDataLastChange {

  /**
   * Current language id
   * @var integer
   */
  public $languageId = 0;

  /**
  * Current surfer id
  * @var string
  */
  protected $_currentSurferId = NULL;

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
   * Parameters of owner module for use in sub-objects
   * @var array
   */
  protected $_referenceParameters = NULL;

  /**
   * A regular expression to filter reference parameters by name
   * @var string
   */
  protected $_referenceParametersExpression = NULL;

  /**
  * Reference object to create urls
  * @var PapayaUiReference
  */
  protected $_reference = NULL;

  /**
   * Buffer for surfers' data:
   * - Surfer name, depends on display mode for names module option
   * - Surfer avatar, depends on avatar size and resize mode if set
   * - Surfer page link, depends on surfer page id module option
   * @var array
   */
  protected $_surfers = NULL;

  /**
   * Size of surfer avatars
   * @var integer
   */
  protected $_surferAvatarSize = NULL;

  /**
   * Resize mode for surfer avatars
   * @var string
   */
  protected $_surferAvatarResizeMode = NULL;

  /**
   * Surfer gender titles
   * @var array
   */
  protected $_surferGenderTitles = array();

  /**
   * Moderator status
   * @var boolean
   */
  protected $_surferIsModerator = NULL;

  /**
   * Ressource object
   * @var ACommunityUiContentRessource
   */
  protected $_ressource = NULL;

  /** RESSOURCE PROPERTIES DEPRECATED **/
  public $ressourceIsActiveSurfer = FALSE;
  protected $_ressourceNeedsActiveSurfer = FALSE;
  /** RESSOURCE PROPERTIES DEPRECATED **/

  /**
   * Get surfer data by id depending on some module options
   *
   * @param array|string $surferId one id or multiple ids
   * @return array
   */
  public function getSurfer(
           $surferId, $deletedSurferHandle = NULL, $details = NULL, $extendedDetails = FALSE
         ) {
    $loadIds = array();
    if (is_array($surferId)) {
      foreach ($surferId as $id) {
        if (!isset($this->_surfers[$id])) {
          $loadIds[] = $id;
        }
      }
    } else {
      if (!isset($this->_surfers[$surferId])) {
        $loadIds = array($surferId);
      }
    }
    $this->_loadSurfers($loadIds, $deletedSurferHandle, $details, $extendedDetails);
    if (is_array($surferId)) {
      $result = array();
      foreach ($surferId as $id) {
        $result[$id] = $this->_surfers[$id];
      }
      return $result;
    } else {
      return $this->_surfers[$surferId];
    }
  }

  /**
   * Helper method for getSurfer to load surfers by loadIds
   *
   * @param array $loadIds
   * @param string $deletedSurferHandle
   * @param boolean $extendedDetails
   */
  protected function _loadSurfers($loadIds, $deletedSurferHandle, $details = NULL, $extendedDetails = FALSE) {
    if (!empty($loadIds)) {
      $avatarSize = isset($this->_surferAvatarSize) ? $this->_surferAvatarSize : 0;
      $avatarResizeMode = isset($this->_surferAvatarResizeMode) ? $this->_surferAvatarResizeMode : 'mincrop';
      $avatars = $this->owner->communityConnector()->getAvatar($loadIds, $avatarSize, TRUE, $avatarResizeMode);
      if (empty($details)) {
        $details = $extendedDetails ?
          $this->owner->communityConnector()->loadSurfers($loadIds) :
          $this->owner->communityConnector()->getNameById($loadIds);
      }
      $displayModeSurferName = $this->owner->acommunityConnector()->getDisplayModeSurferName();
      foreach ($loadIds as $loadId) {
        $surfer = array(
          'id' => $loadId,
          'name' => NULL,
          'avatar' => $avatars[$loadId],
          'page_link' => $this->owner->acommunityConnector()->getSurferPageLink($loadId)
        );
        if ($extendedDetails) {
          $surfer = array_merge(
            $surfer,
            array(
              'gender' => isset($this->_surferGenderTitles[$details[$loadId]['surfer_gender']]) ?
                $this->_surferGenderTitles[$details[$loadId]['surfer_gender']] : $details[$loadId]['surfer_gender'],
              'email' => $details[$loadId]['surfer_email'],
              'lastlogin' => date('Y-m-d H:i:s', $details[$loadId]['surfer_lastlogin']),
              'lastaction' => date('Y-m-d H:i:s', $details[$loadId]['surfer_lastaction']),
              'registration' => date('Y-m-d H:i:s', $details[$loadId]['surfer_registration']),
              'group' => $details[$loadId]['surfergroup_title']
            )
          );
        }
        $surfer['handle'] = isset($details[$loadId]['surfer_handle']) ? $details[$loadId]['surfer_handle'] : $deletedSurferHandle;
        $surfer['givenname'] = isset($details[$loadId]['surfer_givenname']) ? $details[$loadId]['surfer_givenname'] : NULL;
        $surfer['surname'] = isset($details[$loadId]['surfer_surname']) ? $details[$loadId]['surfer_surname'] : NULL;
        $surfer['name'] = $this->_getSurferName($surfer, $displayModeSurferName);
        $this->_surfers[$loadId] = $surfer;
      }
    }
  }

  /**
   * Get surfer name by surfer data and display mode surfer name option
   *
   * @param array $surfer
   * @param string $displayModeSurferName
   * @return string
   */
  protected function _getSurferName($surfer, $displayModeSurferName = NULL) {
    $name = NULL;
    $displayModeName = is_null($displayModeSurferName) ?
      $this->owner->acommunityConnector()->getDisplayModeSurferName() : $displayModeSurferName;
    switch ($displayModeName) {
      case 'all':
        $name = sprintf(
          "%s '%s' %s",
          isset($surfer['givenname']) ? $surfer['givenname'] : $surfer['surfer_givenname'],
          isset($surfer['handle']) ? $surfer['handle'] : $surfer['surfer_handle'],
          isset($surfer['surname']) ? $surfer['surname'] : $surfer['surfer_surname']
        );
        break;
      case 'names':
        $name = sprintf(
          "%s %s",
          isset($surfer['givenname']) ? $surfer['givenname'] : $surfer['surfer_givenname'],
          isset($surfer['surname']) ? $surfer['surname'] : $surfer['surfer_surname']
        );
        break;
      case 'handle':
        $name = isset($surfer['handle']) ? $surfer['handle'] : $surfer['surfer_handle'];
        break;
      case 'givenname':
        $name = isset($surfer['givenname']) ? $surfer['givenname'] : $surfer['surfer_givenname'];
        break;
      case 'surname':
        $name = isset($surfer['surname']) ? $surfer['surname'] : $surfer['surfer_surname'];
        break;
    }
    return $name;
  }

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
        $newName = substr($name, 8);
        $this->captions[$newName] = $data[$name];
      }
    }
    foreach ($messageNames as $name) {
      if (isset($data[$name])) {
        $newName = substr($name, 8);
        $this->messages[$newName] = $data[$name];
      }
    }
  }

  /**
   * Set/get data of current ressource by type and id
   *
   * @param string $type
   * @param object $module Modul object to get parameters from
   * @param array $parameterNames A list of parameter names by ressource type to get ressource id
   * @param array $filterParameterNames A list of parameter names by ressource type to filter for ressource parameters
   * @param array $storpParameterNames A list of parameter names by ressource type to stop ressource detection
   * @param string $returnType array or object
   * @param array|ACommunityUiContentRessource
   */
  public function ressource(
          $type = NULL,
          $module = NULL,
          $parameterNames = NULL,
          $filterParameterNames = NULL,
          $stopParameterNames = NULL,
          $returnType = 'array'
         ) {

    if ($type == 'ressource') {
      if (is_null($this->_ressource)) {
        include_once(dirname(__FILE__).'/Ressource.php');
        if (is_a($module, 'base_content')) {
          // use a singleton for page modules to get the same in connector for box modules
          $this->_ressource = ACommunityUiContentRessource::getInstance();
        } else {
          // compatibility for box modules with a different ressource handling
          // future plans: add support for nested ressources in ressource class
          $this->_ressource = new ACommunityUiContentRessource();
        }
        $this->_ressource->papaya($this->papaya());
        $this->_ressource->uiContent = $this->owner;
      }
      return $this->_ressource;
    } elseif (is_a($type, 'ACommunityUiContentRessource')) {
      $this->_ressource = $type; // set ressource in box modules by page module ressource
    } elseif (!empty($type) && !isset($this->_ressource->id) &&
              (!isset($this->_ressource->isInvalid) || $this->_ressource->isInvalid != TRUE)) {
      include_once(dirname(__FILE__).'/Ressource.php');
      if (is_a($module, 'base_content')) {
        // use a singleton for page modules to get the same in connector for box modules
        $this->_ressource = ACommunityUiContentRessource::getInstance();
      } else {
        // compatibility for box modules with a different ressource handling
        // future plans: add support for nested ressources in ressource class
        $this->_ressource = new ACommunityUiContentRessource();
      }
      $this->_ressource->papaya($this->papaya());
      $this->_ressource->uiContent = $this->owner;
      $this->_ressource->needsActiveSurfer = $this->_ressourceNeedsActiveSurfer; // backward compatibilty
      if (isset($type)) {
        $this->_ressource->set($type, $module, $parameterNames, $filterParameterNames, $stopParameterNames);
        $this->ressourceIsActiveSurfer = $this->_ressource->isActiveSurfer; // backward compatibilty
      }
    }
    if (isset($this->_ressource)) {
      if ($returnType == 'array') {
        return array(
          'type' => $this->_ressource->type,
          'id' => $this->_ressource->id,
          'handle' => $this->_ressource->handle
        );
      } else {
        return $this->_ressource;
      }
    }
    return NULL;
  }

  /**
   * Get/ set reference parameters for use in reference object
   *
   * Parameters of the owner module
   *
   * @param array $parameters
   * @retunr array
   */
  public function referenceParameters($parameters = NULL) {
    if (isset($parameters)) {
      $this->_referenceParameters = $parameters;
    } elseif (is_null($this->_referenceParameters)) {
      $this->_referenceParameters = array();
      if (!empty($this->_referenceParametersExpression)) {
        foreach ($this->owner->parameters() as $name => $value) {
          if (preg_match(sprintf('~%s~i', $this->_referenceParametersExpression), $name)) {
            $this->_referenceParameters[$name] = $value;
          }
        }
      }
    }
    return $this->_referenceParameters;
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
      $referenceParameters = $this->referenceParameters();
      if (!empty($referenceParameters)) {
        $this->_reference->setParameters(
          $referenceParameters, $this->owner->parameterGroup()
        );
      }
      if (method_exists($this->_ressource, 'parameters')) {
        foreach ($this->_ressource->parameters() as $parameterGroup => $parameters) {
          $this->_reference->setParameters(
            $parameters, $parameterGroup
          );
        }
      }
    }
    return $this->_reference;
  }

  /**
  * Get/set current surfer id
  *
  * @param string $currentSurferId
  * @return string
  */
  public function currentSurferId($currentSurferId = NULL) {
    if (isset($currentSurferId)) {
      $this->_currentSurferId = $currentSurferId;
    } elseif (is_null($this->_currentSurferId)) {
      if ($this->papaya()->surfer->isValid && !empty($this->papaya()->surfer->surfer['surfer_id'])) {
        $this->_currentSurferId = $this->papaya()->surfer->surfer['surfer_id'];
      }
    }
    return $this->_currentSurferId;
  }

  /**
   * Get moderator status
   *
   * @return boolean
   */
  public function surferIsModerator() {
    if (is_null($this->_surferIsModerator)) {
      if ($this->papaya()->surfer->isValid) {
        $this->_surferIsModerator = !empty($this->papaya()->surfer->surfer['surfergroup_id']) &&
          $this->papaya()->surfer->surfer['surfergroup_id'] ==
            $this->owner->acommunityConnector()->getModeratorGroupId();
      } else {
        $this->_surferIsModerator = FALSE;
      }
    }
    return $this->_surferIsModerator;
  }
}