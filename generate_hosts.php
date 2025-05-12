<?php
$projectRoot = '/var/www/projects';
$tailscaleIP = '100.118.23.113'; // Server1 Tailscale IP

$lines = [];
foreach (scandir($projectRoot) as $project) {
    if ($project[0] === '.' || !is_dir("$projectRoot/$project")) continue;
    $lines[] = "$tailscaleIP {$project}.dev.local";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>/etc/hosts Helper</title>
    <meta charset="UTF-8">
    <style>
        body { font-family: monospace; padding: 2rem; background: #f0f0f0; }
        pre { background: #fff; padding: 1rem; border-radius: 6px; box-shadow: 0 0 5px #ccc; }
        button { margin-top: 1rem; padding: 0.5rem 1rem; font-size: 1rem; }
    </style>
</head>
<body>
    <h1>Add these lines to <code>/etc/hosts</code> on your Mac</h1>
    <pre id="hostsBlock"><?php echo implode("\n", $lines); ?></pre>
    <button onclick="copy()">Copy to Clipboard</button>
    <script>
        function copy() {
            const text = document.getElementById('hostsBlock').innerText;
            navigator.clipboard.writeText(text).then(() => {
                alert('Copied! Paste this into your /etc/hosts file.');
            });
        }
    </script>
</body>
</html>
