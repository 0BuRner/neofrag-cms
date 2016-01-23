<?php if (!defined('NEOFRAG_CMS')) exit;
/**************************************************************************
Copyright © 2015 Michaël BILCOT & Jérémy VALENTIN

This file is part of NeoFrag.

NeoFrag is free software: you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

NeoFrag is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public License
along with NeoFrag. If not, see <http://www.gnu.org/licenses/>.
**************************************************************************/

class m_addons_c_admin_ajax extends Controller_Module
{
	public function index()
	{
		if (method_exists($this, $addon = '_'.post('addon').'_list'))
		{
			return new Col(new Panel(array_merge(array(
				'body' => FALSE,
				'size' => 'col-md-8 col-lg-9'
			), $this->$addon())));
		}
		
		throw new Exception(NeoFrag::UNFOUND);
	}

	public function active($type, $name)
	{
		$this->extension('json');

		$is_enabled = $this->db	->select('is_enabled')
								->from('nf_settings_addons')
								->where('name', $name)
								->where('type', $type)
								->row();

		$this->db	->where('name', $name)
					->where('type', $type)
					->update('nf_settings_addons', array(
						'is_enabled' => !$is_enabled
					));

		return json_encode(array(
			'success' => TRUE
		));
	}
	
	public function install()
	{
		$this->extension('json');
		
		if (!empty($_FILES['file']) && extension($_FILES['file']['name']) == 'zip')
		{
			if ($zip = zip_open($_FILES['file']['tmp_name']))
			{
				while (file_exists($tmp = sys_get_temp_dir().'/'.unique_id()));
				
				mkdir($tmp, 0777, TRUE);

				while ($zip_entry = zip_read($zip))
				{
					$entry_name = zip_entry_name($zip_entry);

					if (substr($entry_name, -1) == '/')
					{
						mkdir($tmp.'/'.$entry_name, 0777, TRUE);
					}
					else if (zip_entry_open($zip, $zip_entry, 'r'))
					{
						file_put_contents($tmp.'/'.$entry_name, zip_entry_read($zip_entry, zip_entry_filesize($zip_entry)));
						
						zip_entry_close($zip_entry);
					}
				}

				zip_close($zip);
				
				$folders = array_filter(scandir($tmp), function($a) use ($tmp){
					return !in_array($a, array('.', '..')) && is_dir($tmp.'/'.$a);
				});
				
				function install_addon($dir, $types = NULL)
				{
					if (!$types)
					{
						$types = array('Module', 'Widget', 'Theme');
					}
					else if (!is_array($types))
					{
						$types = (array)$types;
					}
					
					foreach (scandir($dir) as $filename)
					{
						if (!is_dir($file = $dir.'/'.$filename) &&
							preg_match('/^(.+?)\.php$/', $filename, $match) &&
							preg_match('/class ('.implode('|', array_map(function($a){ return strtolower(substr($a, 0, 1)); }, $types)).')_('.$match[1].') extends ('.implode('|', $types).')/', $content = php_strip_whitespace($file), $match) &&
							$match[1] == strtolower(substr($match[3], 0, 1)))
						{
							function get_version($version)
							{
								return preg_replace('/[^\d.]/', '', $version);
							}
							
							function parse_version($content, $value)
							{
								if (preg_match('/\$'.$value.'[ \t]*?=[ \t]*?([\'"])(.+?)\1;/', $content, $match))
								{
									return get_version($match[2]);
								}
							}
							
							$addons     = NeoFrag::loader()->addons->{'get_'.($folder = ($type = strtolower($match[3])).'s')}(TRUE);
							$version    = parse_version($content, 'version');
							$nf_version = parse_version($content, 'nf_version');
							
							if (!empty($version) && !empty($nf_version))
							{
								if (isset($addons[$name = strtolower($match[2])]) && ($cmp = version_compare($version, get_version($addons[$name]->version))) !== 1)
								{
									return $cmp === 0 ? 'already_installed_version' : 'not_newer_installed_version';
								}
								
								if (($cmp = version_compare($nf_version, get_version(NEOFRAG_VERSION))) !== -1)
								{
									copy_all($dir, $folder.'/'.$name);

									if ($addon = NeoFrag::loader()->$type($name))
									{
										$addon->reset();
										return TRUE;
									}
								}
							}
							
							return;
						}
					}
				}

				$types   = array('modules', 'widgets', 'themes');
				$results = array();

				if (count($folders) == 1 && !in_array($folder = current($folders), $types))
				{
					$results[] = install_addon($tmp.'/'.$folder);
				}
				else
				{
					foreach (array_intersect($folders, $types) as $folder)
					{
						foreach (scandir($tmp.'/'.$folder) as $dir)
						{
							if (!in_array($dir, array('.', '..')) && is_dir($dir = $tmp.'/'.$folder.'/'.$dir))
							{
								$results[] = install_addon($dir, substr(ucfirst($folder), 0, -1));
							}
						}
					}
				}

				rmdir_all($tmp);
				
				return json_encode(array(
					'success' => TRUE
				));
			}
		}
		
		return json_encode(array(
			'error' => $this('zip_file_required')
		));
	}
	
	private function _modules_list()
	{
		return array(
			'title'   => 'Liste des modules',
			'icon'    => 'fa-edit',
			'content' => $this->load->view('modules')
		);
	}
	
	private function _themes_list()
	{
		return array(
			'title'   => 'Liste des thèmes',
			'icon'    => 'fa-tint',
			'body'    => TRUE,
			'content' => $this->load->view('themes')
		);
	}
	
	private function _widgets_list()
	{
		return array(
			'title' => 'Liste des widgets',
			'icon'  => 'fa-cubes',
			'content' => $this->load->view('widgets')
		);
	}
	
	private function _languages_list()
	{
		return array(
			'title'   => 'Liste des langues',
			'icon'    => 'fa-book',
			'content' => $this->load->view('languages', array(
				'languages' => $this->addons->get_languages()
			))
		);
	}
	
	/*private function _smileys_list()
	{
		return array(
			'title' => 'Liste des smileys',
			'icon'  => 'fa-smile-o'
		);
	}
	
	private function _bbcodes_list()
	{
		return array(
			'title' => 'Liste des BBcodes',
			'icon'  => 'fa-code'
		);
	}*/

	public function _theme_activation($name)
	{
		$this->config('nf_default_theme', $name);
		
		return $name;
	}

	public function _theme_reset($name)
	{
		if ($theme = $this->load->theme($name))
		{
			$theme->reset();
		}
	}

	public function _theme_settings($controller)
	{
		return $controller->index();
	}
	
	public function _language_sort($language, $position)
	{
		$languages = array();
		
		foreach ($this->db->select('code')->from('nf_settings_languages')->where('code !=', $language)->order_by('order')->get() as $code)
		{
			$languages[] = $code;
		}
		
		foreach (array_merge(array_slice($languages, 0, $position, TRUE), array($language), array_slice($languages, $position, NULL, TRUE)) as $order => $code)
		{
			$this->db	->where('code', $code)
						->update('nf_settings_languages', array(
							'order' => $order
						));
		}
	}
}

/*
NeoFrag Alpha 0.1.4
./neofrag/modules/addons/controllers/admin_ajax.php
*/