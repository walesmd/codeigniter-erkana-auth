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
 * Erkana Auth Class
 *
 * @package		ErkanaAuth
 * @subpackage	Libraries
 * @category	Authentication
 * @author		Michael Wales
 */
 
// ------------------------------------------------------------------------
class Erkana_auth {

	var $CI;
	var $errors					= array();
	
	// Default the accounts controller to something intelligent
	var $accounts_controller	= 'accounts';
	
	function Erkana_auth() {
		$this->CI =& get_instance();
		$this->CI->lang->load('erkana_auth', 'english');
		$this->CI->load->helper('erkana_auth');
	}

	/**
	 * Required - Enforces authentication on a controller method
	 *
	 * @access		public
	 * @return		void
	 */
	function required() {
		if (!$this->is_logged_in()) {
			if (!function_exists('redirect')) {
				$this->CI->load->helper('url');
			}
			
			redirect($this->accounts_controller);
		}
	}
	
	
	/**
	 * Accounts Controller - Sets the controller that processes your
	 * authentication forms.
	 *
	 * @access 		public
	 * @return		void
	 */
	function accounts_controller($controller) {
		$this->accounts_controller = $controller;
	}
	
	
	/**
	 * Is Logged In? - Checks if the user is logged in / valid session
	 *
	 * @access		public
	 * @return		bool
	 */
	function is_logged_in() {
		if (!class_exists('CI_Session')) {
			$this->CI->load->library('session');
		}
		
		// Check if there is any session data we can use
		if ($this->CI->session->userdata('user_id') && $this->CI->session->userdata('user_token')) {
			if (!class_exists('Account')) {
				$this->CI->load->model('account');
			}
			
			// Get a user account via the Account model
			$account = $this->CI->account->get($this->CI->session->userdata('user_id'));
			if ($account !== FALSE) {
				if (!function_exists('dohash')) {
					$this->CI->load->helper('security');
				}
				
				// Ensure user_token is still equivalent to the SHA1 of the user_id and password_hash
				if (dohash($this->CI->session->userdata('user_id') . $account->password_hash) === $this->CI->session->userdata('user_token')) {
					return TRUE;
				}
			}
		}
		
		return FALSE;
	}
	
	
	/**
	 * Validate Login - Checks authentication credentials against the database
	 *
	 * @access		public
	 * @param		string	the unique identifier: email or username
	 * @return		bool
	 */
	function validate_login($identifier = 'email') {
		if ($this->CI->input->post($identifier)) {
			if (!class_exists('Account')) {
				$this->CI->load->model('account');
			}
			
			$account = $this->CI->account->get_by(array($identifier => $this->CI->input->post($identifier)));
			if ($account !== NULL) {
				if (!function_exists('dohash')) {
					$this->CI->load->helper('security');
				}
				
				if (($account->$identifier === $this->CI->input->post($identifier)) && (dohash($account->salt . $this->CI->input->post('password')) === $account->password_hash)) {
					$this->_establish_session($account);
					return TRUE;
				}
			}
			
			$this->errors[] = $this->CI->lang->line('erkana_auth_invalid_login');
		}
		
		return FALSE;
	}
	
	
	/**
	 * Create Account - Will create an account if form validation requirements are met
	 *
	 * @access		public
	 * @param		string	the unique identifier: email or username
	 * @return		bool
	 */
	function create_account($identifier = 'email') {
		if (!class_exists('CI_Form_validation')) {
			$this->CI->load->library('form_validation');
		}
		
		if ($identifier == 'username') {
			$this->CI->form_validation->set_rules('username', 'username', 'required|min_length[4]|max_length[20]|trim');
		} else {
			$this->CI->form_validation->set_rules('email', 'email', 'required|max_length[120]|valid_email|trim');
		}
		$this->CI->form_validation->set_rules('password', 'password', 'required|matches[passwordconf]');
		$this->CI->form_validation->set_rules('passwordconf', 'password confirmation', 'required');
	
	
		if ($this->CI->form_validation->run()) {
			if (!class_exists('Account')) {
				$this->CI->load->model('account');
			}
			
			$account = $this->CI->account->get_by(array($identifier => $this->CI->input->post($identifier)));
			if ($account === NULL) {
				$salt = $this->_generate_salt();
				
				if (!function_exists('dohash')) {
					$this->CI->load->helper('security');
				}
				
				$account = array(
					$identifier		=> $this->CI->input->post($identifier),
					'salt'			=> $salt,
					'password_hash'	=> dohash($salt . $this->CI->input->post('password')));
				
				return $this->CI->account->create($account);
			}
			
			$this->errors[] = $this->CI->lang->line('erkana_auth_account_exists');
		}
		
		foreach ($this->CI->form_validation->_error_array as $error) {
			$this->errors[] = $error;
		}
		
		return FALSE;
	}
	
	
	/**
	 * Establish Session - Sets identifying information via the Session Class
	 *
	 * @access		private
	 * @return		void
	 */
	function _establish_session($account) {
		$this->CI->session->set_userdata(array(
			'user_id'	=> $account->id,
			'user_token'=> dohash($account->id . $account->password_hash)));
	}
	
	
	/**
	 * Generate Salt - Generates a random string to be used as a salt
	 *
	 * @access		private
	 * @return		string
	 */
	function _generate_salt() {
		if (!function_exists('random_string')) {
			$this->CI->load->helper('string');
		}
		
		return random_string('alnum', 7);
	}

}

/* End of file Erkana_auth.php */
/* Location: ./applicaiton/libraries/Erkana_auth.php */