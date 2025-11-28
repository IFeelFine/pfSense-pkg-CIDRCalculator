<?php
/*
 * pfSense CIDR Calculator Privilege Mapping
 * Grants access to users with dashboard access.
 * Copyright (c) 2015-2025 I Feel Fine, Inc.
 * Licensed under the Apache License, Version 2.0
 */

global $priv_list;

$priv_list['page-diagnostics-cidr_calculator'] = array(
    'name'    => 'WebCfg - Diagnostics: CIDR Calculator',
    'descr'   => 'Allow access to the CIDR subnet calculator',
    'match'   => array('diagnostics_cidr_calculator.php*'),
);

$priv_list['page-diagnostics-cidr_calculator']['parent'] = 'page-dashboard-all';
?>