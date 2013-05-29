<?php
/**
 * A single line input for a file upload
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
 * A single line input for a file upload
 *
 * @package Papaya-Modules
 * @subpackage External-ACommunity
 */
class ACommunityUiDialogFieldInputFile extends PapayaUiDialogFieldInput {

  /**
  * Field type, used in template
  *
  * @var boolean
  */
  protected $_type = 'file';

  public function __construct($caption, $name, $mandatory = FALSE) {
    parent::__construct($caption, $name);
    $this->setMandatory($mandatory);
  }

}
