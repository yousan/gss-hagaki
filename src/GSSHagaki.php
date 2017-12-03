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
        $hagaki->zipcode($data['zipcode']);
        $hagaki->address($data['address_1'], $data['address_2']);
        $hagaki->name($data['name'], $data['suffix']);


        $hagaki->owner_zipcode('9650015');
        $hagaki->owner_address('富山県12高1岡22高333岡市放生津町二丁目2-2-2ほげ', '放生津町アパートメン津ABD棟202号室');
        // $hagaki->owner_name('電撃　太郎');
        $hagaki->output(__DIR__.'/hoge.pdf');
    }

}