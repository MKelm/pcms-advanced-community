<?php
/**
 * Advanced community commenters ranking box
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
 * Advanced community commenters ranking box
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityCommentersRankingBox extends base_actionbox {

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
    'commenters_limit' => array(
      'Commenters limit', 'isNum', TRUE, 'input', 30, '0 for all commenters', 10
    ),
    'avatar_size' => array(
      'Avatar Size', 'isNum', TRUE, 'input', 30, '', 40
    ),
    'avatar_resize_mode' => array(
      'Avatar Resize Mode', 'isAlpha', TRUE, 'translatedcombo',
       array(
         'abs' => 'Absolute', 'max' => 'Maximum', 'min' => 'Minimum', 'mincrop' => 'Minimum cropped'
       ), '', 'mincrop'
    ),
    'Captions',
    'caption_comments' => array(
      'Comments', 'isNoHTML', TRUE, 'input', 200, '', 'Comment(s)'
    )
  );

  /**
   * Ranking object
   * @var ACommunityCommentersRanking
   */
  protected $_ranking = NULL;

  /**
  * Get (and, if necessary, initialize) the ACommunityCommentersRanking object
  *
  * @return ACommunityCommentersRanking $ranking
  */
  public function ranking(ACommunityCommentersRanking $ranking = NULL) {
    if (isset($ranking)) {
      $this->_ranking = $ranking;
    } elseif (is_null($this->_ranking)) {
      include_once(dirname(__FILE__).'/../Ranking.php');
      $this->_ranking = new ACommunityCommentersRanking();
      $this->_ranking->parameterGroup($this->paramName);
      $this->_ranking->data()->setPluginData($this->data, array('caption_comments'));
    }
    return $this->_ranking;
  }

  /**
   * Get parsed data
   *
   * @return string $result XML
   */
  public function getParsedData() {
    $this->setDefaultData();
    $this->initializeParams();
    return $this->ranking()->getXml();
  }

}
