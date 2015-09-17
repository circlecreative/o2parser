<?php
/**
 * O2Parser
 *
 * An open source template engines driver for PHP 5.4+
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2015, PT. Lingkar Kreasi (Circle Creative).
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package     O2System
 * @author      Circle Creative Dev Team
 * @copyright   Copyright (c) 2005 - 2015, PT. Lingkar Kreasi (Circle Creative).
 * @license     http://circle-creative.com/products/o2parser/license.html
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        http://circle-creative.com/products/o2parser.html
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System\O2Parser\Drivers;
defined( 'BASEPATH' ) OR exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use O2System\O2Parser\Interfaces\Driver;

/**
 * Twig Engine Adapter
 *
 * Parser Adapter for Twig Engine
 *
 * @package       O2TED
 * @subpackage    drivers/Engine
 * @category      Adapter Class
 * @author        Steeven Andrian Salim
 * @copyright     Copyright (c) 2005 - 2014 PT. Lingkar Kreasi (Circle Creative)
 * @license       http://www.circle-creative.com/products/o2ted/license.html
 * @link          http://circle-creative.com
 *                http://o2system.center
 */
class Twig extends Driver
{
    /**
     * List of possible view file extensions
     *
     * @access  public
     *
     * @type array
     */
    public $extensions = array( '.php', '.html', '.tpl' );

    /**
     * Static Engine Object
     *
     * @access  private
     * @var  Engine Object
     */
    private static $_engine;

    /**
     * Setup Engine
     *
     * @param   $settings   Template Config
     *
     * @access  public
     * @return  Parser Engine Adapter Object
     */
    public function set( $settings = array() )
    {
        $twig = new \Twig_Loader_String();
        static::$_engine = new \Twig_Environment( $twig );

        return $this;
    }

    /**
     * Parse String
     *
     * @param   string   String Source Code
     * @param   array    Array of variables data to be parsed
     *
     * @access  public
     * @return  string  Parse Output Result
     */
    public function parse_string( $string, $vars = array() )
    {
        return static::$_engine->render( $string, $vars );
    }


    /**
     * Register Plugin
     *
     * Registers a plugin for use in a Twig template.
     *
     * @access  public
     */
    public function register_plugin()
    {
        list( $name ) = func_get_args();
        static::$_engine->addFunction( $name, new Twig_Function_Function( $name ) );
    }
}