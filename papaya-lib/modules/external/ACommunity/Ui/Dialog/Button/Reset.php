<?php
/**
 * A simple reset button with a caption and without a name.
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
 * A simple reset button with a caption and without a name.
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityUiDialogButtonReset extends PapayaUiDialogButtonSubmit {

  /**
  * Button caption
  * @var string|PapayaUiString
  */
  protected $_caption = 'Reset';

  /**
  * Append button ouptut to DOM
  *
  * @param PapayaXmlElement $parent
  */
  public function appendTo(PapayaXmlElement $parent) {
    $parent->appendElement(
      'button',
      array(
        'type' => 'reset',
        'align' => ($this->_align == PapayaUiDialogButton::ALIGN_LEFT) ? 'left' : 'right'
      ),
      (string)$this->_caption
    );
  }
}