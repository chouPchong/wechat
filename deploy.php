<?php

class Deployment
{
    private $token = 'A;LS=DK+JF(*YOH(*089+F23LKJ';

    public function deploy()
    {
        shell_exec('mkdir /var/www/1111');
        $signature = $_SERVER['HTTP_X_HUB_SIGNATURE'];
        $payload = file_get_contents('php://input');
        if ($this->isFromGithub($payload, $signature)) {
            $command = 'cd /var/www/wechat && git pull';
            shell_exec($command);
            http_response_code(200);
        } else {
            http_response_code(403);
        }
    }

    function isFromGithub($payload, $signature)
    {
        return 'sha1=' . hash_hmac('sha1', $payload, $this->token, false) === $signature;
    }
}

$deploy = new Deployment();
$deploy->deploy();
