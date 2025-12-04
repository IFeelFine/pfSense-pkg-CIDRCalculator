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
 * Objective: IP CIDR calculator for pfSense in the Diagnostic menu.        */

require_once("guiconfig.inc");
require_once("cidr_calc.inc");

// Widget metadata
$widgetTitle = gettext("CIDR Calculator");
$widgetTitleLink = "diag_cidr_calculator.php";

// Get widget configuration with defaults
$show_ipv4 = $config['widgets']['cidr_calculator']['show_ipv4'] ?? 'true';
$show_ipv6 = $config['widgets']['cidr_calculator']['show_ipv6'] ?? 'true';

// Handle configuration save with CSRF validation
if ($_POST && isset($_POST['show_ipv4']) && isset($_POST['show_ipv6'])) {
	// Verify CSRF token
	if (!csrf_check()) {
		csrf_error();
		exit;
	}

	// Validate input - only accept 'true' or 'false'
	$new_ipv4 = ($_POST['show_ipv4'] === 'true') ? 'true' : 'false';
	$new_ipv6 = ($_POST['show_ipv6'] === 'true') ? 'true' : 'false';

	init_config_arr(array('widgets', 'cidr_calculator'));
	$config['widgets']['cidr_calculator']['show_ipv4'] = $new_ipv4;
	$config['widgets']['cidr_calculator']['show_ipv6'] = $new_ipv6;
	write_config(gettext("Updated CIDR Calculator widget settings"));

	// Redirect to avoid form resubmission
	header("Location: /");
	exit;
}
?>

<div class="table-responsive">
	<table class="table table-striped table-hover table-condensed">
		<tbody>
			<tr>
				<td>
					<label class="checkbox-inline">
						<input type="checkbox" id="widget_show_ipv4" <?= $show_ipv4 == 'true' ? 'checked' : '' ?>> <?= gettext("IPv4") ?>
					</label>
					<label class="checkbox-inline">
						<input type="checkbox" id="widget_show_ipv6" <?= $show_ipv6 == 'true' ? 'checked' : '' ?>> <?= gettext("IPv6") ?>
					</label>
				</td>
			</tr>
		</tbody>
	</table>
</div>

<!-- IPv4 Widget Calculator -->
<div id="widget_ipv4_block" style="display: <?= $show_ipv4 == 'true' ? 'block' : 'none' ?>; margin-bottom: 15px;">
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<tbody>
				<tr>
					<td style="width: 30%;"><strong><?= gettext("IP/CIDR") ?>:</strong></td>
					<td>
						<input type="text" id="widget_ipv4_input" class="form-control input-sm" placeholder="192.168.1.0/24" value="192.168.1.0/24" style="width: 100%;" aria-label="<?= gettext("IPv4 address with CIDR notation") ?>">
					</td>
				</tr>
				<tr id="widget_ipv4_results_row" style="display: none;">
					<td colspan="2">
						<div id="widget_ipv4_results" style="font-size: 12px;" role="region" aria-live="polite"></div>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>

<!-- IPv6 Widget Calculator -->
<div id="widget_ipv6_block" style="display: <?= $show_ipv6 == 'true' ? 'block' : 'none' ?>;">
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<tbody>
				<tr>
					<td style="width: 30%;"><strong><?= gettext("IPv6 Address") ?>:</strong></td>
					<td>
						<input type="text" id="widget_ipv6_addr" class="form-control input-sm" placeholder="2001:db8::" style="width: 100%;" aria-label="<?= gettext("IPv6 address") ?>">
					</td>
				</tr>
				<tr>
					<td><strong><?= gettext("Prefix Length") ?>:</strong></td>
					<td>
						<select id="widget_ipv6_mask" class="form-control input-sm" style="width: 100%;" aria-label="<?= gettext("IPv6 prefix length") ?>">
							<?php for ($i = 1; $i <= 128; $i++): ?>
								<option value="<?= htmlspecialchars($i) ?>" <?= $i == 64 ? "selected" : "" ?>><?= htmlspecialchars($i) ?></option>
							<?php endfor; ?>
						</select>
					</td>
				</tr>
				<tr id="widget_ipv6_results_row" style="display: none;">
					<td colspan="2">
						<div id="widget_ipv6_results" style="font-size: 12px;" role="region" aria-live="polite"></div>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>

