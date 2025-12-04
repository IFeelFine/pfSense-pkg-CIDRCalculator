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

##|+PRIV
##|*IDENT=page-diagnostics-cidr-calculator
##|*NAME=Diagnostics: CIDR Calculator
##|*DESCR=Allow access to the 'Diagnostics: CIDR Calculator' page.
##|*MATCH=diag_cidr_calculator.php*
##|-PRIV

require_once("guiconfig.inc");
require_once("cidr_calc.inc");

$pgtitle = array(gettext("Diagnostics"), gettext("CIDR Calculator"));
$active_tab = $_GET['tab'] ?? 'ipv4';
$allowed_tabs = array('ipv4', 'ipv6');

// Validate tab parameter with strict comparison
if (!in_array($active_tab, $allowed_tabs, true)) {
	$active_tab = 'ipv4';
	// Log invalid access attempt
	if (isset($_GET['tab'])) {
		log_error(sprintf(
			'[CIDR Calculator] Invalid tab parameter "%s" from %s',
			$_GET['tab'],
			$_SERVER['REMOTE_ADDR']
		));
	}
}

$tab_array = array();
$tab_array[] = array(gettext("IPv4"), $active_tab == "ipv4", "/diag_cidr_calculator.php?tab=ipv4");
$tab_array[] = array(gettext("IPv6"), $active_tab == "ipv6", "/diag_cidr_calculator.php?tab=ipv6");

include("head.inc");
?>

<!-- JavaScript Required Notice -->
<noscript>
	<div class="alert alert-danger" role="alert">
		<h4><?= gettext("JavaScript Required") ?></h4>
		<p><?= gettext("The CIDR Calculator requires JavaScript to function. Please enable JavaScript in your browser and refresh this page.") ?></p>
	</div>
</noscript>

