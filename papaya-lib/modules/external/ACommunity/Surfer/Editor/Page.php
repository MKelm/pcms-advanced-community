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
      $surferId = $this->papaya()->surfer->surfer['surfer_id'];
      $this->surfer()->data()->lastChange()->assign(
        array('ressource' => 'surfer:surfer_'.$surferId, 'time' => time())
      );
      $this->surfer()->data()->lastChange()->save();
    }
    return $result;
  }
}