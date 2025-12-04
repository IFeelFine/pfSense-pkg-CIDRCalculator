<?php
/*--------------------------------------------------------------------------*
 *                                                                          *
 *       888888    888888              88     888888                        *
 *         88      88                  88     88   oo                       *
 *         88      88                  88     88                            *
 *         88      8888 .d8b.   .d8b.  88     8888 88 8888b.  .d8b.         *
 *         88      88  d8P Y8b d8P Y8b 88     88   88 88  8b d8P Y8b        *
 *         88      88  8888888 8888888 88     88   88 88  88 8888888        *
 *         88      88  Y8b.    Y8b.    88     88   88 88  88 Y8b.           *
 *       888888    88   ºY888P  ºY888P 88     88   88 88  88  ºY888P        *
 *                                                                          *
 *                                               (c) 2025 I Feel Fine, Inc. *
 *--------------------------------------------------------------------------*
 * Part of pfSense (https://www.pfsense.org)                                *
 * Copyright (c) 2015-2025 Rubicon Communications, LLC (Netgate)            *
 * All rights reserved.                                                     *
 *                                                                          *
 * Licensed under the Apache License, Version 2.0 (the "License");          *
 * you may not use this file except in compliance with the License.         *
 * You may obtain a copy of the License at                                  *
 *                                                                          *
 *     http://www.apache.org/licenses/LICENSE-2.0                           *
 *                                                                          *
 * Unless required by applicable law or agreed to in writing, software      *
 * distributed under the License is distributed on an "AS IS" BASIS,        *
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. *
 * See the License for the specific language governing permissions and      *
 * limitations under the License.                                           *
 *--------------------------------------------------------------------------*
 * Objective: IPv4 and IPv6 CIDR calculator for pfSense diagnostics menu.   */

require_once("guiconfig.inc");

// Verify user has required privilege
if (!isAllowedPage("diag_cidr_calculator.php")) {
	// Log unauthorized access attempt
	log_auth(sprintf(gettext("Unauthorized access attempt to CIDR Calculator by user '%s' from %s"),
		$_SESSION['Username'] ?? 'unknown',
		$_SERVER['REMOTE_ADDR']));
	
	// Display error and exit
	include("head.inc");
	echo '<div class="alert alert-danger">';
	echo gettext("You do not have permission to access this page. ");
	echo gettext("The CIDR Calculator requires dashboard access privileges.");
	echo '</div>';
	include("foot.inc");
	exit;
}

$pgtitle = array(gettext("Diagnostics"), gettext("CIDR Calculator"));
$active_tab = $_GET['tab'] ?? 'ipv4';

// Validate tab parameter
if (!in_array($active_tab, array('ipv4', 'ipv6'))) {
	$active_tab = 'ipv4';
}

$tab_array = array();
$tab_array[] = array(gettext("IPv4"), $active_tab == "ipv4", "/diag_cidr_calculator.php?tab=ipv4");
$tab_array[] = array(gettext("IPv6"), $active_tab == "ipv6", "/diag_cidr_calculator.php?tab=ipv6");

include("head.inc");
?>

<!-- JavaScript Required Notice -->
<noscript>
	<div class="alert alert-danger" role="alert">
		<h4><?=gettext("JavaScript Required")?></h4>
		<p><?=gettext("The CIDR Calculator requires JavaScript to function. Please enable JavaScript in your browser and refresh this page.")?></p>
	</div>
</noscript>

<div id="calculator-content" style="display: none;">

<?php
display_top_tabs($tab_array, true);
?>

<div class="panel panel-default">
	<div class="panel-heading"><h2 class="panel-title"><?=gettext("CIDR Calculator")?></h2></div>
	<div class="panel-body">

<?php if ($active_tab == "ipv4"): ?>

