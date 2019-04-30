<?php

class Deployment
{
    private $token = 'A;LS=DK+JF(*YOH(*089+F23LKJ';

    public function deploy()
    {
        $commands = ['cd /var/www/wechat', 'git pull'];
        $signature = $_SERVER['HTTP_X_HUB_SIGNATURE'];
        $payload = file_get_contents('php://input');
        file_put_contents('test.txt', json_encode($_SERVER));
//        if ($this->isFromGithub($payload, $signature)) {
//            foreach ($commands as $command) {
//                shell_exec($command);
//            }
//            http_response_code(200);
//        } else {
//            http_response_code(403);
//        }
    }

    function isFromGithub($payload, $signature)
    {
        return 'sha1=' . hash_hmac('sha1', $payload, $this->token, false) === $signature;
    }
}

$deploy = new Deployment();
$deploy->deploy();
