<?php

class Deployment
{
    private $token = 'A;LS=DK+JF(*YOH(*089+F23LKJ';

    public function deploy()
    {
        $commands = ['cd /var/www/wechat', 'git pull'];
//        $headers = getallheaders();
//        $signature = $headers['X-Hub-Signature'];
//        $payload = file_get_contents('php://input');
//        if ($this->isFromGithub($payload, $signature)) {
//            foreach ($commands as $command) {
//                shell_exec($command);
//            }
//            http_response_code(200);
//        } else {
//            http_response_code(403);
//        }
        var_dump(getallheaders());
    }


    function isFromGithub($payload, $signature)
    {
        return 'sha1=' . hash_hmac('sha1', $payload, $this->token, false) === $signature;
    }
}

$deploy = new Deployment();
$deploy->deploy();