<!-- IPv4 Calculator -->
<div class="table-responsive">
	<table class="table table-striped table-hover">
		<tbody>
			<tr>
				<td style="width: 30%;"><label for="ip"><?=gettext("IP Address")?>:</label></td>
				<td><input type="text" id="ip" class="form-control" placeholder="192.168.1.0" value="192.168.1.0"></td>
			</tr>
			<tr>
				<td><label for="cidr"><?=gettext("CIDR Mask Bits")?>:</label></td>
				<td>
					<select id="cidr" class="form-control">
						<?php for ($i = 1; $i <= 32; $i++): ?>
						<option value="<?=htmlspecialchars($i)?>"<?=($i == 24 ? " selected" : "")?>><?=htmlspecialchars($i)?></option>
						<?php endfor; ?>
					</select>
				</td>
			</tr>
			<tr>
				<td><label for="subnet"><?=gettext("Subnet Mask")?>:</label></td>
				<td>
					<select id="subnet" class="form-control">
						<?php
						$subnetMasks = array(
							'128.0.0.0', '192.0.0.0', '224.0.0.0', '240.0.0.0',
							'248.0.0.0', '252.0.0.0', '254.0.0.0', '255.0.0.0',
							'255.128.0.0', '255.192.0.0', '255.224.0.0', '255.240.0.0',
							'255.248.0.0', '255.252.0.0', '255.254.0.0', '255.255.0.0',
							'255.255.128.0', '255.255.192.0', '255.255.224.0', '255.255.240.0',
							'255.255.248.0', '255.255.252.0', '255.255.254.0', '255.255.255.0',
							'255.255.255.128', '255.255.255.192', '255.255.255.224', '255.255.255.240',
							'255.255.255.248', '255.255.255.252', '255.255.255.254', '255.255.255.255'
						);
						foreach ($subnetMasks as $mask):
							$selected = ($mask === '255.255.255.0') ? ' selected' : '';
						?>
						<option value="<?=htmlspecialchars($mask)?>"<?=$selected?>><?=htmlspecialchars($mask)?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<td><label for="maxSubnets"><?=gettext("Maximum Subnets")?>:</label></td>
				<td>
					<select id="maxSubnets" class="form-control">
						<?php
						$powers = array(2, 4, 8, 16, 32, 64, 128, 256, 512, 1024, 2048, 4096, 8192,
							16384, 32768, 65536, 131072, 262144, 524288, 1048576, 2097152, 4194304,
							8388608, 16777216, 33554432, 67108864, 134217728, 268435456, 536870912,
							1073741824, 2147483648, 4294967296);
						foreach ($powers as $val):
						?>
						<option value="<?=htmlspecialchars($val)?>"><?=htmlspecialchars($val)?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<td><label for="maxAddresses"><?=gettext("Maximum Addresses per Subnet")?>:</label></td>
				<td>
					<select id="maxAddresses" class="form-control">
						<?php foreach ($powers as $val): ?>
						<option value="<?=htmlspecialchars($val)?>"><?=htmlspecialchars($val)?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
		</tbody>
	</table>
</div>

<div class="table-responsive">
	<table class="table table-striped table-hover">
		<tbody>
			<tr>
				<td style="width: 30%;"><strong><?=gettext("Network Address")?>:</strong></td>
				<td>
					<span id="network"></span>
					<button type="button" class="btn btn-xs btn-default" onclick="copyToClipboard('network')" title="<?=gettext("Copy to clipboard")?>">
						<i class="fa fa-clipboard"></i>
					</button>
				</td>
			</tr>
			<tr>
				<td><strong><?=gettext("CIDR Route")?>:</strong></td>
				<td>
					<span id="cidrRoute"></span>
					<button type="button" class="btn btn-xs btn-default" onclick="copyToClipboard('cidrRoute')" title="<?=gettext("Copy to clipboard")?>">
						<i class="fa fa-clipboard"></i>
					</button>
				</td>
			</tr>
			<tr>
				<td><strong><?=gettext("Subnet Range")?>:</strong></td>
				<td>
					<span id="subnetRange"></span>
					<button type="button" class="btn btn-xs btn-default" onclick="copyToClipboard('subnetRange')" title="<?=gettext("Copy to clipboard")?>">
						<i class="fa fa-clipboard"></i>
					</button>
				</td>
			</tr>
			<tr>
				<td><strong><?=gettext("Wildcard Mask")?>:</strong></td>
				<td>
					<span id="wildcard"></span>
					<button type="button" class="btn btn-xs btn-default" onclick="copyToClipboard('wildcard')" title="<?=gettext("Copy to clipboard")?>">
						<i class="fa fa-clipboard"></i>
					</button>
				</td>
			</tr>
			<tr>
				<td><strong><?=gettext("Broadcast Address")?>:</strong></td>
				<td>
					<span id="broadcast"></span>
					<button type="button" class="btn btn-xs btn-default" onclick="copyToClipboard('broadcast')" title="<?=gettext("Copy to clipboard")?>">
						<i class="fa fa-clipboard"></i>
					</button>
				</td>
			</tr>
			<tr>
				<td><strong><?=gettext("Assignable Addresses")?>:</strong></td>
				<td><span id="assignable"></span></td>
			</tr>
			<tr>
				<td><strong><?=gettext("Maximum Subnets")?>:</strong></td>
				<td><span id="maxSubnetsResult"></span></td>
			</tr>
			<tr>
				<td><strong><?=gettext("Maximum Addresses")?>:</strong></td>
				<td><span id="maxAddressesResult"></span></td>
			</tr>
		</tbody>
	</table>
