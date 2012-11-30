<?php

/*
=====================================================
 Social Update
-----------------------------------------------------
 http://www.intoeetive.com/
-----------------------------------------------------
 Copyright (c) 2011-2012 Yuri Salimovskiy
=====================================================
 This software is intended for usage with
 ExpressionEngine CMS, version 2.0 or higher
=====================================================
 File: ft.social_update.php
-----------------------------------------------------
 Purpose: Send updates to social networks upon entry publishing
=====================================================
*/


if ( ! defined('BASEPATH'))
{
    exit('Invalid file request');
}

require_once PATH_THIRD.'social_update/config.php';

class Social_update_ft extends EE_Fieldtype {
	
	var $info = array(
		'name'		=> SOCIAL_UPDATE_ADDON_NAME,
		'version'	=> SOCIAL_UPDATE_ADDON_VERSION
	);
	
	var $module_settings = array();
	
	// --------------------------------------------------------------------
	
	
	function _get_module_settings()
	{
		$query = $this->EE->db->select('settings')
						->from('social_update_settings')
	                    ->where('site_id', $this->EE->config->item('site_id'))
	                    ->limit(1)
						->get();
		if ($query->num_rows()>0)
		{
			$this->module_settings = unserialize($query->row('settings'));
		}
	}
	
	
	function display_settings($data)
    {
        $this->_get_module_settings();
		
		$providers = array();
		foreach ($config['service_providers'] as $provider)
        {
            if (isset($this->module_settings["token"]["$provider"]) && $this->module_settings["token"]["$provider"]!='')
            {
            	$providers["$provider"] = lang("$provider");
            }
        }
    
        $data["provider"] = (isset($data["provider"])) ? $data["provider"] : ''; 
        
        $this->EE->table->add_row(
            lang("provider", "provider"), form_dropdown("provider", $providers, $data["provider"])
            );

        $url_options = array('url_title'=>lang('url_auto_url_title'), 'entry_id'=>lang('url_auto_entry_id'));
        $this->EE->db->select('module_id'); 
        $query = $this->EE->db->get_where('modules', array('module_name' => 'Pages')); 
        if ($query->num_rows()>0) $url_options['pages'] = lang('url_auto_pages');
        $this->EE->db->select('module_id'); 
        $query = $this->EE->db->get_where('modules', array('module_name' => 'Structure')); 
        if ($query->num_rows()>0) $url_options['structure'] = lang('url_auto_structure');
        $url_options['channel_url'] = lang('channel_url');
        $url_options['site_url'] = lang('site_url');
        $url_options['manual'] = lang('url_manual');
        
		$data["url_type"] = (isset($data["url_type"])) ? $data["url_type"] : ''; 
        
        $this->EE->table->add_row(
            lang("url_type", "url_type"), form_dropdown("url_type", $url_options, $data["url_type"])
            );    
            
    } 
	
	
	
	
	/**
	 * Display Field on Publish
	 *
	 * @access	public
	 * @param	existing data
	 * @return	field html
	 *
	 */
	function display_field($data)
	{
		$text = '';
		$url = '';
		if (!empty($data))
		{
			$q = $this->EE->db->select('post, url')
					->from('social_update_posts')
					->where('post_id', $data)
					->get();
			if ($q->num_rows()>0)
			{
				$text = $q->row('post');
				$url = $q->row('url');
			}
		}
		
		$name = (isset($this->cell_name)) ? $this->cell_name : $this->field_name;

		$input = 	form_textarea($name, $text);
		
		if ($url!='' || $this->settings['url_type']=='manual')
		{
        	$input .=         
					BR.
					form_label(lang('url').NBS, $name.'_social_update_url').
					form_input($name.'_social_update_url', $url, 'id="'.$name.'_social_update_url"')
					;
		}
		
		return $input;
        
	}
	
	// --------------------------------------------------------------------
		
	/**
	 * Replace tag
	 *
	 * @access	public
	 * @param	field contents
	 * @return	replacement text
	 *
	 */
	function replace_tag($data, $params = array(), $tagdata = FALSE)
	{
        //todo display associated social post
        return;
	}

    
    function save($data)
	{
		$this->EE->session->cache['social_update'][$this->field_id] = $data;
		var_dump($data);
		exit();
		return '';
	}
    
    function save_settings($data) {
        return array();    
    }
    
    
   	// ------------------------
	// P&T MATRIX SUPPORT
	// ------------------------
	
	/**
	 * Display Matrix field
	 */
	function display_cell($data) {
		return $this->display_field($data);
    }
	
    function display_cell_settings($data)
	{
	   return array();  
    }
    
    function save_cell_settings($data) {
		return $this->save_settings($data);
	}
    
	function save_cell($data)
	{
		return $this->save($data);
	}
    
	// --------------------------------------------------------------------
	
	/**
	 * Install Fieldtype
	 *
	 * @access	public
	 * @return	default global settings
	 *
	 */
	function install()
	{
		return array();
	}
	

}
