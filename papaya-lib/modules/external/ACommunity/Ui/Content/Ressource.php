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
   * Flag on invalid initialization
   * @var boolean
   */
  public $isInvalid = NULL;

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
   * A display mode of the ressource, e.g. to get different behaviours in box modules
   * @var string
   */
  public $displayMode = NULL;

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
   *
   * @param base_actionbox|base_content $module
   * @return ACommunityUiContentRessource
   */
  static public function getInstance($module = NULL) {
    if (NULL === self::$instance) {
      self::$instance = new self($module);
    }
    return self::$instance;
  }

  /**
  * Source parammeter group by page module
  * @var string
  */
  protected $_sourceParameterGroup = NULL;

  /**
   * Source parameters by page module
   * @var array
   */
  protected $_sourceParameters = NULL;

  /**
   * Module
   * @var base_actionbox|base_content
   */
  protected $_module = NULL;

  /**
   * Type of module
   * @var bolean
   */
  protected $_moduleIsPage = FALSE;

  /**
   * Set important source properties in connector
   *
   * @param base_actionbox|base_content $module
   */
  public function __construct($module) {
    $this->_module = $module;
    $this->_moduleIsPage = is_a($this->_module, 'base_content');
    $this->_initializeSourceParameters();
  }

  /**
   * Initialize source parameters by page module
   *
   * @param base_content|base_actionbox $module
   */
  protected function _initializeSourceParameters() {
    if (is_null($this->_sourceParameters) && is_null($this->_sourceParameterGroup)) {
      $this->_sourceParameterGroup = !$this->_moduleIsPage ?
        (isset($this->_module->parentObj->moduleObj->paramName) ?
          $this->_module->parentObj->moduleObj->paramName : NULL) : $this->_module->paramName;
      $this->_sourceParameters = !$this->_moduleIsPage ?
        (isset($this->_module->parentObj->moduleObj->params) ?
          $this->_module->parentObj->moduleObj->params : NULL) : $this->_module->params;
    }
  }

  /**
   * Gets a source parameter by name
   *
   * @param string $parameterName
   * @return string|NULL
   */
  public function getSourceParameter($parameterName) {
    if (isset($this->_sourceParameters[$parameterName])) {
      return $this->_sourceParameters[$parameterName];
    }
    return NULL;
  }

  /**
   * Checks if a source has a specific parameter set
   *
   * @param string $parameterName
   * @param boolean $notEmpty
   * @return boolean
   */
  public function sourceHasParameter($parameterName, $notEmpty = TRUE) {
    if (($notEmpty == TRUE && !empty($this->_sourceParameters[$parameterName])) ||
        ($notEmpty == FALSE && isset($this->_sourceParameters[$parameterName]))) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Checks if a source has a specific class name or returns class name on empty check
   *
   * @param string $className leave empty to get source class
   * @return boolean|string
   */
  public function sourceHasClass($classNameToCheck = NULL) {
    $className = !$this->_sourceIsPage ?
      get_class($this->_module->parentObj->moduleObj) : get_class($this->_module);
    if (isset($classNameToCheck)) {
      return $className == $classNameToCheck;
    } else {
      return $className;
    }
  }

  /**
   * Loads the display mode by source into $this->displayMode
   *
   * @param string $parameterName
   * @return boolean
   */
  public function loadSourceDisplayMode($parameterName) {
    $this->displayMode = $this->getSourceParameter($parameterName);
    if (!empty($this->displayMode)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Detect a stop parameter to make the current ressource invalid. You can use this in box modules
   * e.g. to make them invisible. You can set multiple parameter names to detect and multiple types
   * with a type selection. Use $overwriteProperties to reset id, type and handle.
   *
   * @param array|string $stopParameterNames array('type' => 'parameterNames') or 'parameterName(s)'
   * @param string $type a type to select if $stopParameterNames contains multiple types
   * @param boolean $overwriteProperties reset id, type and handle
   * @return boolean
   */
  public function detectStopParameter($stopParameterNames, $type = NULL, $overwriteProperties = FALSE) {
    if (empty($stopParameterNames)) {
      return FALSE;
    }
    if (isset($type)) {
      $stopParameterNames = isset($stopParameterNames[$type]) ? $stopParameterNames[$type] : array();
    }
    if (!empty($stopParameterNames) && !is_array($stopParameterNames)) {
      $stopParameterNames = array($stopParameterNames);
    }
    foreach ($stopParameterNames as $parameterName) {
      if (isset($this->_sourceParameters[$parameterName])) {
        if ($overwriteProperties) {
          $this->id = NULL;
          $this->type = NULL;
          $this->handle = NULL;
        }
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Detect a parameter value in source parameters by given parameter names. You can use a type
   * selection if you have multiple types in $sourceParameterNames. Use this method in box modules
   * to get another ressource value than the page module, if needed and if the box uses a ressource
   * singleton by the connector.
   *
   * @param array|string $sourceParameterNames array('type' => 'parameterName(s)') or 'parameterName(s)'
   * @param string $type a type to select if $sourceParameterNames contains multiple types
   * @return string $parameterValue
   */
  public function detectSourceParameterValue($sourceParameterNames, $type = NULL) {
    if (empty($sourceParameterNames)) {
      return NULL;
    }
    // determine ressource source parameter value by parameter names to get a ressource id
    if (isset($type)) {
      $sourceParameterNames = isset($sourceParameterNames[$type]) ? $sourceParameterNames[$type] : array();
    }
    if (!empty($sourceParameterNames) && !is_array($sourceParameterNames)) {
      $sourceParameterNames = array($sourceParameterNames);
    }
    foreach ($sourceParameterNames as $type => $parameterName) {
      if (!is_numeric($type)) {
        // detect sub parameter name without predefined type
        if (!empty($parameterName) && !is_array($parameterName)) {
          $parameterName = array($parameterName);
        }
        foreach ($parameterName as $subParameterName) {
          $value = isset($this->_sourceParameters[$subParameterName]) ?
            trim($this->_sourceParameters[$subParameterName]) : NULL;
          if (!empty($value)) {
            return array($type, $value);
          }
        }
      } else {
        // detect parameter name predefined type or if no multiple types have been set
        $value = isset($this->_sourceParameters[$parameterName]) ?
          trim($this->_sourceParameters[$parameterName]) : NULL;
        if (!empty($value)) {
          return $value;
        }
      }
    }
    return NULL;
  }

  /**
   * Filter source parameters by paramter names and optional ressource type selection.
   * You can overwrite the current ressource parameters by setting overwriteParameters to TRUE.
   * Use this method in box modules to set another parameter set than in page module
   * if this box module uses a ressource singleton by the connector.
   *
   * @param array|string $filterParameterNames array('type' => 'parameterName(s)') or 'parameterName(s)'
   * @param string $type a type to select if $filterParameterNames contains multiple types
   * @param boolean $overwriteParamters set new ressource parameters at the method end
   * @return array $filteredParameters
   */
  public function filterSourceParameters($filterParameterNames, $type = NULL, $overwriteParameters = FALSE) {
    $filteredParameters = $this->_sourceParameters;
    if (!isset($filterParameterNames)) {
      return $filteredParameters;
    }
    if (isset($type)) {
      $filterParameterNames = isset($filterParameterNames[$type]) ? $filterParameterNames[$type] : NULL;
    }
    if (isset($filterParameterNames) && !is_array($filterParameterNames)) {
      $filterParameterNames = array($filterParameterNames);
    }
    if (isset($filterParameterNames)) {
      $oldParameters = $this->_sourceParameters;
      $filteredParameters = array();
      foreach ($filterParameterNames as $parameterName) {
        if (isset($oldParameters[$parameterName])) {
          $filteredParameters[$parameterName] = $oldParameters[$parameterName];
        }
      }
      if ($overwriteParameters) {
        $this->parameters($this->_sourceParameterGroup, $filteredParameters);
      }
    }
    return $filteredParameters;
  }

  /**
   * Set data by module to initialize ressource.
   * Use this method if you want a standalone ressource only.
   * If you have dependend modules use a ressource singelton with the connector instead.
   * You can use detectStopParameter(), detectSourceParameterValue() and filterSourceParameters()
   * alone if you need customizations of the ressource in dependend modules.
   *
   * @param string $type page, surfer, group or image
   * @param array $sourceParameterNames A list of parameter names by ressource type to get ressource id
   * @param array $filterParameterNames A list of parameter names by ressource type to filter for ressource parameters
   * @param array $storpParameterNames A list of parameter names by ressource type to stop ressource detection
   */
  public function set(
          $type = NULL,
          $sourceParameterNames = NULL,
          $filterParameterNames = NULL,
          $stopParameterNames = NULL,
          $sourceParameterValue = NULL
         ) {
    if ($this->id === NULL && isset($type)) {
      if ($this->detectStopParameter($stopParameterNames, $type, TRUE)) {
        return FALSE;
      }
      if (empty($sourceParameterValue)) {
        $sourceParameterValue = $this->detectSourceParameterValue($sourceParameterNames, $type);
      }
      $filteredParameters = $this->filterSourceParameters($filterParameterNames, $type);
      switch ($type) {
        case 'surfer':
          if ($this->papaya()->surfer->isValid && !empty($this->papaya()->surfer->surfer['surfer_id'])) {
            $currentSurferId = $this->papaya()->surfer->surfer['surfer_id'];
          } else {
            $currentSurferId = NULL;
          }
          if (!empty($sourceParameterValue)) {
            $surferId = $this->uiContent->communityConnector()->getIdByHandle($sourceParameterValue);
          } else {
            $surferId = NULL;
          }
          if (!empty($surferId)) {
            $this->isActiveSurfer = $surferId == $currentSurferId;
          }
          if ($this->isActiveSurfer) {
            $this->id = $currentSurferId;
          } else {
            $this->id = $surferId;
          }
          if (!empty($this->id)) {
            $this->parameters($this->_sourceParameterGroup, $filteredParameters);
          }
          break;
        case 'image':
          if (!empty($sourceParameterValue)) {
            /**
             * Get the id if we have a predefined source parameter value, e.g. on a ajax request page.
             */
            $this->id = $sourceParameterValue;
          } elseif (!$this->_moduleIsPage && isset($this->_sourceParameters['enlarge']) &&
              method_exists($this->_module->parentObj->moduleObj, 'callbackGetCurrentImageId')) {
            /**
             * Get a image ressource in box modules by parent page module.
             * Needs a callbackGetCurrentImageId method, see ACommunitySurferGalleryPage
             */
            $this->id = $this->_module->parentObj->moduleObj->callbackGetCurrentImageId();
          }
          if (!empty($this->id)) {
            $this->parameters($this->_sourceParameterGroup, $filteredParameters);
          }
          break;
        case 'page':
          if (!empty($sourceParameterValue)) {
            /**
             * Get the id if we have a predefined source parameter value, e.g. on a ajax request page.
             */
            $this->id = (int)$sourceParameterValue;
          } else {
            $this->id = (int)$this->papaya()->request->pageId;
          }
          break;
        case 'group':
          if (!empty($sourceParameterValue)) {
            $this->id = (int)$this->uiContent->acommunityConnector()->getGroupIdByHandle(
              $sourceParameterValue
            );
            if (!empty($this->id)) {
              $this->parameters($this->_sourceParameterGroup, $filteredParameters);
            }
          }
          break;
      }
      if (!empty($this->id)) {
        $this->type = $type;
        if ($type == 'surfer' || $type == 'group') {
          $this->handle = $sourceParameterValue;
        }
        $this->isInvalid = FALSE;
        return TRUE;
      } else {
        $this->isInvalid = TRUE;
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