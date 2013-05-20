<?php
/**
 * Advanced community comment database record
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
 * @subpackage External-Guestbook
 */

/**
 * Advanced community comment database record
 *
 * @package Papaya-Modules
 * @subpackage External-Guestbook
 */
class ACommunityContentComment extends PapayaDatabaseRecord {

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
   * Table containing book
   *
   * @var string
   */
  protected $_tableName = 'acommunity_comments';
}
