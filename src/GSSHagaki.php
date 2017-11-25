<?php

namespace GSSHagaki;

require(__DIR__ . '/../vendor/autoload.php');

use GuzzleHttp\Client;
use setasign\Fpdi\TcpdfFpdi;
use TCPDF_FONTS;

class GSSHagaki
{

    /**
     * @var \SplFileObject $file
     */
    private $file = null;


    public function __construct($url)
    {
//        $client     = new Client();
//        $response   = $client->get($url);
//        $stream     = $response->getBody();
        //$this->file = new \SplFileObject('php://temp');
        $this->file = new \SplFileObject($url);
        $this->file->setFlags(\SplFileObject::READ_CSV);
        $hagaki = new Hagaki();
        $header = []; // カラム名が記載された見出し行
        $data = [];
        while ( !$this->file->eof() && $row = $this->file->fgetcsv()) {
            if ( !count($header) ) { // 見出し行が無かった場合
                $header = $row;
                continue;
            }
            if ( count( $row ) === 1 ||  // 空行だったりした場合
                 empty( $row[0] ) // 最初の列が空の行は省く
            ) {
                continue;
            }

            $data = array_combine( $header, $row ); // 見出し業
        }
        $hagaki->defineHagaki();
        $hagaki->zipcode($data[0]['zipcode']);
        $hagaki->output(__DIR__.'/hoge.pdf');
    }

}