<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Freeform_zip2location_ext {

    var $name       	= 'Freeform Zip 2 Location';
    var $version        = '1.0';
    var $description    = 'Looks up city/state information based on zip code and returns data to city/state fields';
    var $settings_exist = 'n';
    var $docs_url       = ''; // 'http://ellislab.com/expressionengine/user-guide/';

    var $settings        = array();

    /**
     * Constructor
     *
     * @param   mixed   Settings array or empty string if none exist.
     */
	function __construct($settings='')
	{
		$this->settings = $settings;
	}

	// --------------------------------
	//  Activate Extension
	// --------------------------------

	/**
	 * Activate Extension
	 *
	 * This function enters the extension into the exp_extensions table
	 *
	 * @see http://ellislab.com/codeigniter/user-guide/database/index.html for
	 * more information on the db class.
	 *
	 * @return void
	 */
	function activate_extension()
	{
	    $this->settings = array(
	        'max_link_length'   => 18,
	        'truncate_cp_links' => 'no',
	        'use_in_forum'      => 'no'
	    );

	    $data = array(
	        'class'     => __CLASS__,
	        'method'    => 'zip_lookup',
	        'hook'      => 'freeform_module_insert_begin',
	        'settings'  => serialize($this->settings),
	        'priority'  => 10,
	        'version'   => $this->version,
	        'enabled'   => 'y'
	    );

	    ee()->db->insert('extensions', $data);
	}

	/**
	 * Update Extension
	 *
	 * This function performs any necessary db updates when the extension
	 * page is visited
	 *
	 * @return  mixed   void on update / false if none
	 */
	function update_extension($current = '')
	{
	    if ($current == '' OR $current == $this->version)
	    {
	        return FALSE;
	    }

	    if ($current < '1.0')
	    {
	        // Update to version 1.0
	    }

	    ee()->db->where('class', __CLASS__);
	    ee()->db->update(
	                'extensions',
	                array('version' => $this->version)
	    );
	}

	/**
	 * Disable Extension
	 *
	 * This method removes information from the exp_extensions table
	 *
	 * @return void
	 */
	function disable_extension()
	{
	    ee()->db->where('class', __CLASS__);
	    ee()->db->delete('extensions');
	}

	/**
	 * Zip Lookup
	 *
	 * Looks up city and state info based on zip code
	 *
	 * @return array
	 */

	public function zip_lookup($inputs, $entry_id, $form_id, $obj)
	{
	    //have other extensions already manipulated?
	    if (ee()->extensions->last_call !== FALSE)
	    {
	        $inputs = ee()->extensions->last_call;
	    }

	    //determine if a zip code field exists
	    $zip_fields = array('zip_code', 'zip', 'postal_code');
	    $zip_val = '';

	    foreach ($zip_fields as $i => $value) {
	    	if (isset($inputs[$zip_fields[$i]])) {
	    		$zip_val = $inputs[$zip_fields[$i]];
	    	}
	    }

	    if ($zip_val) {
	    	$results = $this->zip_loc($zip_val);

	    	//custom input data
	    	$inputs['city']    	= $results['city'];
	    	$inputs['state'] 	= $results['state'];
	    }

	    //must return input array
	    return $inputs;
	}

	private function zip_loc($z) {
		$resp = '';
		$zt = fopen('http://zip.elevenbasetwo.com?zip='.$z,'r');

		if ( $zt ) {
			while ( ! feof($zt) ) {
				$resp .= fread($zt,1024);
			}

			$resp = json_decode($resp);
			return array(
				'city' => ucfirst(strtolower($resp->city)),
                'state' => $resp->state,
                'zip' => $z
            );
        } else {
            return array(
                'city' => '',
                'state' => '',
                'zip' => ''
            );
        }
    }

}
// END CLASS

/* End of file ext.freeform_zip2location.php */
/* Location: ./system/expressionengine/third_party/freeform_zip2location/ext.freeform_zip2location.php */