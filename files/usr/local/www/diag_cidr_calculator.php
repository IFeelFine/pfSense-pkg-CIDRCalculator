<?php
/*
 * part of pfSense (https://www.pfsense.org)
 *--------------------------------------------------------------------------#
 *                                                                          #
 *       888888    888888              88     888888                        #
 *         88      88                  88     88   oo                       #
 *         88      88                  88     88                            #
 *         88      8888 .d8b.   .d8b.  88     8888 88 8888b.  .d8b.         #
 *         88      88  d8P Y8b d8P Y8b 88     88   88 88  8b d8P Y8b        #
 *         88      88  8888888 8888888 88     88   88 88  88 8888888        #
 *         88      88  Y8b.    Y8b.    88     88   88 88  88 Y8b.           #
 *       888888    88   ºY888P  ºY888P 88     88   88 88  88  ºY888P        #
 *                                                                          #
 *                                          (c) 2015-2025 I Feel Fine, Inc. #
 *--------------------------------------------------------------------------#
 * Licensed under the Apache License, Version 2.0 (the "License");          #
 * you may not use this file except in compliance with the License.         #
 * You may obtain a copy of the License at                                  #
 *                                                                          #
 *     http://www.apache.org/licenses/LICENSE-2.0                           #
 *                                                                          #
 * Unless required by applicable law or agreed to in writing, software      #
 * distributed under the License is distributed on an "AS IS" BASIS,        #
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. #
 * See the License for the specific language governing permissions and      #
 * limitations under the License.                                           #
 *--------------------------------------------------------------------------#
 * diag_cidr_calculator.php                                          #
 * Github Repo: <ifeelfine/pfSense-pkg-CIDRCalculator>                      #
 *--------------------------------------------------------------------------#
 * Description: IP CIDR calculator for pfSense in the Diagnostic menu.
 */
<?php
/*
 * cidr_calculator.widget.php
 * part of pfSense (https://www.pfsense.org)
 * Copyright (c) 2015-2025 I Feel Fine, Inc.
 * Licensed under the Apache License, Version 2.0
 */

$nocsrf = true;

require_once("guiconfig.inc");

// Widget metadata
$widgetTitle = gettext("CIDR Calculator");
$widgetTitleLink = "diagnostics_cidr_calculator.php";

// Get widget configuration with defaults
$show_ipv4 = $config['widgets']['cidr_calculator']['show_ipv4'] ?? 'true';
$show_ipv6 = $config['widgets']['cidr_calculator']['show_ipv6'] ?? 'true';

