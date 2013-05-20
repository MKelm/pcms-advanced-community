<?php
/**
 * Advanced community commenters ranking
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
 * Base ui content object
 */
require_once(dirname(__FILE__).'/../Ui/Content/Object.php');

/**
 * Advanced community  commenters ranking
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityCommentersRanking extends ACommunityUiContentObject {

  /**
   * Comments data
   * @var ACommunityCommentsData
   */
  protected $_data = NULL;

  /**
   * Get/set commenters rankuing data
   *
   * @param ACommunityCommentersRankingData $data
   * @return ACommunityCommentersRankingData
   */
  public function data(ACommunityCommentersRankingData $data = NULL) {
    if (isset($data)) {
      $this->_data = $data;
    } elseif (is_null($this->_data)) {
      include_once(dirname(__FILE__).'/Ranking/Data.php');
      $this->_data = new ACommunityCommentersRankingData();
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
    $ranking = $parent->appendElement('acommunity-commenters-ranking');
    $this->data()->commentersRanking()->load(array('deleted_surfer' => 0), $this->data()->commentersLimit);
    $commentersRanking = $this->data()->commentersRanking()->toArray();
    if (!empty($commentersRanking)) {
      foreach ($commentersRanking as $id => $commenter) {
        $commenterElement = $ranking->appendElement('commenter');
        $commenterElement->appendElement(
          'comments',
          array('caption' => $this->data()->captions['comments'], 'amount' => $commenter['comments_amount'])
        );
        $surfer = $this->data()->getSurfer($id);
        $commenterElement->appendElement(
          'surfer',
          array(
            'name' => $surfer['name'],
            'avatar' => PapayaUtilStringXml::escapeAttribute($surfer['avatar']),
            'page-link' => PapayaUtilStringXml::escapeAttribute($surfer['page_link'])
          )
        );
      }
    }
  }

}