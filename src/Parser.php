<?php
/**
 * O2System
 *
 * An open source application development framework for PHP 5.4+
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
 * @package        O2System
 * @author         Circle Creative Dev Team
 * @copyright      Copyright (c) 2005 - 2015, PT. Lingkar Kreasi (Circle Creative).
 * @license        http://circle-creative.com/products/o2system-codeigniter/license.html
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://circle-creative.com/products/o2system-codeigniter.html
 * @since          Version 2.0
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System;

use O2System\Glob\Interfaces\Libraries;
use O2System\Glob\Exception;

/**
 * Parser Library
 *
 * @package          Template
 * @subpackage       Library
 * @category         Driver
 * @version          1.0 Build 11.09.2012
 * @author           Steeven Andrian Salim
 * @copyright        Copyright (c) 2005 - 2014 PT. Lingkar Kreasi (Circle Creative)
 * @license          http://www.circle-creative.com/products/o2system/license.html
 * @link             http://www.circle-creative.com
 */
// ------------------------------------------------------------------------
class Parser extends Libraries
{
    /**
     * Valid Drivers
     *
     * @access protected
     */
    protected $_valid_drivers = array(
        'standard',
        'dwoo',
        'mustache',
        'smarty',
        'twig',
        'shortcode'
    );

    /**
     * Parser Configuration
     *
     * @access protected
     */
    protected $_config = array(
        'driver'             => 'standard',
        'php'                => TRUE,
        'markdown'           => FALSE,
        'shortcode'          => FALSE,
        'rewrite_short_tags' => FALSE
    );


    /**
     * Active parser engine driver
     *
     * @access  protected
     *
     * @type string
     */
    protected $_driver;

    /**
     * List of possible view file extensions
     *
     * @access  protected
     *
     * @type array
     */
    public $extensions = array( '.php', '.html', '.tpl' );

    // ------------------------------------------------------------------------

    /**
     * Glob Libraries Class Constructor
     *
     * @access  public
     *
     * @uses    O2System\Core\Loader
     * @uses    O2System\Core\Gears\Logger
     *
     */
    public function __construct( $config = array() )
    {
        // Define Parser Glob Exception
        $exception = new Exception();
        $exception->register_path( __DIR__ . '/Views/' );
        $exception->register_handler();

        if( ! empty( $config ) )
        {
            $this->_config = array_merge( $this->_config, $config );
            $this->set_driver( $this->_config[ 'driver' ] );
        }
    }

    /**
     * Set Render Engine Driver
     *
     * @access  public
     *
     * @param   string $driver String of driver name
     *
     * @thrown  throw new Exception()
     *
     * @return \O2System\Libraries\Template Instance of O2System\Core\Template class
     */
    public function set_driver( $driver )
    {
        $driver = strtolower( $driver );

        if( ! in_array( $driver, $this->_valid_drivers ) )
        {
            throw new \BadMethodCallException( 'Unsupported Parser Driver: ' . $driver );
        }

        if( isset( $this->{$driver}->extensions ) )
        {
            $this->extensions = array_merge( $this->extensions, $this->{$driver}->extensions );
            $this->extensions = array_unique( $this->extensions );
        }

        $class_name = get_called_class() . '\\Drivers\\' . ucfirst( $driver );

        $this->_driver = new $class_name();
    }

    /**
     * Parse HTML Source Code
     *
     * Parse HTML source code using active parser engine
     *
     * @access  public
     *
     * @param string $source_code HTML source code
     * @param array  $vars        Array of parse data variables
     *
     * @return string
     */
    public function parse_source_code( $source_code = '', $vars = array() )
    {
        // Parse PHP
        if( $this->_config[ 'php' ] === TRUE )
        {
            $source_code = $this->parse_php( $source_code, $vars );
        }

        // Parse Markdown
        if( $this->_config[ 'markdown' ] === TRUE )
        {
            $source_code = $this->parse_markdown( $source_code );
        }

        // Parse Shortcode
        if( $this->_config[ 'shortcode' ] === TRUE )
        {
            $source_code = $this->parse_shortcode( $source_code );
        }

        // Parse String
        $source_code = $this->parse_string( $source_code, $vars );

        return $source_code;
    }

    /**
     * Parse String
     *
     * Parse String Syntax Code of Render Engine inside HTML source code
     *
     * @access  public
     *
     * @param string $source_code HTML Source Code
     * @param array  $vars        Array of parsing data variables
     *
     * @return string
     */
    public function parse_string( $source_code, $vars = array() )
    {
        return $this->_driver->parse_string( $source_code, $vars );
    }

    /**
     * Parse Markdown
     *
     * Parse Markdown Code inside HTML source code
     *
     * @access  public
     *
     * @param $source_code  HTML source code
     *
     * @return string
     */
    public function parse_markdown( $source_code )
    {
        if( class_exists( 'Parsedown', FALSE ) )
        {
            $source_code = htmlspecialchars_decode( $source_code );

            $markdown = new \Parsedown();

            return $markdown->text( $source_code );
        }

        return $source_code;
    }

    /**
     * Parse Shortcode
     *
     * Parse Wordpress a Like Shortcode Code inside HTML source code
     *
     * @access  public
     *
     * @param $source_code  HTML source code
     *
     * @return string
     */
    public function parse_shortcode( $source_code )
    {
        if( $shortcodes = $this->shortcode->fetch( $source_code ) )
        {
            $source_code = $this->shortcode->parse( $source_code );
        }

        // Fixed Output
        $source_code = str_replace( array( '_shortcode', '[?php', '?]' ), array( 'shortcode', '&lt;?php', '?&gt;' ),
                                    $source_code );

        return $source_code;
    }

    /**
     * Parse PHP
     *
     * Parse PHP Code inside HTML source code
     *
     * @access  public
     *
     * @param string $source_code HTML source code
     * @param array  $vars        Array of parse data variables
     *
     * @return string
     */
    public function parse_php( $source_code, $vars = array() )
    {
        $source_code = htmlspecialchars_decode( $source_code );

        extract( $vars );

        /*
         * Buffer the output
         *
         * We buffer the output for two reasons:
         * 1. Speed. You get a significant speed boost.
         * 2. So that the final rendered template can be post-processed by
         *  the output class. Why do we need post processing? For one thing,
         *  in order to show the elapsed page load time. Unless we can
         *  intercept the content right before it's sent to the browser and
         *  then stop the timer it won't be accurate.
         */
        ob_start();

        // If the PHP installation does not support short tags we'll
        // do a little string replacement, changing the short tags
        // to standard PHP echo statements.
        if( ! ini_get( 'short_open_tag' ) AND
            $this->_config[ 'rewrite_short_tags' ] === TRUE AND
            function_usable( 'eval' )
        )
        {
            echo eval( '?>' . preg_replace( '/;*\s*\?>/', '; ?>', str_replace( '<?=', '<?php echo ', $source_code ) ) );
        }
        else
        {
            echo eval( '?>' . preg_replace( '/;*\s*\?>/', '; ?>', $source_code ) );
        }

        $output = ob_get_contents();
        @ob_end_clean();

        return $output;
    }
}