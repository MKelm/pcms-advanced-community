<?php
/**
 * Advanced community ui control collection with paging support
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
 * Advanced community ui control collection with paging support
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityUiControlCollectionPaging extends PapayaUiControlCollection {

  /**
  * Paging object
  *
  * @var PapayaUiPagingCount
  */
  protected $_paging = NULL;

  /**
   * Paging parameter group name
   * @var string
   */
  public $pagingParameterGroup = NULL;

  /**
   * Parameter name to change page
   * @var string
   */
  public $pagingParameterName = 'page';

  /**
   * Items per page
   * @var integer
   */
  public $pagingItemsPerPage = NULL;

  /**
   * Get relative paging urls
   * @var boolean
   */
  public $pagingGetRelativeUrls = TRUE;

  /**
   * Absolute count of items
   * @var integer
   */
  public $absCount = NULL;

  /**
  * Reference object to create urls
  * @var PapayaUiReference
  */
  protected $_reference = NULL;

  /**
   * Append comments output to parent element.
   *
   * @param PapayaXmlElement $parent
   * @return PapayaXmlElement|NULL parent the elements where appended to,
   *    NULL if no items are appended.
   */
  public function appendTo(PapayaXmlElement $parent) {
    if (count($this->_items) > 0) {
      $parent = $parent->appendElement(
        $this->_tagName
      );
      $this->paging()->appendTo($parent);
      foreach ($this->_items as $item) {
        $item->appendTo($parent);
      }
      return $parent;
    }
    return NULL;
  }

  /**
   *
   * Paging object
   *
   * @param PapayaUiPagingCount $paging
   */
  public function paging(PapayaUiPagingCount $paging) {
    if (isset($paging)) {
      $this->_paging = $paging;
    } elseif (is_null($this->_paging)) {
      $parameter = sprintf(
        '%s[%s]', $this->pagingParameterGroup, $this->pagingParameterName
      );
      $this->_paging = new PapayaUiPagingCount(
        $parameter,
        $this->papaya()->request->getParameter($parameter),
        $this->absCount
      );
      $this->_paging->papaya($this->papaya());
      $this->_paging->itemsPerPage = $this->pagingItemsPerPage;
      $this->_paging->relativeUrls = $this->pagingGetRelativeUrls;
      $this->_paging->reference($this->reference());
    }
    return $this->_paging;
  }

  /**
  * The basic reference object used by the subobjects to create urls.
  *
  * @param PapayaUiReference $reference
  * @return PapayaUiReference
  */
  public function reference(PapayaUiReference $reference = NULL) {
    if (isset($reference)) {
      $this->_reference = $reference;
    } elseif (is_null($this->_reference)) {
      $this->_reference = new PapayaUiReference();
      $this->_reference->papaya($this->papaya());
    }
    return $this->_reference;
  }
}