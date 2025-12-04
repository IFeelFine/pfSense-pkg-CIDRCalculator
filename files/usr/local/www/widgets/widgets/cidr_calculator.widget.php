<?php
/*
 * cidr_calculator.widget.php
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
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

require_once("guiconfig.inc");
require_once("cidr_calc.inc");

// Widget metadata
$widgetTitle = gettext("CIDR Calculator");
$widgetTitleLink = "diag_cidr_calculator.php";

// No user preferences to load - widget always shows both calculators
// This keeps the widget stateless and avoids polluting config file

?>

<!-- IPv4 Calculator -->
<div id="widget_ipv4_block" style="margin-bottom: 15px;">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title"><?= gettext("IPv4") ?></h3>
		</div>
		<div class="panel-body">
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
	</div>
</div>

<!-- IPv6 Calculator -->
<div id="widget_ipv6_block">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title"><?= gettext("IPv6") ?></h3>
		</div>
		<div class="panel-body">
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

	// Event listeners with proper error handling
	document.getElementById('widget_ipv4_input').addEventListener('input', widget_updateIPv4);
	document.getElementById('widget_ipv6_addr').addEventListener('input', widget_updateIPv6);
	document.getElementById('widget_ipv6_mask').addEventListener('change', widget_updateIPv6);

	// Initialize IPv4 calculation
	widget_updateIPv4();

	//]]>
</script>