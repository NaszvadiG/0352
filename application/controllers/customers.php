<?php
require_once(APPPATH.'controllers/abstract/front.php');

/** 
 * This is controller for customers' accounts.
 * 
 * @package customers  
 * @author Michael Kovalskiy
 * @version 2011
 * @access public
 */
class Customers extends Front 
{
	//name of table
    protected $c_table = "customers";

    /**
     * Init required models, helpers, language sections, pages' titles, css files etc.
     *
     * @return \Customers
     */
	public function __construct()
	{
		parent::__construct();
		
		// === Load Models === //
		$this->load->model('customers_model');
		$this->load->model('subscribers_model');
		$this->load->model('captcha_model');
		$this->load->model('groups_model');
		
		// === Init Language Section === //
		$this->lang_model->init(array('label','front','users','posters','job'));
		
		// === Labels === //
		$this->fields_titles['name'] = language('name');
		$this->fields_titles['surname'] = language('surname');
		$this->fields_titles['email'] = language('email');
		$this->fields_titles['password'] = language('password');
		$this->fields_titles['repassword'] = language('repassword');
		$this->fields_titles['phone'] = language('phone');
		$this->fields_titles['phone2'] = language('phone2');
		$this->fields_titles['website'] = language('website');
		$this->fields_titles['city'] = language('city');
		$this->fields_titles['address'] = language('address');
		$this->fields_titles['zip_code'] = language('zip_code');
		$this->fields_titles['reg_date'] = language('reg_date');
		$this->fields_titles['captcha'] = language('captcha');
		
		// === Page Titles === //
		$this->page_titles['index'] = language('my_account');
		$this->page_titles['editinfo'] = language('edit_my_info');
		//default page title
		$this->_setDefaultPageTitle();
	}

	// +++++++++++++ INNER METHODS +++++++++++++++ //

	// === Custom validation : Start === //	
	/**
	 * Validate login.
	 * 
	 * @param string $password
	 * @param string $email
	 * @return bool
	 */
	public function _valid_login($password,$email)
	{
		return (bool)$this->customers_model->checkLogin($email,$password);
	}
	// === Custom validation : End === //
	
	/**
	 * Validate and insert (register customer) or update data.
	 * 
	 * @param  array $record
	 * @return void
	 */
	private function _processInsert(array $record=array())
	{
		if( !$this->session->userdata('customer_id') ) 
		{//register
			// === CAPTCHA === //
			$data["cap_img"] = $this->captcha_model->make();
		}
		else 
		{//update
			// === GET ID FROM SESSION === //
			$id = $this->session->userdata('customer_id');
	
			// === GET RECORD === //
			$data = $this->customers_model->getOneById($id);
			
			// === Current Location === //
			$data['current_location_arr'] =
			array(
				$this->_getBaseURL()."customers" => lowercase($this->_getPageTitle('index')),
				$this->_getBaseURI()."/editinfo" => '[page_title]'
			);
		}

        if($this->input->post())
        {
            $post = $this->input->post();

            if( !$this->session->userdata('customer_id') )
            {//register
                if(!isset($post['captcha'])) $post['captcha'] = '';//always set captcha
            }
            else
            {//update
                $post['email'] = $data['email'];//don't change email
                $post['id'] = $id;
            }

            if($post['id'] = $this->customers_model->storeForm($post,'',intval(@$id)))
            {
                //UPDATE
                if( $this->session->userdata('customer_id') )
                {
                    $data = array_merge($data,$post);
                    $data['success_updated'] = true;
                }
                //ADD
                else
                {
                    // === Subscribe === //
                    if($this->input->post('subscribe'))
                    {
                        if(!$this->subscribers_model->EmailExists($this->input->post('email')))
                        {
                            $this->subscribers_model->addEmail($this->input->post('email'));
                        }
                    }

                    // === Mail Customer === //
                    $this->load->model('auto_responders_model');
                    $this->auto_responders_model->send(1,$post['email'],$post);

                    // === Login Customer === //
                    $this->customers_model->setLogined($post);

                    // === REDIRECT === //
                    if( $this->session->userdata('redirect_after_registration') ) redirect( $this->session->userdata('redirect_after_registration') );
                    else redirect($this->_getBaseURI().'/signin');
                }
            }
        }

        parent::_OnOutput($data);
	}

	// +++++++++++++ INNER METHODS +++++++++++++++ //
	
	// +++++++++++++ ACTION METHODS +++++++++++++++ //

