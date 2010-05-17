<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Register Plugins Hook
 * Thanks to Zombor @ Argentum
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author	   Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module	   Register Plugins Hook
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */

class register_plugins {

	/**
	 * Adds the register method to load after the find_uri Router method.
	 */
	public function __construct()
	{
		// Hook into routing
		if (file_exists(DOCROOT."application/config/database.php"))
			Event::add_after('system.routing', array('Router', 'find_uri'), array($this, 'register'));
	}

	/**
	 * Loads all ushahidi plugins
	 */
	public function register()
	{
		$db = Database::instance();
		$plugins = array();
		// Get the list of plugins from the db
		foreach ($db->getwhere('plugin', array('active' => TRUE, 'installed' => TRUE)) as $plugin)
		{
			$plugins[] = MODPATH.'plugins/'.$plugin->name;
		}

		// Now set the plugins
		Kohana::config_set('core.modules', array_merge(Kohana::config('core.modules'), $plugins));

		// We need to manually include the hook file for each plugin,
		// because the additional plugins aren't loaded until after the application hooks are loaded.
		foreach ($plugins as $plugin)
		{
			$d = dir($plugin.'/hooks'); // Load all the hooks
			while (($entry = $d->read()) !== FALSE)
				if ($entry[0] != '.')
					include $plugin.'/hooks/'.$entry;
		}
	}
}

new register_plugins;