</div>

<script type="text/javascript">
//<![CDATA[
// String.prototype.padStart polyfill for older browsers
if (!String.prototype.padStart) {
	String.prototype.padStart = function(targetLength, padString) {
		targetLength = targetLength >> 0;
		padString = String(padString || ' ');
		if (this.length > targetLength) {
			return String(this);
		} else {
			targetLength = targetLength - this.length;
			if (targetLength > padString.length) {
				padString += padString.repeat(targetLength / padString.length);
			}
			return padString.slice(0, targetLength) + String(this);
		}
	};
}

const SUBNET_MASKS = [
	{ cidr: 1, mask: '128.0.0.0' }, { cidr: 2, mask: '192.0.0.0' }, { cidr: 3, mask: '224.0.0.0' },
	{ cidr: 4, mask: '240.0.0.0' }, { cidr: 5, mask: '248.0.0.0' }, { cidr: 6, mask: '252.0.0.0' },
	{ cidr: 7, mask: '254.0.0.0' }, { cidr: 8, mask: '255.0.0.0' }, { cidr: 9, mask: '255.128.0.0' },
	{ cidr: 10, mask: '255.192.0.0' }, { cidr: 11, mask: '255.224.0.0' }, { cidr: 12, mask: '255.240.0.0' },
	{ cidr: 13, mask: '255.248.0.0' }, { cidr: 14, mask: '255.252.0.0' }, { cidr: 15, mask: '255.254.0.0' },
	{ cidr: 16, mask: '255.255.0.0' }, { cidr: 17, mask: '255.255.128.0' }, { cidr: 18, mask: '255.255.192.0' },
	{ cidr: 19, mask: '255.255.224.0' }, { cidr: 20, mask: '255.255.240.0' }, { cidr: 21, mask: '255.255.248.0' },
	{ cidr: 22, mask: '255.255.252.0' }, { cidr: 23, mask: '255.255.254.0' }, { cidr: 24, mask: '255.255.255.0' },
	{ cidr: 25, mask: '255.255.255.128' }, { cidr: 26, mask: '255.255.255.192' }, { cidr: 27, mask: '255.255.255.224' },
	{ cidr: 28, mask: '255.255.255.240' }, { cidr: 29, mask: '255.255.255.248' }, { cidr: 30, mask: '255.255.255.252' },
	{ cidr: 31, mask: '255.255.255.254' }, { cidr: 32, mask: '255.255.255.255' }
];

const ipInput = document.getElementById('ip');
const cidrSelect = document.getElementById('cidr');
const subnetSelect = document.getElementById('subnet');
const maxSubnetsSelect = document.getElementById('maxSubnets');
const maxAddressesSelect = document.getElementById('maxAddresses');

const networkSpan = document.getElementById('network');
const cidrRouteSpan = document.getElementById('cidrRoute');
const subnetRangeSpan = document.getElementById('subnetRange');
const wildcardSpan = document.getElementById('wildcard');
const broadcastSpan = document.getElementById('broadcast');
const assignableSpan = document.getElementById('assignable');
const maxSubnetsResultSpan = document.getElementById('maxSubnetsResult');
const maxAddressesResultSpan = document.getElementById('maxAddressesResult');

function validateAndNormalizeIP(ip) {
	const parts = ip.split('.');
	if (parts.length !== 4) return null;
	
	const normalized = parts.map(function(part) {
		const num = parseInt(part, 10);
		if (isNaN(num) || num < 0 || num > 255) return null;
		return num;
	});
	
	if (normalized.includes(null)) return null;
	return normalized.join('.');
}

