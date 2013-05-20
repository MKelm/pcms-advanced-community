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
 * Advanced community content data object to handle plugin data and more
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityUiContentData extends PapayaObject {

  /**
   * Owner object
   * @var ACommunityUiContent
   */
  public $owner = NULL;

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
   * Current ressource by type and id
   * @var array
   */
  protected $_ressource = NULL;

  /**
   * Parameters of owner box module's parent page module
   * @var array
   */
  protected $_ressourceParameters = array();

  /**
   * Ressource is active surfer
   * @var boolean
   */
  public $ressourceIsActiveSurfer = FALSE;

  /**
   * Ressource needs active surfer
   * @var boolean
   */
  protected $_ressourceNeedsActiveSurfer = FALSE;

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
  }

  /**
   * Set/get data of current ressource by type and id
   *
   * @param string $type
   * @param object $module Modul object to get parameters from
   * @param array $parameterNames A list of parameter names by ressource type to get ressource id
   * @param array $filterParameterNames A list of parameter names by ressource type to filter for ressource parameters
   * @param array $storpParameterNames A list of parameter names by ressource type to stop ressource detection
   * @param array
   */
  public function ressource(
          $type = NULL,
          $module = NULL,
          $parameterNames = array(),
          $filterParameterNames = array(),
          $stopParameterNames = array()
         ) {
    if ($this->_ressource === NULL && isset($type)) {
      $isBoxModule = is_a($module, 'base_actionbox');

      if ($type != 'page') {
        // determine parameters to get ressource data from
        $parameterGroup = $isBoxModule ?
          (isset($module->parentObj->moduleObj->paramName) ? $module->parentObj->moduleObj->paramName : NULL)
          : $module->paramName;
        $parameters = $isBoxModule ?
          (isset($module->parentObj->moduleObj->params) ? $module->parentObj->moduleObj->params : NULL)
          : $module->params;
        // detect stop parameter to make ressource invalid
        $stopParameterNames = isset($stopParameterNames[$type]) ? $stopParameterNames[$type] : array();
        if (!empty($stopParameterNames) && !is_array($stopParameterNames)) {
          $stopParameterNames = array($stopParameterNames);
        }
        foreach ($stopParameterNames as $parameterName) {
          if (isset($parameters[$parameterName])) {
            $this->_ressource = FALSE;
            return $this->_ressource;
          }
        }
        // determine ressource source parameter value by parameter names to get a ressource id
        $parameterNames = isset($parameterNames[$type]) ? $parameterNames[$type] : array();
        if (!empty($parameterNames) && !is_array($parameterNames)) {
          $parameterNames = array($parameterNames);
        }
        $parameterValue = NULL;
        foreach ($parameterNames as $parameterName) {
          $value = isset($parameters[$parameterName]) ? trim($parameters[$parameterName]) : NULL;
          if (!empty($value)) {
            $parameterValue = $value;
            break;
          }
        }
        // filter parameters to use in reference later
        $filterParameterNames = isset($filterParameterNames[$type]) ? $filterParameterNames[$type] : array();
        if (!empty($filterParameterNames) && !is_array($filterParameterNames)) {
          $filterParameterNames = array($filterParameterNames);
        }
        if (!empty($filterParameterNames)) {
          $oldParameters = $parameters;
          $parameters = array();
          foreach ($filterParameterNames as $parameterName) {
            if (isset($oldParameters[$parameterName])) {
              $parameters[$parameterName] = $oldParameters[$parameterName];
            }
          }
          unset($oldParameters);
        }
      }

      switch ($type) {
        case 'surfer':
          // parameter value must contain a valid surfer handle
          $id = NULL;
          if (!empty($parameterValue)) {
            $parameterId = $this->owner->communityConnector()->getIdByHandle($parameterValue);
            if ($this->_ressourceNeedsActiveSurfer == FALSE) {
              $id = $parameterId;
              $this->ressourceParameters($parameterGroup, $parameters);
            }
          } else {
            $parameterId = NULL;
          }
          $currentSurfer = $this->owner->communityConnector()->getCurrentSurfer();
          if (!empty($currentSurfer->surfer['surfer_id']) && $currentSurfer->isValid) {
            $this->ressourceIsActiveSurfer = $parameterId == $currentSurfer->surfer['surfer_id'];
            if ($this->_ressourceNeedsActiveSurfer == FALSE  || empty($parameterId) ||
                ($this->_ressourceNeedsActiveSurfer == TRUE && $this->ressourceIsActiveSurfer == TRUE)) {
              if (empty($id)) {
                $id = $currentSurfer->surfer['surfer_id'];
                $this->ressourceParameters($parameterGroup, $parameters);
                $this->ressourceIsActiveSurfer = $id == $currentSurfer->surfer['surfer_id'];
              }
            }
          }
          break;
        case 'image':
          /**
           * Get a image ressource in box modules by parent page module.
           * Needs a callbackGetCurrentImageId method, see ACommunitySurferGalleryPage
           */
          if ($isBoxModule &&
              method_exists($module->parentObj->moduleObj, 'callbackGetCurrentImageId')) {
            $id = $module->parentObj->moduleObj->callbackGetCurrentImageId();
            if (!empty($id)) {
              $this->ressourceParameters($parameterGroup, $parameters);
            }
          }
          break;
        case 'page':
          $id = $this->papaya()->request->pageId;
          break;
      }
      if (!empty($id)) {
        $this->_ressource = array('type' => $type, 'id' => $id);
      } else {
        $this->_ressource = FALSE;
      }
    }
    return $this->_ressource;
  }

  /**
   * Set ressource parameters for use in reference object
   *
   * Parameters of the owner box module's parent page module
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
      foreach ($this->ressourceParameters() as $parameterGroup => $parameters) {
        $this->_reference->setParameters(
          $parameters, $parameterGroup
        );
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
      $currentSurfer = $this->owner->communityConnector()->getCurrentSurfer();
      if ($currentSurfer->isValid && !empty($currentSurfer->surfer['surfer_id'])) {
        $this->_currentSurferId = $currentSurfer->surfer['surfer_id'];
      }
    }
    return $this->_currentSurferId;
  }

}
