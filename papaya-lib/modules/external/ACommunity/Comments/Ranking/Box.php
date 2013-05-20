<?php
/**
 * Advanced community comments ranking box
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
 * Advanced community comments ranking box
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityCommentsRankingBox extends base_actionbox {
  
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
    'comments_per_page' => array(
      'Comments per page', 'isNum', TRUE, 'input', 30, '0 for all comments', 10
    ),
    'handle_deleted_surfer' => array(
      'Handle of deleted surfer', 'isAlphaNumChar', TRUE, 'input', 200, '', 'Deleted user'
    )
  );
  
  /**
   * Comments object
   * @var ACommunityComments
   */
  protected $_comments = NULL;
  
  /**
  * Get (and, if necessary, initialize) the ACommunityComments object 
  * 
  * @return ACommunityComments $comments
  */
  public function comments(ACommunityComments $comments = NULL) {
    if (isset($comments)) {
      $this->_comments = $comments;
    } elseif (is_null($this->_comments)) {
      include_once(dirname(__FILE__).'/../../Comments.php');
      $this->_comments = new ACommunityComments();
      $this->_comments->parameterGroup($this->paramName);
      $this->_comments->data()->setPluginData($this->data);
      $this->_comments->data()->languageId = $this->papaya()->request->languageId;
      $this->_comments->data()->mode = 'ranking';
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
    return $this->comments()->getXml();
  }
}
