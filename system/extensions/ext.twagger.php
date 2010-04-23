<?php if (!defined('EXT')) exit('Invalid file request');

/**
*
* @package ExpressionEngine
* @author Wouter Vervloet
* @copyright	Copyright (c) 2010, Baseworks
* @license		http://creativecommons.org/licenses/by-sa/3.0/
* @link		http://www.baseworks.nl/ee/docs/twagger/
* @filesource
* 
* This work is licensed under the Creative Commons Attribution-Share Alike 3.0 Unported.
* To view a copy of this license, visit http://creativecommons.org/licenses/by-sa/3.0/
* or send a letter to Creative Commons, 171 Second Street, Suite 300,
* San Francisco, California, 94105, USA.
* 
*/
class Twagger
{
  public $settings            = array();
  
  public $name                = 'Twagger';
  public $version             = '0.6';
  public $description         = 'Inline tagging for weblog entries made easy.';
  public $settings_exist      = 'y';
  public $docs_url            = '';

  public $tags = array();

  /** 
  * ...
  */  
  function display_tags_for_entry($tagdata, $row, $weblog_obj) {
    global $DB, $TMPL;
    
    foreach ($TMPL->var_pair as $key => $val) {
      
      /** ----------------------------------------
      /**  parse tags
      /** ----------------------------------------*/      
      if (preg_match("/tags/", $key)) {
        $entry_tags = $DB->query("SELECT * FROM exp_twagger_tags WHERE entry_id = ".$row['entry_id']);

        // If there are no tags associated with this entry, return
        if($entry_tags->num_rows == 0) return $tagdata;
        
        $output = $tagdata;
        $chunk = '';
      
        $chunk_tmpl = $TMPL->fetch_data_between_var_pairs($tagdata, 'tags');
        
        foreach ($entry_tags->result as $tag) {
          $chunk .= $TMPL->swap_var_single('tag', $tag['text'], $chunk_tmpl);
        }

        $tagdata = preg_replace("/".LD."tags"."(.*?)".RD."(.*?)".LD.SLASH."tags".RD."/s", $chunk, $output);
      }
    }
    
    return $tagdata;
  }

	/**
	* ...
	*/
  function submit_new_entry_absolute_end($entry_id, $data) {
    global $DB;
    
    $this->entry_id = $entry_id;
    $this->data = $data;
    
    $parse_fields = array();
    $enabled_weblogs = array();
    
    $field_query = $DB->query("SELECT field_id, weblog_id FROM exp_twagger_settings WHERE weblog_id = ".$this->data['weblog_id'])->result;
    foreach($field_query as $row) {
      $parse_fields[] = $row['field_id'];
      $enabled_weblogs[] = $row['weblog_id'];
    }
    
    $enabled_weblogs = array_unique($enabled_weblogs);
    
    $weblog_fields = $DB->query('SELECT * FROM exp_weblog_fields f LEFT JOIN exp_weblogs w ON w.field_group = f.group_id WHERE w.weblog_id = '.$data['weblog_id']);
    foreach ($weblog_fields->result as $weblog_field) {
      
      if( ! in_array($weblog_field['field_id'], $parse_fields)) continue;
      
      $field_data = $DB->query('SELECT * FROM exp_weblog_data WHERE entry_id = '.$entry_id)->row['field_id_'.$weblog_field['field_id']];
      
      if($field_data === '') continue;
      
      $this->_parse_tags($field_data);

    }

  }

  /** 
  * ...
  */  
  function delete_entries_loop($entry_id, $weblog_id) {
    
    global $DB;
    $DB->query("DELETE FROM exp_twagger_tags WHERE entry_id = $entry_id");
    
  }
  
  /** 
  * ...
  */
  function _parse_tags($str='') {
    global $DB;


    if(!preg_match_all('/(^|\s)#([a-z0-9-_]+)?/i', $str, $matches)) return $str;

    foreach($matches[2] as $match) {
      $this->tags[] = $match;
      $str = preg_replace('/(^|\s)#('.$match.')/', ' <a href="#${2}">#${2}</a>', $str, 1);
    }

    
    
    $str = preg_replace('/(\\\\#)/', '#', $str);
  
    $this->_save_tags();
  }
	
  /** 
  * ...
  */	
  protected function _save_tags()
  {
    global $DB;

    $sql[] = "DELETE FROM exp_twagger_tags WHERE entry_id = '".$this->entry_id."'";
    
    foreach($this->tags as $tag) {
      $sql[] = $DB->insert_string('exp_twagger_tags', array('text' => $tag, 'entry_id' => $this->entry_id));      
    }
    
    foreach($sql as $query) {
      $DB->query($query);
    }
  }
	
