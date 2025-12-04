<?php
/*
 * pfSense CIDR Calculator Privilege Mapping
 * Grants access to users with dashboard access.
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

global $priv_list;

$priv_list['page-diagnostics-cidr_calculator'] = array(
    'name'    => 'WebCfg - Diagnostics: CIDR Calculator',
    'descr'   => 'Allow access to the CIDR subnet calculator',
    'match'   => array('diag_cidr_calculator.php*'),
);
// Users with "WebCfg - Diagnostics: all pages" will inherit this privilege automatically
?>