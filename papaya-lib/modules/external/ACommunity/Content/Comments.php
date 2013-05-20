<?php
/**
 * Advanced community comments database records
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
 * Advanced community comments database records
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityContentComments extends PapayaDatabaseRecords {
  
  /**
   * Map field names to more convinient property names
   *
   * @var array(string=>string)
   */
  protected $_fields = array(
	  'id' => 'comment_id',
    'language_id' => 'language_id',
    'surfer_id' => 'surfer_id',
    'parent_id' => 'comment_parent_id',
    'ressource_id' => 'comment_ressource_id',
    'ressource_type' => 'comment_ressource_type',
    'time' => 'comment_time',
    'text' => 'comment_text',
    'votes_score' => 'comment_votes_score',
    'deleted_surfer' => 'comment_deleted_surfer'
  );

  /**
   * Table containing books
   *
   * @var string
   */
  protected $_tableName = 'acommunity_comments';

  /**
   * An array of properties, used to compile the identifer
   *
   * @var array(string)
   */
  protected $_identifierProperties = array('id');
  
  /**
   * Order by properties
   * @var array
   */
  protected $_orderByProperties = array(
    'time' => PapayaDatabaseInterfaceOrder::DESCENDING
  );
  
  /**
   * Set order by to get ranking of comments
   */
  public function setRankingOrder() {
    $this->_orderByProperties = array(
      'votes_score' => PapayaDatabaseInterfaceOrder::DESCENDING
    );
  }

}