    /**
	 * Show customer's home page.
	 * 
	 * @return void
	 */
	public function Index()
	{
		$this->_CheckLogged();

		parent::_OnOutput();
	}

	/**
	 * Check if email not exists (by AJAX). Used in registration.
	 * 
	 * @return string
	 */
	public function Check_Email()
	{
		$this->load->library('form_validation');
		
		$configValidation = array(
               array(
                     'field'   => 'email', 
                     'label'   => parent::_getFieldTitle('email'), 
                     'rules'   => 'trim|required|max_length[255]|valid_email|callback__unique_field[email]'
                  )
            );

		$this->form_validation->set_rules($configValidation);
			
		if ($this->form_validation->run() == FALSE)
		{
			die( "<span class='red'>".validation_errors()."</span>" );
		}
		else 
		{
			die( "<span class='green'>".language('email_available')."</span>" );
		}
	}

	/**
	 * Show registration form.
	 * 
	 * @return void
	 */
	public function Register()
	{
		if($this->_is_logged())
		{
			redirect($this->_getBaseURI());
		}

		$this->_processInsert();
	}

	/**
	 * Show customer's edit information form.
	 * 
	 * @return void
	 */
	public function EditInfo()
	{
		$this->_CheckLogged();

		$this->_processInsert();
	}

	/**
	 * Show login form and login customer on website.
	 * 
	 * @return void
	 */
	public function SignIn()
	{
		if($this->_is_logged())
		{
			redirect($this->_getBaseURI());
		}
		
		$this->load->library('form_validation');
		
		$configValidation = array(
               array(
                     'field'   => 'email', 
                     'label'   => parent::_getFieldTitle('email'), 
                     'rules'   => 'trim|required|valid_email'
                  ),
               array(
                     'field'   => 'password', 
                     'label'   => parent::_getFieldTitle('password'), 
                     'rules'   => 'trim|required|md5|callback__valid_login['.$this->input->post('email').']'
                  ),
            );

		$this->form_validation->set_rules($configValidation);

		if ($this->form_validation->run() == FALSE)
		{
			parent::_OnOutput();
		}
		else
		{
			$customer = $this->customers_model->checkLogin($this->input->post('email'),$this->input->post('password'));
			
			$this->customers_model->setLogined($customer);

			redirect($this->_getBaseURI());
		}

	}

	/**
	 * Show forgot password form and send password on email.
	 * 
	 * @return void
	 */
	public function Forgot_Pass()
	{
		// === Current Location === //
		$data['current_location_arr'] =
		array(
			$this->_getBaseURI()."/signin" => language('authorization'),
			$this->_getBaseURI()."/forgot_pass" => '[page_title]'
		);
		
		$this->load->library('form_validation');
		
		$configValidation = array(
               array(
                     'field'   => 'email', 
                     'label'   => parent::_getFieldTitle('email'), 
                     'rules'   => 'trim|required|valid_email|callback__value_exists[email]'
                  ),
            );

		$this->form_validation->set_rules($configValidation);

		if ($this->form_validation->run() == FALSE)
		{
			parent::_OnOutput($data);
		}
		else
		{
			$customer = $this->customers_model->getOneByUnique('email',$this->input->post('email'));
			
			//mail restore password link to customer
			$this->load->model('auto_responders_model');
			$this->auto_responders_model->send(2,$customer['email'],$customer);

			$data['success_sent'] = TRUE;
			
			parent::_OnOutput($data);
		}
	}
	
	/**
	 * Generate new password for customer.
	 *
	 * @return void
	 */
	public function Restore_Pass()
	{
	    if( ($customer_id = $this->uri->segment($this->_getSegmentsOffset()+3)) && ($hash = $this->uri->segment($this->_getSegmentsOffset()+4))  && ($hash == md5($customer_id.$this->config->item('encryption_key'))) )
	    { 
	      //change password for customer (on random generated)
		  $data['password'] = $this->customers_model->generatePassword($customer_id);
		  
		  parent::_OnOutput($data);
	    }
	    else die('Error: Wrong link for restore password. If you have problem to restore password please use <a href='.site_url($this->_getBaseURL().'contact_us').'>Contact Form</a>.');
	}

	/**
	 * Logout customer.
	 * 
	 * @return void
	 */
	public function SignOut()
	{
		$this->customers_model->setLogout();

		redirect($this->_getBaseURI().'/signin');
	}

}