function ipToInt(ip) {
	const parts = ip.split('.').map(Number);
	return ((parts[0] << 24) | (parts[1] << 16) | (parts[2] << 8) | parts[3]) >>> 0;
}

function intToIp(int) {
	return [(int >>> 24) & 0xFF, (int >>> 16) & 0xFF, (int >>> 8) & 0xFF, int & 0xFF].join('.');
}

function getSubnetMaskFromCidr(cidr) {
	const item = SUBNET_MASKS.find(function(m) { return m.cidr === cidr; });
	return item ? item.mask : '255.255.255.0';
}

function getCidrFromSubnetMask(mask) {
	const item = SUBNET_MASKS.find(function(m) { return m.mask === mask; });
	return item ? item.cidr : 24;
}

function getWildcardMask(subnetMask) {
	return subnetMask.split('.').map(function(part) { 
		return 255 - parseInt(part); 
	}).join('.');
}

function getNetworkAddress(ip, subnetMask) {
	return intToIp((ipToInt(ip) & ipToInt(subnetMask)) >>> 0);
}

function getBroadcastAddress(networkAddress, cidr) {
	const networkInt = ipToInt(networkAddress);
	const hostBits = 32 - cidr;
	return intToIp((networkInt | ((1 << hostBits) - 1)) >>> 0);
}

function calculate() {
	const ip = ipInput.value.trim();
	const cidr = parseInt(cidrSelect.value, 10);
	
	const normalizedIp = validateAndNormalizeIP(ip);
	if (!normalizedIp) {
		clearResults();
		return;
	}
	
	if (normalizedIp !== ip) {
		ipInput.value = normalizedIp;
	}
	
	const subnetMask = getSubnetMaskFromCidr(cidr);
	const networkAddress = getNetworkAddress(normalizedIp, subnetMask);
	const broadcastAddress = getBroadcastAddress(networkAddress, cidr);
	const wildcardMask = getWildcardMask(subnetMask);
	const maxSubnets = Math.pow(2, cidr);
	const maxAddresses = Math.pow(2, 32 - cidr);
	const usableHosts = maxAddresses <= 2 ? maxAddresses : maxAddresses - 2;
	
	let firstUsable, lastUsable;
	if (cidr === 32) {
		firstUsable = networkAddress;
		lastUsable = networkAddress;
	} else if (cidr === 31) {
		firstUsable = networkAddress;
		lastUsable = broadcastAddress;
	} else {
		firstUsable = intToIp(ipToInt(networkAddress) + 1);
		lastUsable = intToIp(ipToInt(broadcastAddress) - 1);
	}
	
	networkSpan.textContent = networkAddress;
	cidrRouteSpan.textContent = networkAddress + '/' + cidr;
	subnetRangeSpan.textContent = networkAddress + ' - ' + broadcastAddress;
	wildcardSpan.textContent = wildcardMask;
	broadcastSpan.textContent = broadcastAddress;
	assignableSpan.textContent = firstUsable + ' - ' + lastUsable + ' (' + usableHosts.toLocaleString() + ' hosts)';
	maxSubnetsResultSpan.textContent = maxSubnets.toLocaleString();
	maxAddressesResultSpan.textContent = maxAddresses.toLocaleString();
	
	subnetSelect.value = subnetMask;
	maxSubnetsSelect.value = maxSubnets;
	maxAddressesSelect.value = maxAddresses;
}

function clearResults() {
	[networkSpan, cidrRouteSpan, subnetRangeSpan, wildcardSpan, broadcastSpan, 
	 assignableSpan, maxSubnetsResultSpan, maxAddressesResultSpan].forEach(function(el) {
		el.textContent = '—';
	});
}

function copyToClipboard(elementId) {
	const element = document.getElementById(elementId);
	const text = element.textContent || element.innerText;
	
	// Modern clipboard API
	if (navigator.clipboard && navigator.clipboard.writeText) {
		navigator.clipboard.writeText(text).then(function() {
			showCopyFeedback(element);
		}).catch(function(err) {
			console.error('Copy failed:', err);
		});
	} else {
		// Fallback for older browsers
		const textarea = document.createElement('textarea');
		textarea.value = text;
		textarea.style.position = 'fixed';
		textarea.style.opacity = '0';
		document.body.appendChild(textarea);
		textarea.select();
		
		try {
			document.execCommand('copy');
			showCopyFeedback(element);
		} catch (err) {
			console.error('Copy fallback failed:', err);
		}
		
		document.body.removeChild(textarea);
	}
}

