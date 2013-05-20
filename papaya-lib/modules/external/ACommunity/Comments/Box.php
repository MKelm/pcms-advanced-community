<?php
/**
 * Advanced community comments box
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
 * Basic box class
 */
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');

/**
 * Advanced community comments box
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityCommentsBox extends base_actionbox {
  
  /**
   * Parameter prefix name
   * @var string $paramName
   */
  public $paramName = 'acc';
  
  /**
   * Edit fields
   * @var array $editFields
   */
  public $editFields = array(
    'ressource_type' => array(
      'Ressource type', 'isAlpha', TRUE, 'combo', 
      array('page' => 'Page', 'surfer' => 'Surfer', 'image' => 'Image'), 
      NULL, 'page'
    ),
    'comments_per_page' => array(
      'Comments per page', 'isNum', TRUE, 'input', 30, '0 for all comments', 10
    ),
    'comments_per_page' => array(
      'Comments per page', 'isNum', TRUE, 'input', 30, '0 for all comments', 10
    ),
    'comments_per_comment' => array(
      'Comments per comment', 'isNum', TRUE, 'input', 30, '0 for all comments', 5
    ),
    'handle_deleted_surfer' => array(
      'Handle of deleted surfer', 'isAlphaNumChar', TRUE, 'input', 200, '', 'Deleted user'
    ),
    'Captions',
    'caption_dialog_text' => array(
      'Dialog Text', 'isNoHTML', TRUE, 'input', 200, '', 'Text'
    ),
    'caption_dialog_button' => array(
      'Dialog Button', 'isNoHTML', TRUE, 'input', 200, '', 'Add'
    ),
    'Messages',
    'message_dialog_input_error' => array(
      'Dialog Input Error', 'isNoHTML', TRUE, 'input', 200, '', 
      'Invalid input. Please check the field(s) "%s".'
    )
  );
  
  /**
   * Comments object
   * @var ACommunityComments
   */
  protected $_comments = NULL;
  
  /**
   * Get ressource data to load corresponding comments
   * Overwrite this method for customized ressources
   */
  public function setRessourceData() {
    switch ($this->data['ressource_type']) {
      case 'page':
        $this->comments()->data()->ressource('page', $this->papaya()->request->pageId);
        break;
      case 'surfer':
        if (!empty($this->parentObj->moduleObj->paramName)) {
          $surferParameters = $this->papaya()->request->getParameterGroup(
            $this->parentObj->moduleObj->paramName
          );
          $parameterNames = array('user_name', 'user_handle', 'surfer_handle');
          $hasUser = FALSE;
          foreach ($parameterNames as $parameterName) {
            $value = trim($surferParameters[$parameterName]);
            if (!empty($value)) {
              $this->comments()->data()->ressource('surfer', $value);  
              $hasUser = TRUE;
            }
          }
          if ($hasUser == TRUE) {
            $this->comments()->data()->ressourceParameters(
              $this->parentObj->moduleObj->paramName,
              $surferParameters
            );
          } else {
            $this->comments()->data()->ressource('surfer', NULL);  
          }
        }
        break;
      case 'image':
        if (!empty($this->parentObj->moduleObj->paramName)) {
          $mediaId = $this->parentObj->moduleObj->callbackGetCurrentImageId();
          if (!empty($mediaId)) {
            $this->comments()->data()->ressource('image', $mediaId);
            $this->comments()->data()->ressourceParameters(
              $this->parentObj->moduleObj->paramName,
              $this->parentObj->moduleObj->params
            ); 
          }
        }
        break;
    }
  }
  
  /**
  * Get (and, if necessary, initialize) the ACommunityComments object 
  * 
  * @return ACommunityComments $comments
  */
  public function comments(ACommunityComments $comments = NULL) {
    if (isset($comments)) {
      $this->_comments = $comments;
    } elseif (is_null($this->_comments)) {
      include_once(dirname(__FILE__).'/../Comments.php');
      $this->_comments = new ACommunityComments();
      $this->_comments->parameterGroup($this->paramName);
      $this->_comments->data()->setPluginData($this->data);
      $this->_comments->data()->languageId = $this->papaya()->request->languageId;
    }
    return $this->_comments;
  }

  /**
   * Get parsed data
   *
   * @return string $result XML
   */
  public function getParsedData() {
    $this->setDefaultData();
    $this->initializeParams();
    $this->setRessourceData();
    return $this->comments()->getXml();
  }
}
