<?php
/**
 * Advanced community ui content comments
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

// ui control collection paging
require_once(dirname(__FILE__).'/../Control/Collection/Paging.php');

/**
 * Advanced community ui content comments
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityUiContentComments extends ACommunityUiControlCollectionPaging {

  /**
  * Only {@see PapayaModuleGuestbookUiContentBookEntry} objects are allowed in this list
  *
  * @var string
  */
  protected $_itemClass = 'ACommunityUiContentComment';

  /**
  * If a tag name is provided, an additional element will be added in
  * {@see PapayaUiControlCollection::appendTo()) that will wrapp the items.
  * @var string
  */
  protected $_tagName = 'comments';

  /**
   * Parameter name to change page
   * @var string
   */
  public $pagingParameterName = 'comments_page';
}