  /**
  * System Functions
  */

  /**
  * Constructor - Extensions use this for settings
  * @param array $settings settings for this extension
  */
  function Twagger($settings='')
  { 
    $this->__construct($settings);
  }
  
  function __construct($settings='')
  {
    $this->settings = $settings;
  }
  // END

	/**
	 * Takes the control panel and adds the Better Meta scripts and styles
	 *
	 * @param	string $out The control panel html
	 * @return	string The modified control panel html
	 * @since 	Version 1.4.0
	 */
  function show_full_control_panel_end( $out )
  {
		global $DB, $EXT, $IN, $PREFS, $REGX, $SESS;

		// -- Check if we're not the only one using this hook
		if($EXT->last_call !== FALSE)
			$out = $EXT->last_call;

		if($IN->GBL('C', 'GET') == 'publish' || $IN->GBL('C', 'GET') == 'edit' || $IN->GBL('P', 'GET') == 'extension_settings') {

			$css = $js = "";

			if(isset($SESS->cache['Morphine']['cp_styles_included']) === FALSE)
			{
				$css .= "\n<link rel='stylesheet' type='text/css' media='screen' href='" . $PREFS->ini('theme_folder_url', 1) . "cp_themes/".$PREFS->ini('cp_theme')."/Morphine/css/MOR_screen.css' />";
				$SESS->cache['Morphine']['cp_styles_included'] = TRUE;
			}

      if(isset($SESS->cache['Twagger']['cp_styles_included']) === FALSE)
			{
  			$css .= "\n<link rel='stylesheet' type='text/css' media='screen' href='" . $PREFS->ini('theme_folder_url', 1) . "cp_themes/".$PREFS->ini('cp_theme')."/twagger/css/admin.css' />";
  			$js .= "\n<script type='text/javascript' src='" . $PREFS->ini('theme_folder_url', 1) . "cp_themes/".$PREFS->ini('cp_theme')."/twagger/js/admin.js'></script>";
				$SESS->cache['Twagger']['cp_styles_included'] = TRUE;
			}

			$out = str_replace("</head>", $css . "</head>", $out);
			$out = str_replace("</body>", $js . "</body>", $out);
			// make sure we don't add it again
		}
	
		return $out;
	}

  /**
  * Extension settings
  */
  function settings_form($current)
  {
    global $DB, $DSP, $LANG, $IN, $PREFS;
    
		$LANG->fetch_language_file('twagger_ext');
    
    $DSP->crumbline = TRUE;
    
    $DSP->title  = $LANG->line('twagger_extension_name');
    $DSP->crumb  = $DSP->anchor(BASE.AMP.'C=admin'.AMP.'area=utilities', $LANG->line('utilities')).
      $DSP->crumb_item($DSP->anchor(BASE.AMP.'C=admin'.AMP.'M=utilities'.AMP.'P=extensions_manager', $LANG->line('extensions_manager')));
    $DSP->crumb .= $DSP->crumb_item($LANG->line('twagger_extension_name'));
    
    $DSP->right_crumb($LANG->line('disable_extension'), BASE.AMP.'C=admin'.AMP.'M=utilities'.AMP.'P=toggle_extension_confirm'.AMP.'which=disable'.AMP.'name='.$IN->GBL('name'));
    
    // Settings form
    
		$DSP->body = '';
		$DSP->body .= "<div class='mor settings-form'>";

		$DSP->body .= $DSP->heading($LANG->line('twagger_extension_name') . " <small>{$this->version}</small>");
		
		$DSP->body .= $DSP->form_open(
								array(
									'action' => 'C=admin'.AMP.'M=utilities'.AMP.'P=save_extension_settings',
									'id'     => 'Lg_better_meta_settings'
								),
								// WHAT A M*THERF!@KING B!TCH THIS WAS
								// REMBER THE NAME ATTRIBUTE MUST ALWAYS MATCH THE FILENAME AND ITS CASE SENSITIVE
								// BUG??
								array('name' => strtolower(get_class($this)))
		);

//		$settings = $this->_get_settings();
//		$default_settings =  $this->_build_default_settings();
//		$settings = $this->array_merge_recursive_flat($default_settings, $settings);

		$addon_name = $this->name;
		$member_group_query = $DB->query("SELECT group_id, group_title FROM exp_member_groups WHERE site_id = ".$PREFS->ini('site_id')." ORDER BY group_title");
		$weblog_query = $DB->query("SELECT blog_name, blog_title, weblog_id FROM `exp_weblogs` WHERE `site_id` = ".$PREFS->ini('site_id')." ORDER BY `blog_name`");
		$field_query = $DB->query("SELECT * FROM  exp_weblog_fields ORDER BY field_order");
		$DSP->body .= "\n<link rel='stylesheet' type='text/css' media='screen' href='" . $PREFS->ini('theme_folder_url', 1) . "cp_themes/".$PREFS->ini('cp_theme')."/twagger/css/admin.css' />";

    $enabled_weblogs = array();
    
    $enabled_weblogs_query = $DB->query("SELECT DISTINCT weblog_id FROM exp_twagger_settings GROUP BY weblog_id")->result;
    foreach($enabled_weblogs_query as $row) {
      $enabled_weblogs[] = $row['weblog_id'];
    }
    
		ob_start();
		
		include PATH_CP_THEME.$PREFS->ini('cp_theme')."/twagger/views/extension_settings.php";
		
		$DSP->body .= ob_get_clean();
		
		$DSP->body .= $DSP->form_c();
		$DSP->body .= "</div>";
	}