<div id="calculator-content" style="display: none;">

	<!-- Informational alert -->
	<div class="alert alert-info" role="alert">
		<i class="fa fa-info-circle"></i>
		<?= gettext("All calculations are performed client-side in your browser. No data is sent to the server.") ?>
	</div>

	<?php
	display_top_tabs($tab_array, true);
	?>

	<div class="panel panel-default">
		<div class="panel-heading">
			<h2 class="panel-title"><?= gettext("CIDR Calculator") ?></h2>
		</div>
		<div class="panel-body">

			<?php if ($active_tab == "ipv4"): ?>

				<!-- IPv4 Calculator -->
				<div class="table-responsive">
					<table class="table table-striped table-hover">
						<tbody>
							<tr>
								<td style="width: 30%;"><label for="ip"><?= gettext("IP Address") ?>:</label></td>
								<td>
									<input type="text" id="ip" class="form-control" placeholder="192.168.1.0" value="192.168.1.0" aria-label="<?= gettext("IPv4 Address") ?>" aria-describedby="ip-help">
									<small id="ip-help" class="help-block">
										<?= gettext("Enter IPv4 address in dotted decimal notation (e.g., 192.168.1.0)") ?>
									</small>
								</td>
							</tr>
							<tr>
								<td><label for="cidr"><?= gettext("CIDR Mask Bits") ?>:</label></td>
								<td>
									<select id="cidr" class="form-control" aria-label="<?= gettext("CIDR prefix length") ?>" aria-describedby="cidr-help">
										<?php for ($i = 1; $i <= 32; $i++): ?>
											<option value="<?= htmlspecialchars($i) ?>" <?= ($i == 24 ? " selected" : "") ?>><?= htmlspecialchars($i) ?></option>
										<?php endfor; ?>
									</select>
									<small id="cidr-help" class="help-block">
										<?= gettext("Number of network bits (1-32). Common: /24 = Class C, /16 = Class B, /8 = Class A") ?>
									</small>
								</td>
							</tr>
							<tr>
								<td><label for="subnet"><?= gettext("Subnet Mask") ?>:</label></td>
								<td>
									<select id="subnet" class="form-control" aria-label="<?= gettext("Subnet mask in dotted decimal notation") ?>">
										<?php
										$subnetMasks = array(
											'128.0.0.0',
											'192.0.0.0',
											'224.0.0.0',
											'240.0.0.0',
											'248.0.0.0',
											'252.0.0.0',
											'254.0.0.0',
											'255.0.0.0',
											'255.128.0.0',
											'255.192.0.0',
											'255.224.0.0',
											'255.240.0.0',
											'255.248.0.0',
											'255.252.0.0',
											'255.254.0.0',
											'255.255.0.0',
											'255.255.128.0',
											'255.255.192.0',
											'255.255.224.0',
											'255.255.240.0',
											'255.255.248.0',
											'255.255.252.0',
											'255.255.254.0',
											'255.255.255.0',
											'255.255.255.128',
											'255.255.255.192',
											'255.255.255.224',
											'255.255.255.240',
											'255.255.255.248',
											'255.255.255.252',
											'255.255.255.254',
											'255.255.255.255'
										);
										foreach ($subnetMasks as $mask):
											$selected = ($mask === '255.255.255.0') ? ' selected' : '';
											?>
											<option value="<?= htmlspecialchars($mask) ?>" <?= $selected ?>><?= htmlspecialchars($mask) ?></option>
										<?php endforeach; ?>
									</select>
								</td>
							</tr>
							<tr>
								<td><label for="maxSubnets"><?= gettext("Maximum Subnets") ?>:</label></td>
								<td>
									<select id="maxSubnets" class="form-control" aria-label="<?= gettext("Maximum number of subnets") ?>">
										<?php
										$powers = array(
											2,
											4,
											8,
											16,
											32,
											64,
											128,
											256,
											512,
											1024,
											2048,
											4096,
											8192,
											16384,
											32768,
											65536,
											131072,
											262144,
											524288,
											1048576,
											2097152,
											4194304,
											8388608,
											16777216,
											33554432,
											67108864,
											134217728,
											268435456,
											536870912,
											1073741824,
											2147483648,
											4294967296
										);
										foreach ($powers as $val):
											?>
											<option value="<?= htmlspecialchars($val) ?>"><?= htmlspecialchars($val) ?></option>
										<?php endforeach; ?>
									</select>
								</td>
							</tr>
							<tr>
								<td><label for="maxAddresses"><?= gettext("Maximum Addresses per Subnet") ?>:</label></td>
								<td>
									<select id="maxAddresses" class="form-control" aria-label="<?= gettext("Maximum addresses per subnet") ?>">
										<?php foreach ($powers as $val): ?>
											<option value="<?= htmlspecialchars($val) ?>"><?= htmlspecialchars($val) ?></option>
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
								<td style="width: 30%;"><strong><?= gettext("Network Address") ?>:</strong></td>
								<td>
									<span id="network" aria-live="polite"></span>
									<button type="button" class="btn btn-xs btn-default" onclick="copyToClipboard('network')" aria-label="<?= gettext("Copy network address to clipboard") ?>" title="<?= gettext("Copy to clipboard") ?>">
										<i class="fa fa-clipboard" aria-hidden="true"></i>
										<span class="sr-only"><?= gettext("Copy") ?></span>
									</button>
								</td>
							</tr>
							<tr>
								<td><strong><?= gettext("CIDR Route") ?>:</strong></td>
								<td>
									<span id="cidrRoute" aria-live="polite"></span>
									<button type="button" class="btn btn-xs btn-default" onclick="copyToClipboard('cidrRoute')" aria-label="<?= gettext("Copy CIDR route to clipboard") ?>" title="<?= gettext("Copy to clipboard") ?>">
										<i class="fa fa-clipboard" aria-hidden="true"></i>
										<span class="sr-only"><?= gettext("Copy") ?></span>
									</button>
								</td>
							</tr>
							<tr>
								<td><strong><?= gettext("Subnet Range") ?>:</strong></td>
								<td>
									<span id="subnetRange" aria-live="polite"></span>
									<button type="button" class="btn btn-xs btn-default" onclick="copyToClipboard('subnetRange')" aria-label="<?= gettext("Copy subnet range to clipboard") ?>" title="<?= gettext("Copy to clipboard") ?>">
										<i class="fa fa-clipboard" aria-hidden="true"></i>
										<span class="sr-only"><?= gettext("Copy") ?></span>
									</button>
								</td>
							</tr>
							<tr>
								<td><strong><?= gettext("Wildcard Mask") ?>:</strong></td>
								<td>
									<span id="wildcard" aria-live="polite"></span>
									<button type="button" class="btn btn-xs btn-default" onclick="copyToClipboard('wildcard')" aria-label="<?= gettext("Copy wildcard mask to clipboard") ?>" title="<?= gettext("Copy to clipboard") ?>">
										<i class="fa fa-clipboard" aria-hidden="true"></i>
										<span class="sr-only"><?= gettext("Copy") ?></span>
									</button>
								</td>
							</tr>
							<tr>
								<td><strong><?= gettext("Broadcast Address") ?>:</strong></td>
								<td>
									<span id="broadcast" aria-live="polite"></span>
									<button type="button" class="btn btn-xs btn-default" onclick="copyToClipboard('broadcast')" aria-label="<?= gettext("Copy broadcast address to clipboard") ?>" title="<?= gettext("Copy to clipboard") ?>">
										<i class="fa fa-clipboard" aria-hidden="true"></i>
										<span class="sr-only"><?= gettext("Copy") ?></span>
									</button>
								</td>
							</tr>
							<tr>
								<td><strong><?= gettext("Assignable Addresses") ?>:</strong></td>
								<td><span id="assignable" aria-live="polite"></span></td>
							</tr>
							<tr>
								<td><strong><?= gettext("Maximum Subnets") ?>:</strong></td>
								<td><span id="maxSubnetsResult" aria-live="polite"></span></td>
							</tr>
							<tr>
								<td><strong><?= gettext("Maximum Addresses") ?>:</strong></td>
								<td><span id="maxAddressesResult" aria-live="polite"></span></td>
							</tr>
						</tbody>
					</table>
				</div>

				<script type="text/javascript">
					//<![CDATA[
					// String.prototype.padStart polyfill for older browsers
					if (!String.prototype.padStart) {
						String.prototype.padStart = function (targetLength, padString) {
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

					// Load subnet masks from server-generated JSON to avoid duplication
					const SUBNET_MASKS = <?= cidr_calc_get_subnet_masks_json() ?>;

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

						const normalized = parts.map(function (part) {
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
						const item = SUBNET_MASKS.find(function (m) { return m.cidr === cidr; });
						return item ? item.mask : '255.255.255.0';
					}

					function getCidrFromSubnetMask(mask) {
						const item = SUBNET_MASKS.find(function (m) { return m.mask === mask; });
						return item ? item.cidr : 24;
					}

					function getWildcardMask(subnetMask) {
						return subnetMask.split('.').map(function (part) {
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
							assignableSpan, maxSubnetsResultSpan, maxAddressesResultSpan].forEach(function (el) {
								el.textContent = '—';
							});
					}

					function copyToClipboard(elementId) {
						const element = document.getElementById(elementId);
						const text = element.textContent || element.innerText;

						// Check if clipboard is available
						if (!navigator.clipboard && !document.queryCommandSupported('copy')) {
							showCopyFeedback(element, 'unavailable');
							return;
						}
						// Modern clipboard API
						if (navigator.clipboard && navigator.clipboard.writeText) {
							navigator.clipboard.writeText(text).then(function () {
								showCopyFeedback(element, 'success');
							}).catch(function (err) {
								console.error('Copy failed:', err);
								showCopyFeedback(element, 'error');
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
								const success = document.execCommand('copy');
								showCopyFeedback(element, success ? 'success' : 'error');
							} catch (err) {
								console.error('Copy fallback failed:', err);
								showCopyFeedback(element, 'error');
							}

							document.body.removeChild(textarea);
						}
					}

					function showCopyFeedback(element, type) {
						const feedback = document.createElement('span');
						feedback.className = type === 'error' ? 'label label-danger' : 'label label-success';
						feedback.textContent = type === 'error' ? '<?= gettext("Copy failed") ?>' : '<?= gettext("Copied!") ?>';
						feedback.style.marginLeft = '5px';
						feedback.setAttribute('role', 'status');
						feedback.setAttribute('aria-live', 'polite');
						element.parentNode.appendChild(feedback);

						setTimeout(function () {
							feedback.remove();
						}, 3000);
					}

					ipInput.addEventListener('blur', calculate);
					ipInput.addEventListener('keypress', function (e) {
						if (e.key === 'Enter' || e.keyCode === 13) calculate();
					});
					cidrSelect.addEventListener('change', calculate);
					subnetSelect.addEventListener('change', function () {
						cidrSelect.value = getCidrFromSubnetMask(this.value);
						calculate();
					});
					maxSubnetsSelect.addEventListener('change', function () {
						const cidr = Math.log2(parseInt(this.value, 10));
						if (cidr >= 1 && cidr <= 32 && Number.isInteger(cidr)) {
							cidrSelect.value = cidr;
							calculate();
						}
					});
					maxAddressesSelect.addEventListener('change', function () {
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
								<td style="width: 30%;"><label for="ipv6_addr"><?= gettext("IPv6 Address") ?>:</label></td>
								<td>
									<input type="text" id="ipv6_addr" class="form-control" placeholder="2001:db8::1" aria-label="<?= gettext("IPv6 Address") ?>" aria-describedby="ipv6-help">
									<small id="ipv6-help" class="help-block">
										<?= gettext("Enter IPv6 address in standard or compressed notation (e.g., 2001:db8::1)") ?>
									</small>
								</td>
							</tr>
							<tr>
								<td><label for="ipv6_mask"><?= gettext("Prefix Length") ?>:</label></td>
								<td>
									<select id="ipv6_mask" class="form-control" aria-label="<?= gettext("IPv6 prefix length") ?>" aria-describedby="ipv6-mask-help">
										<?php for ($i = 1; $i <= 128; $i++): ?>
											<option value="<?= htmlspecialchars($i) ?>" <?= ($i == 64 ? " selected" : "") ?>><?= htmlspecialchars($i) ?></option>
										<?php endfor; ?>
									</select>
									<small id="ipv6-mask-help" class="help-block">
										<?= gettext("Network prefix length (1-128). Common: /64 = subnet, /48 = site, /32 = ISP") ?>
									</small>
								</td>
							</tr>
						</tbody>
					</table>
				</div>

				<div class="table-responsive">
					<table class="table table-striped table-hover">
						<tbody>
							<tr>
								<td style="width: 30%;"><strong><?= gettext("Full Address") ?>:</strong></td>
								<td>
									<span id="ipv6_full" aria-live="polite"></span>
									<button type="button" class="btn btn-xs btn-default" onclick="copyToClipboardV6('ipv6_full')" aria-label="<?= gettext("Copy full IPv6 address to clipboard") ?>" title="<?= gettext("Copy to clipboard") ?>">
										<i class="fa fa-clipboard" aria-hidden="true"></i>
										<span class="sr-only"><?= gettext("Copy") ?></span>
									</button>
								</td>
							</tr>
							<tr>
								<td><strong><?= gettext("Network Address") ?>:</strong></td>
								<td>
									<span id="ipv6_network" aria-live="polite"></span>
									<button type="button" class="btn btn-xs btn-default" onclick="copyToClipboardV6('ipv6_network')" aria-label="<?= gettext("Copy IPv6 network address to clipboard") ?>" title="<?= gettext("Copy to clipboard") ?>">
										<i class="fa fa-clipboard" aria-hidden="true"></i>
										<span class="sr-only"><?= gettext("Copy") ?></span>
									</button>
								</td>
							</tr>
							<tr>
								<td><strong><?= gettext("Start Address") ?>:</strong></td>
								<td><span id="ipv6_start" aria-live="polite"></span></td>
							</tr>
							<tr>
								<td><strong><?= gettext("End Address") ?>:</strong></td>
								<td><span id="ipv6_end" aria-live="polite"></span></td>
							</tr>
							<tr>
								<td><strong><?= gettext("Total Addresses") ?>:</strong></td>
								<td><span id="ipv6_total" aria-live="polite"></span></td>
							</tr>
							<tr>
								<td><strong><?= gettext("/64 Subnets Available") ?>:</strong></td>
								<td><span id="ipv6_subnets" aria-live="polite"></span></td>
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
							return segs.map(function (x) { return x.padStart(4, '0'); }).join(":");
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
						const bits = hextets.map(function (h) { return parseInt(h, 16); });

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

						return networkBits.map(function (b) {
							return b.toString(16).padStart(4, '0');
						}).join(':');
					}

					function calculateIPv6EndAddress(networkAddr, prefixLen) {
						// Split into hextets
						const hextets = networkAddr.split(':');
						const bits = hextets.map(function (h) { return parseInt(h, 16); });

						// Calculate last address in network
						const lastBits = [];
						let bitsRemaining = prefixLen;

						for (let i = 0; i < 8; i++) {
							if (bitsRemaining >= 16) {
								// Keep network hextet
								lastBits.push(bits[i]);
								bitsRemaining -= 16;
							} else if (bitsRemaining > 0) {
								// Set host bits to all 1s
								const mask = (0xFFFF << (16 - bitsRemaining)) & 0xFFFF;
								lastBits.push((bits[i] & mask) | ((1 << (16 - bitsRemaining)) - 1));
								bitsRemaining = 0;
							} else {
								// All 1s for remaining hextets
								lastBits.push(0xFFFF);
							}
						}

						return lastBits.map(function (b) {
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
							document.getElementById('ipv6_total').textContent = '';
							document.getElementById('ipv6_subnets').textContent = '';
							return;
						}

						if (!validateIPv6(addr)) {
							document.getElementById('ipv6_full').textContent = '<?= gettext("Invalid IPv6 address") ?>';
							document.getElementById('ipv6_network').textContent = '';
							document.getElementById('ipv6_start').textContent = '';
							document.getElementById('ipv6_end').textContent = '';
							document.getElementById('ipv6_total').textContent = '';
							document.getElementById('ipv6_subnets').textContent = '';
							return;
						}

						const full = expandIPv6(addr);
						if (!full) {
							document.getElementById('ipv6_full').textContent = '<?= gettext("Error expanding address") ?>';
							return;
						}

						const networkAddr = calculateIPv6Network(full, mask);
						const endAddr = calculateIPv6EndAddress(networkAddr, mask);
						const subnets = mask < 64 ? Math.pow(2, 64 - mask).toLocaleString() : "1";

						// Calculate total addresses
						let totalAddresses;
						if (mask === 128) {
							totalAddresses = "1";
						} else {
							const hostBits = 128 - mask;
							if (hostBits <= 53) {
								// Can display as number
								totalAddresses = Math.pow(2, hostBits).toLocaleString();
							} else {
								// Too large, use scientific notation
								totalAddresses = "2^" + hostBits;
							}
						}

						document.getElementById('ipv6_full').textContent = full;
						document.getElementById('ipv6_network').textContent = networkAddr + '/' + mask;
						document.getElementById('ipv6_start').textContent = networkAddr;
						document.getElementById('ipv6_end').textContent = endAddr;
						document.getElementById('ipv6_total').textContent = totalAddresses;
						document.getElementById('ipv6_subnets').textContent = subnets;
					}

					function copyToClipboardV6(elementId) {
						const element = document.getElementById(elementId);
						const text = element.textContent || element.innerText;

						if (navigator.clipboard && navigator.clipboard.writeText) {
							navigator.clipboard.writeText(text).then(function () {
								showCopyFeedback(element, 'success');
							}).catch(function (err) {
								console.error('Copy failed:', err);
								showCopyFeedback(element, 'error');
							});
						} else {
							const textarea = document.createElement('textarea');
							textarea.value = text;
							textarea.style.position = 'fixed';
							textarea.style.opacity = '0';
							document.body.appendChild(textarea);
							textarea.select();

							try {
								const success = document.execCommand('copy');
								showCopyFeedback(element, success ? 'success' : 'error');
							} catch (err) {
								console.error('Copy fallback failed:', err);
								showCopyFeedback(element, 'error');
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