// Handle configuration save with validation
if ($_POST && isset($_POST['show_ipv4']) && isset($_POST['show_ipv6'])) {
    // Validate input - only allow 'true' or 'false' strings
    $new_ipv4 = ($_POST['show_ipv4'] === 'true') ? 'true' : 'false';
    $new_ipv6 = ($_POST['show_ipv6'] === 'true') ? 'true' : 'false';
    
    init_config_arr(array('widgets', 'cidr_calculator'));
    $config['widgets']['cidr_calculator']['show_ipv4'] = $new_ipv4;
    $config['widgets']['cidr_calculator']['show_ipv6'] = $new_ipv6;
    write_config(gettext("Updated CIDR Calculator widget settings"));
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
            <input type="checkbox" id="widget_show_ipv4" <?= $show_ipv4 == 'true' ? 'checked' : '' ?>> <?=gettext("IPv4")?>
          </label>
          <label class="checkbox-inline">
            <input type="checkbox" id="widget_show_ipv6" <?= $show_ipv6 == 'true' ? 'checked' : '' ?>> <?=gettext("IPv6")?>
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
          <td style="width: 30%;"><strong><?=gettext("IP/CIDR")?>:</strong></td>
          <td>
            <input type="text" id="widget_ipv4_input" class="form-control input-sm" placeholder="192.168.1.0/24" value="192.168.1.0/24" style="width: 100%;">
          </td>
        </tr>
        <tr id="widget_ipv4_results_row" style="display: none;">
          <td colspan="2">
            <div id="widget_ipv4_results" style="font-size: 12px;"></div>
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
          <td style="width: 30%;"><strong><?=gettext("IPv6 Address")?>:</strong></td>
          <td>
            <input type="text" id="widget_ipv6_addr" class="form-control input-sm" placeholder="2001:db8::" style="width: 100%;">
          </td>
        </tr>
        <tr>
          <td><strong><?=gettext("Prefix Length")?>:</strong></td>
          <td>
            <select id="widget_ipv6_mask" class="form-control input-sm" style="width: 100%;">
              <?php for ($i = 1; $i <= 128; $i++): ?>
              <option value="<?=$i?>" <?= $i == 64 ? "selected" : "" ?>><?=$i?></option>
              <?php endfor; ?>
            </select>
          </td>
        </tr>
        <tr id="widget_ipv6_results_row" style="display: none;">
          <td colspan="2">
            <div id="widget_ipv6_results" style="font-size: 12px;"></div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<script type="text/javascript">
//<![CDATA[

const SUBNET_MASKS_WIDGET = [
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
        return null;
    }
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
    
    if (!result) {
        resultsDiv.innerHTML = '<span style="color: #d9534f;">Invalid IP/CIDR format. Use: 192.168.1.0/24</span>';
        resultsRow.style.display = 'table-row';
        return;
    }
    
    resultsDiv.innerHTML = 
        '<strong>Network:</strong> ' + result.network + '/' + result.cidr + '<br>' +
        '<strong>Mask:</strong> ' + result.mask + '<br>' +
        '<strong>Range:</strong> ' + result.firstUsable + ' - ' + result.lastUsable + '<br>' +
        '<strong>Broadcast:</strong> ' + result.broadcast + '<br>' +
        '<strong>Usable Hosts:</strong> ' + result.usableHosts.toLocaleString();
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
    return ip.match(/^([0-9a-f]{1,4}:){2,7}[0-9a-f]{1,4}$/i) || ip.includes("::");
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
    
    if (!widget_validateIPv6(addr)) {
        resultsDiv.innerHTML = '<span style="color: #d9534f;">Invalid IPv6 address format</span>';
        resultsRow.style.display = 'table-row';
        return;
    }
    
    const full = widget_expandIPv6(addr);
    if (!full) {
        resultsDiv.innerHTML = '<span style="color: #d9534f;">Error expanding IPv6 address</span>';
        resultsRow.style.display = 'table-row';
        return;
    }
    
    const networkLen = Math.floor(mask / 4) * 5 + (mask % 4 > 0 ? (mask % 4 + 1) : 0);
    const network = full.substr(0, networkLen).padEnd(39, '0');
    const subnets = mask < 64 ? Math.pow(2, 64 - mask).toLocaleString() : "1";
    
    resultsDiv.innerHTML = 
        '<strong>Full:</strong> ' + full + '<br>' +
        '<strong>Network:</strong> ' + network + '<br>' +
        '<strong>/64 Subnets:</strong> ' + subnets;
    resultsRow.style.display = 'table-row';
}

document.getElementById('widget_show_ipv4').addEventListener('change', function() {
    document.getElementById('widget_ipv4_block').style.display = this.checked ? 'block' : 'none';
    $.post('/widgets/cidr_calculator.widget.php', {
        show_ipv4: this.checked ? 'true' : 'false',
        show_ipv6: document.getElementById('widget_show_ipv6').checked ? 'true' : 'false'
    });
});

document.getElementById('widget_show_ipv6').addEventListener('change', function() {
    document.getElementById('widget_ipv6_block').style.display = this.checked ? 'block' : 'none';
    $.post('/widgets/cidr_calculator.widget.php', {
        show_ipv4: document.getElementById('widget_show_ipv4').checked ? 'true' : 'false',
        show_ipv6: this.checked ? 'true' : 'false'
    });
});

document.getElementById('widget_ipv4_input').addEventListener('input', widget_updateIPv4);
document.getElementById('widget_ipv6_addr').addEventListener('input', widget_updateIPv6);
document.getElementById('widget_ipv6_mask').addEventListener('change', widget_updateIPv6);

widget_updateIPv4();

//]]>
</script>
