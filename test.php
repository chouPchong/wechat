<?php
$commands = ['cd /var/www/wechat', 'sudo -Hu git git pull'];
foreach ($commands as $command) {
    $output[] = shell_exec($command);
}
echo "<pre>$output</pre>";
