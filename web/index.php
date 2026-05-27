<?php
// NSCam - IP Geolocation & Scam Tracker
// Author - @dogesenic

$target = $_GET['target'] ?? '';
$data = null;
$error = null;

function isPrivateOrReservedIP($ip) {
    $octets = explode('.', $ip);
    if (count($octets) !== 4) return false;
    
    $first = (int)$octets[0];
    $second = (int)$octets[1];
    $third = (int)$octets[2];

    // RFC 1918 & Reserves Check Logicc
    // 10.0.0.0/8
    if ($first === 10) return true;
    // 172.16.0.0/12
    if ($first === 172 && $second >= 16 && $second <= 31) return true;
    // 192.168.0.0/16
    if ($first === 192 && $second === 168) return true;
    // 127.0.0.0/8 (Loopback)
    if ($first === 127) return true;
    // 169.254.0.0/16 (Link-local)
    if ($first === 169 && $second === 254) return true;
    // 100.64.0.0/10 (CGN)
    if ($first === 100 && $second >= 64 && $second <= 127) return true;
    
    return false;
}

if ($target) {
    if (!preg_match('/^[a-zA-Z0-9\.\-]+$/', $target)) {
        $error = "Invalid characters detected.";
    } else {
        $resolvedIp = $target;
        $isDomain = false;

        if (filter_var($target, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
            if (strpos($target, '.') === false) {
                $error = "Invalid format. Please enter a valid IP or Domain.";
            } else {
                $resolvedIp = gethostbyname($target);
                $isDomain = true;
                if ($resolvedIp === $target) {
                    $error = "Could not resolve domain.";
                }
            }
        } else {
            $resolvedIp = $target;
        }

        if (!$error && isPrivateOrReservedIP($resolvedIp)) {
            $error = "Access Denied. Private/Internal IP addresses are not allowed.";
            $resolvedIp = null;
        }

        if (!$error && $resolvedIp) {
            $url = "http://ip-api.com/json/" . urlencode($resolvedIp) . "?fields=status,message,query,isp,org,as,country,country_code,region,region_name,city,zip,lat,lon,timezone,mobile,proxy,hosting";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if ($httpCode === 200) {
                $result = json_decode($response, true);
                if ($result && $result['status'] === 'success') {
                    $data = $result;
                } else {
                    $error = $result['message'] ?? "Invalid IP or Domain";
                }
            } else {
                $error = "API Service Unavailable";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nscam - IP Geolocation & Scam Tracker</title>
    <style>
        * { box-sizing: border-box; }
        body { 
            background: #2A2833; 
            color: #c9d1d9; 
            margin: 0; 
            padding: 20px; 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif; 
        }
        .container { max-width: 800px; margin: 0 auto; }
        
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; color: #A6A6A6; font-size: 2.5rem; }
        .header p { margin: 5px 0 0; color: #8b949e; }
        
        .search-box { margin-bottom: 30px; text-align: center; }
        .search-box form { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
        input { 
            padding: 12px 15px; 
            width: 60%; 
            max-width: 400px; 
            border: 1px solid #30363d; 
            background: #161b22; 
            color: white; 
            border-radius: 6px; 
            font-size: 16px; 
        }
        input:focus { outline: none; border-color: #4CAF50; }
        
        button { 
            padding: 12px 25px; 
            background: #238636; 
            color: #ffffff; 
            border: none; 
            border-radius: 6px; 
            cursor: pointer; 
            font-weight: bold; 
            font-size: 16px; 
        }
        button:hover { background: #2ea043; } 
        
        .result-box { background: #161b22; border: 1px solid #30363d; border-radius: 6px; padding: 20px; <?= ($data) ? 'display:block' : 'display:none'; ?> }
        
        h3 { border-bottom: 1px solid #30363d; padding-bottom: 10px; margin: 30px 0 15px; color: #58a6ff; font-size: 1.1rem; }
        h3:first-of-type { margin-top: 0; }
        
        .row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #21262d; }
        .row:last-child { border-bottom: none; }
        .label { color: #8b949e; }
        .value { font-weight: 600; text-align: right; word-break: break-word; max-width: 60%; }
        
        .tag { padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .tag.res { background: #238636; color: white; }
        .tag.proxy { background: #f85149; color: white; }
        .tag.mobile { background: #a371f7; color: white; }
        .tag.hosting { background: #d29922; color: black; }

        .map-container { margin-top: 20px; border-radius: 6px; overflow: hidden; border: 1px solid #30363d; }
        .map-frame { width: 100%; height: 400px; border: none; }
        .map-footer { background: #21262d; padding: 10px; display: flex; justify-content: space-between; align-items: center; }
        .coordinates { color: #8b949e; font-size: 14px; }
        .map-link { color: #58a6ff; text-decoration: none; font-size: 14px; }
        .map-link:hover { text-decoration: underline; }

        .error { color: #0d1117; text-align: center; background: #d29922; padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        
        .footer { text-align: center; margin-top: 30px; color: #8b949e; font-size: 14px; }
        .footer a { color: #58a6ff; text-decoration: none; }
        
        @media (max-width: 600px) {
            input { width: 100%; }
            .row { flex-direction: column; gap: 5px; }
            .value { text-align: left; max-width: 100%; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Nscam</h1>
        <p>IP Geolocation & Scam Tracker</p>
    </div>

    <div class="search-box">
        <form method="GET">
            <input type="text" name="target" placeholder="IP Address or Domain" value="<?= htmlspecialchars($target) ?>">
            <button type="submit">Trace</button>
        </form>
    </div>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($data): ?>
    <div class="result-box">
        <h3>TARGET INFORMATION</h3>
        <div class="row">
            <span class="label">Target</span>
            <span class="value"><?= htmlspecialchars($data['query']) ?></span>
        </div>
        
        <?php 
        $type = "Residential IP";
        $class = "res";
        if(!empty($data['proxy'])) { $type = "Proxy / VPN"; $class = "proxy"; }
        elseif(!empty($data['mobile'])) { $type = "Mobile Data"; $class = "mobile"; }
        elseif(!empty($data['hosting'])) { $type = "Hosting Server"; $class = "hosting"; }
        ?>
        <div class="row">
            <span class="label">Network Type</span>
            <span class="value"><span class="tag <?= $class ?>"><?= $type ?></span></span>
        </div>
        
        <div class="row">
            <span class="label">ISP</span>
            <span class="value"><?= htmlspecialchars($data['isp'] ?? '-') ?></span>
        </div>
        <div class="row">
            <span class="label">Organization</span>
            <span class="value"><?= htmlspecialchars($data['org'] ?? '-') ?></span>
        </div>
        <div class="row">
            <span class="label">AS Number</span>
            <span class="value"><?= htmlspecialchars($data['as'] ?? '-') ?></span>
        </div>

        <h3>GEOLOCATION</h3>
        <div class="row">
            <span class="label">Country</span>
            <span class="value"><?= htmlspecialchars($data['country'] ?? '-') ?> (<?= htmlspecialchars($data['country_code'] ?? '-') ?>)</span>
        </div>
        <div class="row">
            <span class="label">Region</span>
            <span class="value"><?= htmlspecialchars($data['region_name'] ?? '-') ?></span>
        </div>
        <div class="row">
            <span class="label">City</span>
            <span class="value"><?= htmlspecialchars($data['city'] ?? '-') ?></span>
        </div>
        <div class="row">
            <span class="label">ZIP Code</span>
            <span class="value"><?= htmlspecialchars($data['zip'] ?? '-') ?></span>
        </div>
        <div class="row">
            <span class="label">Timezone</span>
            <span class="value"><?= htmlspecialchars($data['timezone'] ?? '-') ?></span>
        </div>

        <?php if(!empty($data['lat']) && !empty($data['lon'])): ?>
        <h3>MAPS</h3>
        <div class="map-container">
            <iframe 
                class="map-frame"
                src="https://www.openstreetmap.org/export/embed.html?bbox=<?= $data['lon'] + 0.01 ?>%2C<?= $data['lat'] + 0.01 ?>%2C<?= $data['lon'] - 0.01 ?>%2C<?= $data['lat'] - 0.01 ?>&layer=mapnik&marker=<?= $data['lat'] ?>%2C<?= $data['lon'] ?>"
                loading="lazy">
            </iframe>
            <div class="map-footer">
                <span class="coordinates">📍 <?= htmlspecialchars($data['lat']) ?>, <?= htmlspecialchars($data['lon']) ?></span>
                <a class="map-link" href="https://www.google.com/maps/search/?api=1&query=<?= $data['lat'] ?>,<?= $data['lon'] ?>" target="_blank">Open in Google Maps →</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <div class="footer">
        <p>Author - <a href="https://github.com/dogesenic">@dogesenic</a></p>
    </div>
</div>

</body>
</html>