<script type="text/javascript">
	//<![CDATA[

	// Load subnet masks from server-generated JSON to avoid duplication
	const SUBNET_MASKS_WIDGET = <?= cidr_calc_get_subnet_masks_json() ?>;

	function widget_validateIP(ip) {
		const parts = ip.split('.');
		if (parts.length !== 4) return null;
		const normalized = parts.map(part => {
			const num = parseInt(part, 10);
			if (isNaN(num)) return null;
			return Math.min(255, Math.max(0, num));
		});
		if (normalized.includes(null)) return null;
		return normalized.join('.');
	}

	function widget_ipToInt(ip) {
		const parts = ip.split('.').map(Number);
		return ((parts[0] << 24) | (parts[1] << 16) | (parts[2] << 8) | parts[3]) >>> 0;
	}

	function widget_intToIp(int) {
		return [(int >>> 24) & 0xFF, (int >>> 16) & 0xFF, (int >>> 8) & 0xFF, int & 0xFF].join('.');
	}

	function widget_getSubnetMask(cidr) {
		const item = SUBNET_MASKS_WIDGET.find(m => m.cidr === cidr);
		return item ? item.mask : '255.255.255.0';
	}

	function widget_calculateIPv4(input) {
		try {
			const parts = input.split('/');
			if (parts.length !== 2) return null;

			const ip = widget_validateIP(parts[0].trim());
			const cidr = parseInt(parts[1].trim(), 10);

			if (!ip || isNaN(cidr) || cidr < 1 || cidr > 32) return null;

			const subnetMask = widget_getSubnetMask(cidr);
			const ipInt = widget_ipToInt(ip);
			const maskInt = widget_ipToInt(subnetMask);
			const networkInt = (ipInt & maskInt) >>> 0;
			const networkAddress = widget_intToIp(networkInt);
			const hostBits = 32 - cidr;
			const broadcastInt = (networkInt | ((1 << hostBits) - 1)) >>> 0;
			const broadcastAddress = widget_intToIp(broadcastInt);
			const totalAddresses = Math.pow(2, hostBits);
			const usableHosts = totalAddresses <= 2 ? totalAddresses : totalAddresses - 2;

			let firstUsable, lastUsable;
			if (cidr === 32) {
				firstUsable = networkAddress;
				lastUsable = networkAddress;
			} else if (cidr === 31) {
				firstUsable = networkAddress;
				lastUsable = broadcastAddress;
			} else {
				firstUsable = widget_intToIp(networkInt + 1);
				lastUsable = widget_intToIp(broadcastInt - 1);
			}

			return {
				network: networkAddress,
				cidr: cidr,
				mask: subnetMask,
				broadcast: broadcastAddress,
				firstUsable: firstUsable,
				lastUsable: lastUsable,
				usableHosts: usableHosts,
				totalAddresses: totalAddresses
			};
		} catch (e) {
			console.error('CIDR calculation error: ', e);
			return null;
		}
	}

	function widget_createResultElement(label, value) {
		const container = document.createElement('div');

		const strong = document.createElement('strong');
		strong.textContent = label + ': ';
		container.appendChild(strong);

		const text = document.createTextNode(value);
		container.appendChild(text);

		return container;
	}

	function widget_updateIPv4() {
		const input = document.getElementById('widget_ipv4_input').value.trim();
		const resultsDiv = document.getElementById('widget_ipv4_results');
		const resultsRow = document.getElementById('widget_ipv4_results_row');

		if (!input) {
			resultsRow.style.display = 'none';
			return;
		}

		const result = widget_calculateIPv4(input);

		// Clear previous results
		resultsDiv.textContent = '';

		if (!result) {
			const errorSpan = document.createElement('span');
			errorSpan.style.color = '#d9534f';
			errorSpan.textContent = '<?= gettext("Invalid IP/CIDR format. Use: 192.168.1.0/24") ?>';
			resultsDiv.appendChild(errorSpan);
			resultsRow.style.display = 'table-row';
			return;
		}

		// Build results using DOM manipulation (XSS-safe)
		resultsDiv.appendChild(widget_createResultElement('<?= gettext("Network") ?>', result.network + '/' + result.cidr));
		resultsDiv.appendChild(widget_createResultElement('<?= gettext("Mask") ?>', result.mask));
		resultsDiv.appendChild(widget_createResultElement('<?= gettext("Range") ?>', result.firstUsable + ' - ' + result.lastUsable));
		resultsDiv.appendChild(widget_createResultElement('<?= gettext("Broadcast") ?>', result.broadcast));
		resultsDiv.appendChild(widget_createResultElement('<?= gettext("Usable Hosts") ?>', result.usableHosts.toLocaleString()));

		resultsRow.style.display = 'table-row';
	}

	function widget_expandIPv6(addr) {
		try {
			let parts = addr.split("::");
			let left = (parts[0] !== undefined && parts[0] !== '') ? parts[0].split(":") : [];
			let right = (parts[1] !== undefined && parts[1] !== '') ? parts[1].split(":") : [];
			let missing = 8 - (left.length + right.length);
			let segs = left.concat(new Array(missing).fill('0000')).concat(right);
			return segs.map(x => x.padStart(4, '0')).join(":");
		} catch (e) {
			return null;
		}
	}

	function widget_validateIPv6(ip) {
		// Allow compressed notation with ::
		if (ip.includes('::')) {
			// Verify only one :: occurrence
			if ((ip.match(/::/g) || []).length > 1) return false;
		}
		// Standard validation
		return /^([0-9a-f]{0,4}:){2,7}[0-9a-f]{0,4}$/i.test(ip);
	}

	function widget_calculateIPv6Network(fullAddr, prefixLen) {
		const hextets = fullAddr.split(':');
		const bits = hextets.map(h => parseInt(h, 16));

		const networkBits = [];
		let bitsRemaining = prefixLen;

		for (let i = 0; i < 8; i++) {
			if (bitsRemaining >= 16) {
				networkBits.push(bits[i]);
				bitsRemaining -= 16;
			} else if (bitsRemaining > 0) {
				const mask = (0xFFFF << (16 - bitsRemaining)) & 0xFFFF;
				networkBits.push(bits[i] & mask);
				bitsRemaining = 0;
			} else {
				networkBits.push(0);
			}
		}

		return networkBits.map(b => b.toString(16).padStart(4, '0')).join(':');
	}

	function widget_updateIPv6() {
		const addr = document.getElementById('widget_ipv6_addr').value.trim();
		const mask = parseInt(document.getElementById('widget_ipv6_mask').value, 10);
		const resultsDiv = document.getElementById('widget_ipv6_results');
		const resultsRow = document.getElementById('widget_ipv6_results_row');

		if (!addr) {
			resultsRow.style.display = 'none';
			return;
		}

		// Clear previous results
		resultsDiv.textContent = '';

		if (!widget_validateIPv6(addr)) {
			const errorSpan = document.createElement('span');
			errorSpan.style.color = '#d9534f';
			errorSpan.textContent = '<?= gettext("Invalid IPv6 address format") ?>';
			resultsDiv.appendChild(errorSpan);
			resultsRow.style.display = 'table-row';
			return;
		}

		const full = widget_expandIPv6(addr);
		if (!full) {
			const errorSpan = document.createElement('span');
			errorSpan.style.color = '#d9534f';
			errorSpan.textContent = '<?= gettext("Error expanding IPv6 address") ?>';
			resultsDiv.appendChild(errorSpan);
			resultsRow.style.display = 'table-row';
			return;
		}

		const network = widget_calculateIPv6Network(full, mask);
		const subnets = mask < 64 ? Math.pow(2, 64 - mask).toLocaleString() : "1";

		// Build results using DOM manipulation (XSS-safe)
		resultsDiv.appendChild(widget_createResultElement('<?= gettext("Full") ?>', full));
		resultsDiv.appendChild(widget_createResultElement('<?= gettext("Network") ?>', network + '/' + mask));
		resultsDiv.appendChild(widget_createResultElement('<?= gettext("/64 Subnets") ?>', subnets));

		resultsRow.style.display = 'table-row';
	}

	function widget_saveSettings(show_ipv4, show_ipv6) {
		// Create hidden form for proper CSRF-protected POST
		const form = document.createElement('form');
		form.method = 'POST';
		form.action = window.location.href;
		form.style.display = 'none';

		// Add CSRF token
		const csrfInput = document.createElement('input');
		csrfInput.type = 'hidden';
		csrfInput.name = '__csrf_magic';
		csrfInput.value = csrf.magic; // pfSense global csrf object
		form.appendChild(csrfInput);

		// Add IPv4 setting
		const ipv4Input = document.createElement('input');
		ipv4Input.type = 'hidden';
		ipv4Input.name = 'show_ipv4';
		ipv4Input.value = show_ipv4 ? 'true' : 'false';
		form.appendChild(ipv4Input);

		// Add IPv6 setting
		const ipv6Input = document.createElement('input');
		ipv6Input.type = 'hidden';
		ipv6Input.name = 'show_ipv6';
		ipv6Input.value = show_ipv6 ? 'true' : 'false';
		form.appendChild(ipv6Input);

		document.body.appendChild(form);
		form.submit();
	}

	// Event listeners with proper error handling
	document.getElementById('widget_show_ipv4').addEventListener('change', function () {
		const show_ipv4 = this.checked;
		const show_ipv6 = document.getElementById('widget_show_ipv6').checked;

		document.getElementById('widget_ipv4_block').style.display = show_ipv4 ? 'block' : 'none';
		widget_saveSettings(show_ipv4, show_ipv6);
	});

	document.getElementById('widget_show_ipv6').addEventListener('change', function () {
		const show_ipv4 = document.getElementById('widget_show_ipv4').checked;
		const show_ipv6 = this.checked;

		document.getElementById('widget_ipv6_block').style.display = show_ipv6 ? 'block' : 'none';
		widget_saveSettings(show_ipv4, show_ipv6);
	});

	document.getElementById('widget_ipv4_input').addEventListener('input', widget_updateIPv4);
	document.getElementById('widget_ipv6_addr').addEventListener('input', widget_updateIPv6);
	document.getElementById('widget_ipv6_mask').addEventListener('change', widget_updateIPv6);

	// Initialize IPv4 calculation
	widget_updateIPv4();

	//]]>
</script>