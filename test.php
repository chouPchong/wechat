<?php
$commands = ['cd /var/www/wechat', 'git pull'];
foreach ($commands as $command) {
    $output[] = shell_exec($command);
}
echo "<pre>$output</pre>";
