<?php
namespace GSSHagaki;

require(__DIR__.'/../vendor/autoload.php');

use GuzzleHttp\Client;

class GSSHagaki
{
	public function __construct($url)
	{
        $client = new Client();
        $response = $client->get($url);
        $stream = $response->getBody();
        var_dump($stream);
	}
}