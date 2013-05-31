<?php
/**
 * Advanced community content ressource
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
 * Advanced community content ressource
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityUiContentRessource extends PapayaObject {

  /**
   * Ui content class for connectors
   * @var ACommunityUiContent
   */
  public $uiContent = NULL;

  /**
   * Ressource type
   * @var string
   */
  public $type = NULL;

  /**
   * Ressource id
   * @var string|integer
   */
  public $id = NULL;

  /**
   * Ressource handle (optional if supported)
   * @var string
   */
  public $handle = NULL;

  /**
   * Ressource is active surfer
   * @var boolean
   */
  public $isActiveSurfer = FALSE;

  /**
   * Ressource needs active surfer
   * @var boolean
   */
  public $needsActiveSurfer = FALSE;

  /**
   * Parameters of owner box module's parent page module
   * @var array
   */
  protected $_parameters = array();

  /**
   * Singleton instance
   * @var object
   */
  static private $instance = NULL;

  /**
   * Get singleton instance
   * @return ACommunityUiContentRessource
   */
  static public function getInstance() {
    if (NULL === self::$instance) {
      self::$instance = new self;
    }
    return self::$instance;
  }

  /**
   * Set data of current ressource by type and id
   *
   * @param string $type
   * @param object $module Modul object to get parameters from
   * @param array $parameterNames A list of parameter names by ressource type to get ressource id
   * @param array $filterParameterNames A list of parameter names by ressource type to filter for ressource parameters
   * @param array $storpParameterNames A list of parameter names by ressource type to stop ressource detection
   */
  public function set(
          $type = NULL,
          $module = NULL,
          $parameterNames = NULL,
          $filterParameterNames = NULL,
          $stopParameterNames = NULL
         ) {
    if ($this->_ressource === NULL && isset($type)) {
      $isBoxModule = is_a($module, 'base_actionbox');
      $parameterValue = NULL;

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
            return FALSE;
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
        $filterParameterNames = isset($filterParameterNames[$type]) ? $filterParameterNames[$type] : NULL;
        if (isset($filterParameterNames) && !is_array($filterParameterNames)) {
          $filterParameterNames = array($filterParameterNames);
        }
        if (isset($filterParameterNames)) {
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
            $parameterId = $this->uiContent->communityConnector()->getIdByHandle($parameterValue);
            if ($this->needsActiveSurfer == FALSE) {
              $id = $parameterId;
              $this->parameters($parameterGroup, $parameters);
            }
          } else {
            $parameterId = NULL;
          }
          if ($this->papaya()->surfer->isValid && !empty($this->papaya()->surfer->surfer['surfer_id'])) {
            $this->isActiveSurfer = $parameterId == $this->papaya()->surfer->surfer['surfer_id'];
            if ($this->needsActiveSurfer == FALSE  || empty($parameterId) ||
                ($this->needsActiveSurfer == TRUE && $this->isActiveSurfer == TRUE)) {
              if (empty($id)) {
                $id = $this->papaya()->surfer->surfer['surfer_id'];
                $parameterValue = $this->papaya()->surfer->surfer['surfer_handle'];
                $this->parameters($parameterGroup, $parameters);
                $this->isActiveSurfer = $id == $this->papaya()->surfer->surfer['surfer_id'];
              }
            }
          }
          break;
        case 'image':
          /**
           * Get a image ressource in box modules by parent page module.
           * Needs a callbackGetCurrentImageId method, see ACommunitySurferGalleryPage
           */
          if ($isBoxModule && isset($parameters['enlarge']) &&
              method_exists($module->parentObj->moduleObj, 'callbackGetCurrentImageId')) {
            $id = $module->parentObj->moduleObj->callbackGetCurrentImageId();
            if (!empty($id)) {
              $this->parameters($parameterGroup, $parameters);
            }
          }
          break;
        case 'page':
          $id = $this->papaya()->request->pageId;
          break;
        case 'group':
          if (!empty($parameterValue)) {
            if (!empty($parameterValue)) {
              $id = $this->uiContent->acommunityConnector()->getGroupIdByHandle($parameterValue);
              if (!empty($id)) {
                $this->parameters($parameterGroup, $parameters);
              }
            }
          }
          break;
      }
      if (!empty($id)) {
        $this->type = $type;
        $this->id = $id;
        $this->handle = $parameterValue;
        return TRUE;
      }
    }
    return FALSE;
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
  public function parameters($parameterGroup = NULL, $parameters = NULL) {
    if (isset($parameterGroup) && isset($parameters)) {
      $this->_parameters[$parameterGroup] = $parameters;
    }
    return $this->_parameters;
  }
}