  /**
  * Extension settings
  */
  function save_settings()
  {
    global $DB, $DSP, $LANG, $IN, $PREFS;
    
    $fields = $IN->GBL("fields");
    $weblogs = $IN->GBL('weblogs');

    $DB->query("DELETE FROM exp_twagger_settings");      
    
    if( ! $fields || ! $weblogs ) return;
  
    foreach($fields as $weblog => $fieldset) {

      if( ! in_array($weblog, $weblogs) ) continue;

      foreach ($fieldset as $fi => $field) {

        if( ! $field ) continue;
        $data = array('weblog_id' => $weblog, 'field_id' => $field);
        $DB->query($DB->insert_string('exp_twagger_settings', $data));
      }
      
    }    
    
  }


  /**
  * Activate Extension
  * @return bool Has the extension been activated successfully?
  */
  function activate_extension() {
    global $DB;

		// hooks array
		$hooks = array(
			'control_panel_home_page_right_option' => 'harvest_tags',
      'submit_new_entry_absolute_end' => 'submit_new_entry_absolute_end',
			'show_full_control_panel_end' => 'show_full_control_panel_end',
			'delete_entries_loop' => 'delete_entries_loop',
			'weblog_entries_tagdata' => 'display_tags_for_entry'
//			'weblog_entries_row'  => 'modify_weblog_variables'
		);

		foreach ($hooks as $hook => $method) {
			$sql[] = $DB->insert_string( 'exp_extensions',
				array(
					'extension_id'	=> '',
					'class'			=> get_class($this),
					'method'		=> $method,
					'hook'			=> $hook,
					'settings'		=> '',
					'priority'		=> 9,
					'version'		=> $this->version,
					'enabled'		=> 'y'
				)
			);
		}

    // add extension table
    $sql[] = 'DROP TABLE IF EXISTS `exp_twagger_settings`';		
		$sql[] = "CREATE TABLE `exp_twagger_settings` (`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, `weblog_id` INT NOT NULL, `field_id` INT NOT NULL)";

    $sql[] = 'DROP TABLE IF EXISTS `exp_twagger_tags`';		
		$sql[] = "CREATE TABLE `exp_twagger_tags` (`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, `text` VARCHAR(250) NOT NULL, `entry_id` INT NOT NULL)";


		// run all sql queries
		foreach ($sql as $query) {
			$DB->query($query);
		}

		return TRUE;
  }
  // END


  /**
  * Update Extension
  * @param string $current Current version
  */
  function update_extension($current='') {
    global $DB;

    if ($current == '' OR $current == $this->version) return FALSE;
    if ($current < '0.1') { }// Update to next version 0.5
    $DB->query("UPDATE exp_extensions SET version = '".$DB->escape_str($this->version)."' WHERE class = '".get_class($this)."'");
  }
  // END

  /**
  * Disable Extension
  */
  function disable_extension() {
    global $DB;
    $sql[] = 'DROP TABLE IF EXISTS `exp_'.strtolower(get_class($this)).'_settings`';		
    $sql[] = 'DROP TABLE IF EXISTS `exp_'.strtolower(get_class($this)).'_tags`';		
    $sql[] = "DELETE FROM exp_extensions WHERE class = '".get_class($this)."'";		

    foreach($sql as $query) {
      $DB->query($query);
    }

  }
  // END

}
// END CLASS