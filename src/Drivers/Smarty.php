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

namespace O2System\Parser\Drivers;

// ------------------------------------------------------------------------

use O2System\Parser\Interfaces\Driver;

/**
 * Smarty Engine Adapter
 *
 * Parser Adapter for Smarty Engine
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
class Smarty extends Driver
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
        if( ! isset( static::$_engine ) )
        {
            static::$_engine = new \Smarty();
        }

        static::$_engine->setCompileDir( $settings[ 'cache' ][ 'compiler' ] );
        static::$_engine->setCacheDir( $settings[ 'cache' ][ 'path' ] );
        static::$_engine->caching = $settings[ 'cache' ][ 'enable' ];

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
        foreach( $vars as $_assign_key => $_assign_value )
        {
            static::$_engine->assign( $_assign_key, $_assign_value );
        }

        return static::$_engine->fetch( 'string:' . $string );
    }

    /**
     * Registers plugin to be used in templates
     *
     * @param string   $type       plugin type
     * @param string   $tag        name of template tag
     * @param callback $callback   PHP callback to register
     * @param boolean  $cacheable  if true (default) this fuction is cachable
     * @param array    $cache_attr caching attributes if any
     *
     * @throws SmartyException when the plugin tag is invalid
     */
    public function register_plugin()
    {
        @list( $type, $tag, $callback, $cacheable, $cache_attr ) = func_get_args();

        return static::$_engine->registerPlugin( $type, $tag, $callback, $cacheable, $cache_attr );
    }
}