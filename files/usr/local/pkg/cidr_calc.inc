<?php
/*
 * cidr_calc.inc
 *
 * part of pfSense (https://www.pfsense.org)
 * Copyright (c) 2025 I Feel Fine, Inc.
 * Copyright (c) 2015-2025 Rubicon Communications, LLC (Netgate)
 * All rights reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/*
 * CIDR Calculator Package Functions
 * 
 * Provides lifecycle hooks for the pfSense package system.
 * All calculation logic is client-side JavaScript; this file
 * handles installation/deinstallation configuration management.
 */

require_once("config.inc");
require_once("functions.inc");
require_once("util.inc");

/**
 * Initialize package during installation
 * 
 * Sets up default widget configuration and logs installation.
 * 
 * @return void
 */
function cidr_calc_install()
{
	global $config;

	// Initialize widget configuration with defaults
	if (!isset($config['widgets']['cidr_calculator'])) {
		init_config_arr(array('widgets', 'cidr_calculator'));
		$config['widgets']['cidr_calculator']['show_ipv4'] = 'true';
		$config['widgets']['cidr_calculator']['show_ipv6'] = 'true';
		write_config(gettext("Initialized CIDR Calculator widget settings"));
	}

	log_error(gettext("[CIDRCalculator] Package installed successfully"));
}

/**
 * Clean up during package removal
 * 
 * Removes widget configuration and logs deinstallation.
 * 
 * @return void
 */
function cidr_calc_deinstall()
{
	global $config;

	// Clean up widget configuration
	if (isset($config['widgets']['cidr_calculator'])) {
		unset($config['widgets']['cidr_calculator']);
		write_config(gettext("Removed CIDR Calculator widget configuration"));
		log_error(gettext("[CIDRCalculator] Widget configuration removed"));
	}

	log_error(gettext("[CIDRCalculator] Package uninstalled successfully"));
}

/**
 * Handle package upgrade
 * 
 * Ensures configuration is current after package upgrade.
 * 
 * @return void
 */
function cidr_calc_upgrade()
{
	// Re-initialize configuration if needed
	cidr_calc_install();
	log_error(gettext("[CIDRCalculator] Package upgraded successfully"));
}

/**
 * Validate package configuration input
 * 
 * Currently unused as package has no configuration page,
 * but included for future extensibility.
 * 
 * @param array $post POST data to validate
 * @param array $input_errors Reference to error array
 * @return void
 */
function cidr_calc_validate_input($post, &$input_errors)
{
	// No configuration page currently
	// Future: validate widget settings if configuration page added
}

?>