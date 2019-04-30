<?php

$result = shell_exec('git pull 2>&1', $output, $return_var);;

echo "<pre>$result</pre><hr>";
echo "<pre>$output</pre><hr>";
echo "<pre>$return_var</pre>";