function showCopyFeedback(element) {
	const feedback = document.createElement('span');
	feedback.className = 'label label-success';
	feedback.textContent = '<?=gettext("Copied!")?>';
	feedback.style.marginLeft = '5px';
	element.parentNode.appendChild(feedback);
	
	setTimeout(function() {
		feedback.remove();
	}, 2000);
}

ipInput.addEventListener('blur', calculate);
ipInput.addEventListener('keypress', function(e) { 
	if (e.key === 'Enter' || e.keyCode === 13) calculate(); 
});
cidrSelect.addEventListener('change', calculate);
subnetSelect.addEventListener('change', function() {
	cidrSelect.value = getCidrFromSubnetMask(this.value);
	calculate();
});
maxSubnetsSelect.addEventListener('change', function() {
	const cidr = Math.log2(parseInt(this.value, 10));
	if (cidr >= 1 && cidr <= 32 && Number.isInteger(cidr)) {
		cidrSelect.value = cidr;
		calculate();
	}
});
maxAddressesSelect.addEventListener('change', function() {
	const hostBits = Math.log2(parseInt(this.value, 10));
	if (Number.isInteger(hostBits)) {
		const cidr = 32 - hostBits;
		if (cidr >= 1 && cidr <= 32) {
			cidrSelect.value = cidr;
			calculate();
		}
	}
});

// Initialize
calculate();
//]]>
</script>

<?php else: ?>

<!-- IPv6 Calculator -->
<div class="table-responsive">
	<table class="table table-striped table-hover">
		<tbody>
			<tr>
				<td style="width: 30%;"><label for="ipv6_addr"><?=gettext("IPv6 Address")?>:</label></td>
				<td><input type="text" id="ipv6_addr" class="form-control" placeholder="2001:db8::1"></td>
			</tr>
			<tr>
				<td><label for="ipv6_mask"><?=gettext("Prefix Length")?>:</label></td>
				<td>
					<select id="ipv6_mask" class="form-control">
						<?php for ($i = 1; $i <= 128; $i++): ?>
						<option value="<?=htmlspecialchars($i)?>"<?=($i == 64 ? " selected" : "")?>><?=htmlspecialchars($i)?></option>
						<?php endfor; ?>
					</select>
				</td>
			</tr>
		</tbody>
	</table>
</div>

<div class="table-responsive">
	<table class="table table-striped table-hover">
		<tbody>
			<tr>
				<td style="width: 30%;"><strong><?=gettext("Full Address")?>:</strong></td>
				<td>
					<span id="ipv6_full"></span>
					<button type="button" class="btn btn-xs btn-default" onclick="copyToClipboardV6('ipv6_full')" title="<?=gettext("Copy to clipboard")?>">
						<i class="fa fa-clipboard"></i>
					</button>
				</td>
			</tr>
			<tr>
				<td><strong><?=gettext("Network Address")?>:</strong></td>
				<td>
					<span id="ipv6_network"></span>
					<button type="button" class="btn btn-xs btn-default" onclick="copyToClipboardV6('ipv6_network')" title="<?=gettext("Copy to clipboard")?>">
						<i class="fa fa-clipboard"></i>
					</button>
				</td>
			</tr>
			<tr>
				<td><strong><?=gettext("Start Address")?>:</strong></td>
				<td><span id="ipv6_start"></span></td>
			</tr>
			<tr>
				<td><strong><?=gettext("End Address")?>:</strong></td>
				<td><span id="ipv6_end"></span></td>
			</tr>
			<tr>
				<td><strong><?=gettext("/64 Subnets Available")?>:</strong></td>
				<td><span id="ipv6_subnets"></span></td>
			</tr>
		</tbody>
	</table>
</div>

<script type="text/javascript">
//<![CDATA[
function expandIPv6(addr) {
	try {
		let parts = addr.split("::");
		let left = (parts[0] !== undefined && parts[0] !== '') ? parts[0].split(":") : [];
		let right = (parts[1] !== undefined && parts[1] !== '') ? parts[1].split(":") : [];
		let missing = 8 - (left.length + right.length);
		let segs = left.concat(new Array(missing).fill('0000')).concat(right);
		return segs.map(function(x) { return x.padStart(4, '0'); }).join(":");
	} catch (e) {
		return null;
	}
}

