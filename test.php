<?php
$commands = ['cd /var/www/wechat', 'sudo git pull'];
foreach ($commands as $command) {
    $output[] = shell_exec($command);
}
echo "<pre>$output</pre>";
