<?php if (!defined('BASEPATH')) exit('No direct script access allowed.');
/**
 * ErkanaAuth
 *
 * An easy-to-use authentication framework for CodeIgniter
 *
 * @package		ErkanaAuth
 * @author		Michael Wales
 * @copyright	Copyright (c) 2010, Michael D. Wales
 * @license		http://creativecommons.org/licenses/BSD/
 */
 
// ------------------------------------------------------------------------

/**
 * ErkanaAuth Helper
 *
 * @package		ErkanaAuth
 * @subpackage	Helpers
 * @category	Helpers
 * @author		Michael Wales
 */
 
// ------------------------------------------------------------------------

/**
 * Authentication Errors - Returns a carriage return separated list of 
 * authentication errors.
 *
 * @access		public
 * @return		mixed
 */
function authentication_errors() {
	$CI =& get_instance();
	
	if (class_exists('Erkana_auth')) {
		if (count($CI->erkana_auth->errors) > 0) {
			return implode('<br />', $CI->erkana_auth->errors);
		}
	}
	
	return NULL;
}

/* End of file erkana_auth_helper.php */
/* Location: ./application/helpers/erkana_auth_helper.php */