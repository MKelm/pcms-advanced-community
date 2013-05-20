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
class ACommunityCommentersRankingBox extends base_actionbox implements PapayaPluginCacheable {

  /**
   * Parameter prefix name
   * @var string $paramName
   */
  public $paramName = 'accmr';

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
      $this->_cacheDefiniton = $definition;
    } elseif (NULL == $this->_cacheDefiniton) {
      $this->_cacheDefiniton = new PapayaCacheIdentifierDefinitionValues(
        'acommunity_commenters_ranking_box'
      );
    }
    return $this->_cacheDefiniton;
  }

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
    }
    return $this->_ranking;
  }

  /**
   * Get parsed data
   *
   * @return string $result XML
   */
  public function getParsedData() {
    $this->initializeParams();
    $this->setDefaultData();
    $this->ranking()->data()->setPluginData($this->data, array('caption_comments'));
    return $this->ranking()->getXml();
  }
}