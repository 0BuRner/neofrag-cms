<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace NF\Widgets\Header\Controllers;

use NF\NeoFrag\Loadables\Controller;

class Checker extends Controller
{
	public function index($settings = [])
	{
		return [
			'display'           => in_array($settings['display'], ['logo', 'title']) ? $settings['display'] : 'title',
			'align'             => in_array($settings['align'], ['text-left', 'text-right']) ? $settings['align'] : 'text-center',
			'title'             => utf8_htmlentities($settings['title']),
			'description'       => utf8_htmlentities($settings['description']),
			'color-title'       => preg_match($regex = '/^#([a-f0-9]{3}){1,2}$/i', $settings['color-title'])       ? $settings['color-title']       : '',
			'color-description' => preg_match($regex,                              $settings['color-description']) ? $settings['color-description'] : ''
		];
	}
}
