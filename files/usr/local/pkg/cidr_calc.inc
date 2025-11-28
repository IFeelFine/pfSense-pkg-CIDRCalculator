<?php
/*
 * cidr_calc.inc
 * part of pfSense (https://www.pfsense.org)
 * 888888 888888 888888 | Copyright (c) 2015-2025 I Feel Fine, Inc.
 *   88   88     88     | Licensed under the Apache License, Version 2.0 (the
 *   88   8888   8888   | "License"); you may not use this file except in
 *   88   88     88     | compliance with the License. You may obtain a copy of
 * 888888 88     88     | the License at
 * _____________________|   http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

// Package functions file (required by pfSense package system)
// No specific functions needed for this package as all logic is client-side JavaScript

function cidr_calc_install() {
	// Called during package installation
	// No special installation actions required
	return;
}

function cidr_calc_deinstall() {
	// Called during package removal
	// Clean up any configuration
	global $config;
	if (isset($config['widgets']['cidr_calculator'])) {
		unset($config['widgets']['cidr_calculator']);
		write_config("Removed CIDR Calculator widget configuration");
	}
}
?>
