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
 * @package		O2System
 * @author		Circle Creative Dev Team
 * @copyright	Copyright (c) 2005 - 2015, PT. Lingkar Kreasi (Circle Creative).
 * @license		http://circle-creative.com/products/o2system-codeigniter/license.html
 * @license	    http://opensource.org/licenses/MIT	MIT License
 * @link		http://circle-creative.com/products/o2system-codeigniter.html
 * @since		Version 2.0
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System;
defined( 'BASEPATH' ) OR exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use O2System\O2Glob\Libraries;

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
class O2Parser extends Libraries
{
    /**
     * Valid Drivers
     *
     * @access protected
     */
    protected $valid_drivers = array(
        'latte',
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
    public $config = array();


    /**
     * Active parser engine driver
     *
     * @access  protected
     *
     * @type string
     */
    protected $_driver = 'latte';

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
    public function __construct()
    {
        $controller =& get_instance();

        $controller->config->load( 'parser', TRUE );
        $this->config = $controller->config->item( 'parser' );

        $driver = empty( $this->config[ 'driver' ] ) ? 'latte' : $this->config[ 'driver' ];
        $this->set_driver( $driver );
    }

    /**
     * Set Render Engine Driver
     *
     * @access  public
     *
     * @param   string $driver String of driver name
     *
     * @thrown  show_error()
     *
     * @return \O2System\Libraries\Template Instance of O2System\Core\Template class
     */
    public function set_driver( $driver )
    {
        $driver = strtolower( $driver );

        if( ! in_array( $driver, $this->valid_drivers ) )
        {
            show_error( 'Unsupported Template Engine: ' . $driver );
        }

        if( isset( $this->{$driver}->extensions ) )
        {
            $this->extensions = array_merge( $this->extensions, $this->{$driver}->extensions );
            $this->extensions = array_unique( $this->extensions );
        }

        $this->_driver = $driver;

        return $this;
    }

    /**
     * Parse View File
     *
     * Parse view file using active render engine
     *
     * @access  public
     *
     * @param string $view String of view filename
     * @param array  $vars Array of data variables
     *
     * @thrown  show_error()
     *
     * @return string
     */
    public function parse_view( $view, $vars = array() )
    {
        if( file_exists( $view ) )
        {
            return $this->parse_source_code( file_get_contents( $view ), $vars );
        }

        show_error( 'Unable to load the requested view file: ' . $view );
    }

    /**
     * Parse Template File
     *
     * Parse template file using active render engine
     *
     * @access  public
     *
     * @param string $template Template filename
     * @param array  $partials Array of template partials
     * @param array  $vars     Array of template data variables
     *
     * @thrown  show_error()
     *
     * @return string
     */
    public function parse_template( $template, $partials = array(), $vars = array() )
    {
        $vars = array_merge( $partials, $vars );

        if( file_exists( $template ) )
        {
            return $this->parse_source_code( file_get_contents( $template ), $vars );
        }

        show_error( 'Unable to load the requested template file: ' . $template );
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
        if( $this->config[ 'php' ] === TRUE )
        {
            $source_code = $this->parse_php( $source_code, $vars );
        }

        // Parse String
        $source_code = $this->parse_string( $source_code, $vars );

        // Parse Markdown
        if( $this->config[ 'markdown' ] === TRUE )
        {
            $source_code = $this->parse_markdown( $source_code );
        }

        // Parse Shortcode
        if( $this->config[ 'shortcodes' ] === TRUE )
        {
            $source_code = $this->parse_shortcode( $source_code );
        }

        return $source_code;
    }

    protected function _parse( $output = '', $vars = array() )
    {
        // Parse PHP
        if( $this->config[ 'parser' ][ 'php' ] === TRUE )
        {
            $output = $this->parse_php( $output, $vars );
        }

        // Parse String
        $output = $this->parse_string( $output, $vars );

        // Parse Markdown
        if( $this->config[ 'parser' ][ 'markdown' ] === TRUE )
        {
            $output = $this->parse_markdown( $output );
        }

        // Parse Shortcode
        if( $this->config[ 'parser' ][ 'shortcodes' ] === TRUE )
        {
            $output = $this->parse_shortcode( $output );
        }

        return $output;
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
        return $this->{$this->_driver}->parse_string( $source_code, $vars );
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
        if(class_exists('Parsedown', FALSE))
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
         *	the output class. Why do we need post processing? For one thing,
         *	in order to show the elapsed page load time. Unless we can
         *	intercept the content right before it's sent to the browser and
         *	then stop the timer it won't be accurate.
         */
        ob_start();

        // If the PHP installation does not support short tags we'll
        // do a little string replacement, changing the short tags
        // to standard PHP echo statements.
        if( ! is_php( '5.4' ) && ! ini_get( 'short_open_tag' ) && config_item( 'rewrite_short_tags' ) === TRUE && function_usable( 'eval' ) )
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