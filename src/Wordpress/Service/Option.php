<?php

namespace Triangle\Wordpress\Service;

!defined( 'WPINC ' ) or die;

/**
 * Add extra layer for wordpress functions
 *
 * @package    Triangle
 * @subpackage Triangle\Wordpress
 */

class Option {

    /**
     * Retrieves an option value based on an option name.
     * @return  mixed       Value set for the option
     * @var     string      $option         Name of option to retrieve. Expected to not be SQL-escaped.
     * @var     array       $default    	Default value to return if the option does not exist.
     */
    public function get_option($option, $default = false){
        return get_option($option, $default);
    }

    /**
     * Retrieves an option value based on an option name.
     * @return  bool        False if value was not updated and true if value was updated.
     * @var     string      $option         Option name. Expected to not be SQL-escaped.
     * @var     array       $value      	Option value. Must be serializable if non-scalar. Expected to not be SQL-escaped.
     * @var     array       $autoload    	Whether to load the option when WordPress starts up.
     */
    public function update_option($option, $value, $autoload = null){
        return update_option($option, $value, $autoload);
    }

}