<?php

namespace GSSHagaki;

require(__DIR__ . '/../vendor/autoload.php');

use GuzzleHttp\Client;
use setasign\Fpdi\TcpdfFpdi;
use TCPDF_FONTS;

class GSSHagaki
{

    /**
     * 読み込まれるCSVファイル。
     *
     * @var \SplFileObject $file
     */
    private $file = null;

    /**
     * はがきインスタンス
     *
     * @var Hagaki
     */
    private $hagaki;


    public function __construct($url)
    {
//        $client     = new Client();
//        $response   = $client->get($url);
//        $stream     = $response->getBody();
        //$this->file = new \SplFileObject('php://temp');
        $this->file = new \SplFileObject($url);
        $this->file->setFlags(\SplFileObject::READ_CSV);
        $this->hagaki = new Hagaki();
        $header       = []; // カラム名が記載された見出し行
        $datas        = [];

        // CSVデータを読み出す
        while ( ! $this->file->eof() && $row = $this->file->fgetcsv()) {
            if ( ! count($header)) { // 見出し行が無かった場合
                $header = $row;
                continue;
            }
            if (count($row) === 1 ||  // 空行だったりした場合
                empty($row[0]) // 最初の列が空の行は省く
            ) {
                continue;
            }
            $data    = array_combine($header, $row); // 見出し行を連想配列のキーに設定する
            $datas[] = $data;
        }

        $this->hagaki->defineHagaki();
//        var_dump($datas);
        foreach ($datas as $data) {
            $this->hagaki->addPage();
            $this->hagaki->zipcode($data['zipcode']);
            $this->hagaki->address($data['address_1'], $data['address_2']);
            $this->hagaki->name($data['name'], $data['suffix']);

            $this->hagaki->owner_zipcode($data['owner_zipcode']);
            $this->hagaki->owner_address($data['owner_address_1'], $data['owner_address_2']);
            $this->hagaki->owner_name($data['owner_name']);
//            $this->hagaki->owner_zipcode('9650015');
//            $this->hagaki->owner_address('富山県12高1岡22高333岡市放生津町二丁目2-2ー2ほげ',
//                '放生津町アパートメン津ABD棟202号室');
            // $this->hagaki->owner_name('電撃　太郎');
            $this->hagaki->addVersion();
        }
        $this->output();
    }

    /**
     * ブラウザに出力する。
     *
     * @link  https://qiita.com/horimislime/items/325848fcf1e3dc6bd53a
     * 下記のリンクは恐らく古い情報で、'O'は未サポート。 @see method of tcpdf
     * @link https://stackoverflow.com/questions/31198949/how-to-send-the-file-inline-to-the-browser-using-php-with-tcpdf
     */
    private function output()
    {
        // header("Content-type: application/pdf");
        header("Content-type: force-download");
        // $this->hagaki->Output('name.pdf', 'O');
        // If the above does not work, it is just better to use the php header() function.
        $this->hagaki->output('hagaki.pdf', 'D');
    }
}