function validateIPv6(ip) {
	// Allow compressed notation with ::
	if (ip.includes('::')) {
		// Verify only one :: occurrence
		const count = (ip.match(/::/g) || []).length;
		if (count > 1) return false;
	}
	// Standard validation
	return /^([0-9a-f]{0,4}:){2,7}[0-9a-f]{0,4}$/i.test(ip);
}

function calculateIPv6Network(fullAddr, prefixLen) {
	// Split into hextets
	const hextets = fullAddr.split(':');
	const bits = hextets.map(function(h) { return parseInt(h, 16); });
	
	// Calculate network address
	const networkBits = [];
	let bitsRemaining = prefixLen;
	
	for (let i = 0; i < 8; i++) {
		if (bitsRemaining >= 16) {
			// Keep entire hextet
			networkBits.push(bits[i]);
			bitsRemaining -= 16;
		} else if (bitsRemaining > 0) {
			// Mask partial hextet
			const mask = (0xFFFF << (16 - bitsRemaining)) & 0xFFFF;
			networkBits.push(bits[i] & mask);
			bitsRemaining = 0;
		} else {
			// Zero remaining hextets
			networkBits.push(0);
		}
	}
	
	return networkBits.map(function(b) { 
		return b.toString(16).padStart(4, '0'); 
	}).join(':');
}

function updateIPv6Calc() {
	const addr = document.getElementById('ipv6_addr').value.trim();
	const mask = parseInt(document.getElementById('ipv6_mask').value, 10);
	
	if (!addr) {
		document.getElementById('ipv6_full').textContent = '';
		document.getElementById('ipv6_network').textContent = '';
		document.getElementById('ipv6_start').textContent = '';
		document.getElementById('ipv6_end').textContent = '';
		document.getElementById('ipv6_subnets').textContent = '';
		return;
	}
	
	if (!validateIPv6(addr)) {
		document.getElementById('ipv6_full').textContent = '<?=gettext("Invalid IPv6 address")?>';
		document.getElementById('ipv6_network').textContent = '';
		document.getElementById('ipv6_start').textContent = '';
		document.getElementById('ipv6_end').textContent = '';
		document.getElementById('ipv6_subnets').textContent = '';
		return;
	}
	
	const full = expandIPv6(addr);
	if (!full) {
		document.getElementById('ipv6_full').textContent = '<?=gettext("Error expanding address")?>';
		return;
	}
	
	const networkAddr = calculateIPv6Network(full, mask);
	const subnets = mask < 64 ? Math.pow(2, 64 - mask).toLocaleString() : "1";
	
	document.getElementById('ipv6_full').textContent = full;
	document.getElementById('ipv6_network').textContent = networkAddr + '/' + mask;
	document.getElementById('ipv6_start').textContent = networkAddr;
	document.getElementById('ipv6_end').textContent = full;
	document.getElementById('ipv6_subnets').textContent = subnets;
}

function copyToClipboardV6(elementId) {
	const element = document.getElementById(elementId);
	const text = element.textContent || element.innerText;
	
	if (navigator.clipboard && navigator.clipboard.writeText) {
		navigator.clipboard.writeText(text).then(function() {
			showCopyFeedback(element);
		}).catch(function(err) {
			console.error('Copy failed:', err);
		});
	} else {
		const textarea = document.createElement('textarea');
		textarea.value = text;
		textarea.style.position = 'fixed';
		textarea.style.opacity = '0';
		document.body.appendChild(textarea);
		textarea.select();
		
		try {
			document.execCommand('copy');
			showCopyFeedback(element);
		} catch (err) {
			console.error('Copy fallback failed:', err);
		}
		
		document.body.removeChild(textarea);
	}
}

document.getElementById('ipv6_addr').addEventListener('input', updateIPv6Calc);
document.getElementById('ipv6_mask').addEventListener('change', updateIPv6Calc);
//]]>
</script>

<?php endif; ?>

	</div>
</div>

</div><!-- #calculator-content -->

<script type="text/javascript">
//<![CDATA[
// Show calculator when JS is available
document.getElementById('calculator-content').style.display = 'block';
//]]>
</script>

<?php include("foot.inc"); ?>
