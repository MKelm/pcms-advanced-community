<?php
/**
 * Advanced community surfer editor page
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
 * content_profile class to extend
 */
require_once(PAPAYA_INCLUDE_PATH.'modules/_base/community/content_profile.php');

/**
 * Advanced community surfer editor page
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunitySurferEditorPage extends content_profile implements PapayaPluginCacheable {

  /**
   * Use a advanced community parameter group name
   * @var string
   */
  public $paramName = 'acse';

  /**
   * Surfer object
   * @var ACommunitySurfer
   */
  protected $_surfer = NULL;

  /**
   * Cache definition
   * @var PapayaCacheIdentifierDefinition
   */
  protected $_cacheDefiniton = NULL;

  /**
   * Advanced Community connector
   * @var ACommunityConnector
   */
  protected $_acommunityConnector = NULL;

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
      $this->_cacheDefinition = $definition;
    } elseif (NULL == $this->_cacheDefinition) {
      $this->_cacheDefinition = new PapayaCacheIdentifierDefinitionBoolean(FALSE);
    }
    return $this->_cacheDefinition;
  }

  /**
   * Check url name to fix wrong page names
   *
   * @param string $currentFileName
   * @param string $outputMode
   */
  public function checkURLFileName($currentFileName, $outputMode) {
    $this->setRessourceData();
    return $this->surfer()->checkURLFileName($this, $currentFileName, $outputMode, 's-editor');
  }

  /**
   * Set surfer ressource data to load corresponding surfer
   */
  public function setRessourceData() {
    if (is_null($this->_ressource)) {
      $this->_ressource = $this->surfer()->ressource();
      $this->_ressource->set('surfer', NULL, array('surfer' => array()));
    }
    return $this->_ressource;
  }

  /**
  * Get (and, if necessary, initialize) the ACommunitySurfer object
  *
  * @return ACommunitySurfer $surfer
  */
  public function surfer(ACommunityComments $surfer = NULL) {
    if (isset($surfer)) {
      $this->_surfer = $surfer;
    } elseif (is_null($this->_surfer)) {
      include_once(dirname(__FILE__).'/../../Surfer.php');
      $this->_surfer = new ACommunitySurfer();
      $this->_surfer->parameterGroup($this->paramName);
      $this->_surfer->data()->languageId = $this->papaya()->request->languageId;
      $this->_surfer->module = $this;
    }
    return $this->_surfer;
  }

  /**
   * Extend save profile data to save surfer change time
   *
   * @return boolean
   */
  function saveProfileData() {
    $result = parent::saveProfileData();
    $surferId = !empty($this->papaya()->surfer->surfer['surfer_id']) ?
      $this->papaya()->surfer->surfer['surfer_id'] : NULL;
    if ($result == TRUE && !is_null($surferId)) {
      if ($this->_detectSurferNameChange()) {
        $this->surfer()->data()->setLastChangeTime('surfer_names');
      }
      $this->surfer()->data()->setLastChangeTime('surfer:surfer_'.$surferId);
    }
    return $result;
  }

  /**
   * Detect surfer name change to allow a correct cache invalidation in surfers page
   *
   * @return boolean
   */
  protected function _detectSurferNameChange() {
    $displayModeSurferName = $this->acommunityConnector()->getDisplayModeSurferName();
    switch ($displayModeSurferName) {
      case 'all':
        $nameFields = array('surfer_givenname', 'surfer_handle', 'surfer_surname');
        break;
      case 'names':
        $nameFields = array('surfer_givenname', 'surfer_surname');
        break;
      case 'handle':
        $nameFields = array('surfer_handle');
        break;
      case 'givenname':
        $nameFields = array('surfer_givenname');
        break;
      case 'surname':
        $nameFields = array('surfer_surname');
        break;
    }
    if (!empty($nameFields)) {
      $changed = FALSE;
      foreach ($nameFields as $nameField) {
        if ($this->papaya()->surfer->surfer[$nameField] != $this->profileForm->data[$nameField]) {
          $changed = TRUE;
        }
      }
      return $changed;
    }
    return FALSE;
  }

  /**
   * Get/set advanced community connector
   *
   * @param object $connector
   * @return object
   */
  public function acommunityConnector(ACommunityConnector $connector = NULL) {
    if (isset($connector)) {
      $this->_acommunityConnector = $connector;
    } elseif (is_null($this->_acommunityConnector)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
      $this->_acommunityConnector = base_pluginloader::getPluginInstance(
        '0badeb14ea2d41d5bcfd289e9d190534', $this
      );
    }
    return $this->_acommunityConnector;
  }
}