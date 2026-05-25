<?php
// NSCam - IP Geolocation & Scam Tracker
// Author - @dogesenic

$target = $_GET['target'] ?? '';
$data = null;
$error = null;

if ($target) {
    $url = "http://ip-api.com/json/" . urlencode($target) . "?fields=status,message,query,isp,org,as,country,country_code,region,region_name,city,zip,lat,lon,timezone,mobile,proxy,hosting";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    
    $result = json_decode($response, true);

    if ($result && $result['status'] === 'success') {
        $data = $result;
    } else {
        $error = $result['message'] ?? "Invalid IP or Domain";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nscam - IP Tracker</title>
    <style>
        body { background: #0d1117; color: #c9d1d9; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .search-box { margin-bottom: 30px; text-align: center; }
        input { padding: 10px; width: 60%; border: 1px solid #30363d; background: #0d1117; color: white; border-radius: 6px; }
        button { padding: 10px 20px; background: #238636; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; }
        button:hover { background: #2ea043; }
        
        .result-box { background: #161b22; border: 1px solid #30363d; border-radius: 6px; padding: 20px; <?= ($data) ? 'display:block' : 'display:none'; ?> }
        
        h3 { border-bottom: 1px solid #30363d; padding-bottom: 10px; margin-top: 30px; color: #58a6ff; }
        .row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #21262d; }
        .label { color: #8b949e; }
        .value { font-weight: bold; text-align: right; }
        
        .tag { padding: 2px 8px; border-radius: 12px; font-size: 12px; background: #30363d; }
        .tag.proxy { background: #f85149; color: white; }
        .tag.hosting { background: #d29922; color: black; }
        .tag.mobile { background: #a371f7; color: white; }
        .tag.res { background: #238636; color: white; }

        .map-link { color: #58a6ff; text-decoration: none; }
        .error { color: #f85149; text-align: center; background: #21262d; padding: 10px; border-radius: 6px; }
    </style>
</head>
<body>

<div class="container">
    <div style="text-align:center; margin-bottom: 30px;">
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
        <div class="error">Error: <?= $error ?></div>
    <?php endif; ?>

    <?php if ($data): ?>
    <div class="result-box">
        <h3>TARGET INFORMATION</h3>
        <div class="row"><span class="label">Target</span><span class="value"><?= $data['query'] ?></span></div>
        
        <?php 
        $type = "Residential IP";
        $class = "res";
        if(!empty($data['proxy'])) { $type = "Proxy / VPN"; $class = "proxy"; }
        elseif(!empty($data['mobile'])) { $type = "Mobile Data (4G/5G)"; $class = "mobile"; }
        elseif(!empty($data['hosting'])) { $type = "Hosting Server"; $class = "hosting"; }
        ?>
        <div class="row"><span class="label">Network Type</span><span class="value"><span class="tag <?= $class ?>"><?= $type ?></span></span></div>
        
        <div class="row"><span class="label">ISP</span><span class="value"><?= $data['isp'] ?? '-' ?></span></div>
        <div class="row"><span class="label">Organization</span><span class="value"><?= $data['org'] ?? '-' ?></span></div>
        <div class="row"><span class="label">AS Number</span><span class="value"><?= $data['as'] ?? '-' ?></span></div>

        <h3>GEOLOCATION</h3>
        <div class="row"><span class="label">Country</span><span class="value"><?= $data['country'] ?? '-' ?> (<?= $data['country_code'] ?? '-' ?>)</span></div>
        <div class="row"><span class="label">Region</span><span class="value"><?= $data['region_name'] ?? '-' ?></span></div>
        <div class="row"><span class="label">City</span><span class="value"><?= $data['city'] ?? '-' ?></span></div>
        <div class="row"><span class="label">ZIP</span><span class="value"><?= $data['zip'] ?? '-' ?></span></div>
        <div class="row"><span class="label">Timezone</span><span class="value"><?= $data['timezone'] ?? '-' ?></span></div>

        <?php if(!empty($data['lat']) && !empty($data['lon'])): ?>
        <h3>MAPS</h3>
        <div class="row"><span class="label">Coordinates</span><span class="value"><?= $data['lat'] ?>, <?= $data['lon'] ?></span></div>
        <div class="row"><span class="label">Google Maps</span><span class="value"><a class="map-link" href="https://www.google.com/maps/search/?api=1&query=<?= $data['lat'] ?>,<?= $data['lon'] ?>" target="_blank">Open Maps</a></span></div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

</body>
</html>
