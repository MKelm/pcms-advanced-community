<?php
/**
 * Advanced community content contains basic methods for further objects
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
 * Advanced community content contains basic methods for further objects
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityUiContent extends PapayaUiControlInteractive {

  /**
   * Community connector
   * @var connector_surfers
   */
  protected $_communityConnector = NULL;

  /**
   * Advanced Community connector
   * @var ACommunityConnector
   */
  protected $_acommunityConnector = NULL;

  /**
   * Notification handler
   * @var ACommunityNotificationHandler
   */
  protected $_notificationHandler = NULL;

  /**
   * Content data
   * @var ACommunityUiContentData
   */
  protected $_data = NULL;

  /**
   * base_actionbox or base_content module class
   * @var object
   */
  public $module = NULL;

  /**
   * Ressource object
   * @var ACommunityUiContentRessource
   */
  protected $_ressource = NULL;

  /**
   * Get/set content data
   *
   * @param ACommunityUiContentData $data
   * @return ACommunityUiContentData
   */
  public function data(ACommunityUiContentData $data = NULL) {
    if (isset($data)) {
      $this->_data = $data;
    } elseif (is_null($this->_data)) {
      include_once(dirname(__FILE__).'/Content/Data.php');
      $this->_data = new ACommunityUiContentData();
      $this->_data->papaya($this->papaya());
      $this->_data->owner = $this;
    }
    return $this->_data;
  }

  /**
   * Create dom node structure of the given object and append it to the given xml
   * element node.
   *
   * @param PapayaXmlElement $parent
   */
  public function appendTo(PapayaXmlElement $parent) {
  }

  /**
   * Get/set community connector
   *
   * @param object $connector
   * @return object
   */
  public function communityConnector(connector_surfers $connector = NULL) {
    if (isset($connector)) {
      $this->_communityConnector = $connector;
    } elseif (is_null($this->_communityConnector)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
      $this->_communityConnector = base_pluginloader::getPluginInstance(
        '06648c9c955e1a0e06a7bd381748c4e4', $this
      );
    }
    return $this->_communityConnector;
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

  /**
   * Get/set notification handler
   *
   * @param ACommunityNotificationHandler $handler
   * @return ACommunityNotificationHandler
   */
  public function notificationHandler(ACommunityNotificationHandler $handler = NULL) {
    if (isset($handler)) {
      $this->_notificationHandler = $handler;
    } elseif (is_null($this->_notificationHandler)) {
      include_once(dirname(__FILE__).'/../Notification/Handler.php');
      $this->_notificationHandler = new ACommunityNotificationHandler();
      $this->_notificationHandler->papaya($this->papaya());
    }
    return $this->_notificationHandler;
  }

  /**
   * Check url file name in for page modules and return new url if the current file name is invalid
   *
   * @param base_content $pageModule
   * @param string $currentFileName
   * @param string $outputMode
   * @param string $pageNamePostfix
   * @param string $handle
   * @return string|FALSE
   */
  public function checkURLFileName(
           $pageModule, $currentFileName, $outputMode, $pageNamePostfix, $handle = NULL
         ) {
    $ressource = $this->data()->ressource();
    if (!empty($handle) || !empty($ressource['handle'])) {
      $handle = empty($handle) ? $ressource['handle'] : $handle;
      $ressourcePageName = $pageModule->parentObj->escapeForFilename($handle.$pageNamePostfix);
      if ($currentFileName == $ressourcePageName) {
        return FALSE;
      } else {
        return $pageModule->getWebLink(
          $pageModule->parentObj->topic['topic_id'], NULL, $outputMode,
          array($ressource['type'].'_handle' => $ressource['handle']),
          $pageModule->paramName, $ressourcePageName
        );
      }
    }
    $pageFileName = $pageModule->parentObj->escapeForFilename(
      $pageModule->parentObj->topic['TRANSLATION']['topic_title'],
      'index',
      $pageModule->parentObj->currentLanguage['lng_ident']
    );
    return $pageModule->getWebLink(
      $pageModule->parentObj->topic['topic_id'], NULL, $outputMode, NULL, NULL, $pageFileName
    );
  }

  /**
   * Get / set ressource of current request.
   *
   * Use this method in all modules to get a valid ressource. You can set $ressource->pointer = 0
   * if you want to use the ressource data of the page module. Use $ressource->set to initialize
   * new ressource data. You can use previous ressource properties with set, to
   * get an abreviation of the previouse ressource. See ACommunityUiContentRessourceTests
   * ->testScenarioImageGalleryPageWithBoxDependencies for an example of using ressources with
   * dependencies.
   *
   * @param ACommunityUiContentRessource $ressource
   * @return ACommunityUiContentRessource
   */
  public function ressource(ACommunityUiContentRessource $ressource = NULL) {
    if (isset($ressource)) {
      $this->_ressource = $ressource;
    } elseif (is_null($this->_ressource)) {
      include_once(dirname(__FILE__).'/Content/Ressource.php');
      $this->_ressource = ACommunityUiContentRessource::getInstance($module);
      $this->_ressource->papaya($this->papaya());
      $this->_ressource->uiContent = $this;
    }
    return $this->_ressource;
  }
}