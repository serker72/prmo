<?php
require_once('Smarty.class.php');

/**
 * Project:     Smarty: the PHP compiling template engine
 * File:        Smarty.class.php
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * For questions, help, comments, discussion, etc., please join the
 * Smarty mailing list. Send a blank e-mail to
 * smarty-discussion-subscribe@googlegroups.com 
 *
 * @link http://www.smarty.net/
 * @copyright 2001-2005 New Digital Group, Inc.
 * @author Monte Ohrt <monte at ohrt dot com>
 * @author Andrei Zmievski <andrei@php.net>
 * @package Smarty
 * @version 2.6.22
 */

/* $Id: Smarty.class.php 2785 2008-09-18 21:04:12Z Uwe.Tews $ */

/**
 * DIR_SEP isn't used anymore, but third party apps might
 */
if(!defined('DIR_SEP')) {
    define('DIR_SEP', DIRECTORY_SEPARATOR);
}

/**
 * set SMARTY_DIR to absolute path to Smarty library files.
 * if not defined, include_path will be used. Sets SMARTY_DIR only if user
 * application has not already defined it.
 */

if (!defined('SMARTY_DIR')) {
    define('SMARTY_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR);
}

if (!defined('SMARTY_CORE_DIR')) {
    define('SMARTY_CORE_DIR', SMARTY_DIR . 'internals' . DIRECTORY_SEPARATOR);
}

/**
 * @package Smarty
 */
class SmartyAj extends Smarty
{
    /**#@+
     * Smarty Configuration Section
     */

    /**
     * The name of the directory where templates are located.
     *
     * @var string
     */
    var $template_dir    =  '../tpl-sm';

    /**
     * The directory where compiled templates are located.
     *
     * @var string
     */
    var $compile_dir     =  '../tpl-sm/compile';

  

      /**
     * The left delimiter used for the template tags.
     *
     * @var string
     */
    var $left_delimiter  =  '%{';

    /**
     * The right delimiter used for the template tags.
     *
     * @var string
     */
    var $right_delimiter =  '}%';


}

/* vim: set expandtab: */

?>
