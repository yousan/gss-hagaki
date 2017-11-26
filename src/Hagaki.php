<?php

namespace GSSHagaki;

use setasign\Fpdi\TcpdfFpdi;
use TCPDF_FONTS;

class Hagaki
{

    /**
     * @var TcpdfFpdi $pdf
     */
    private $pdf;

    /**
     * 連名が複数回出現すると横にズラして表記する必要があるため、出現回数を記録しておく。
     *
     * @var int $names_count
     */
    private $names_count = 0;

    const FONT = __DIR__ . '/../fonts/ipag.ttf';

    const BASEPDF = __DIR__ . '/../misc/hagaki.pdf';

    /**
     * @var TCPDF_FONTS $font
     */
    private $font;

    private $fontfamily;

    public function defineHagaki()
    {
        $this->pdf = new TcpdfFpdi('P', 'mm', [100, 148]);
        // PDFの余白(上左右)を設定
        $this->pdf->SetMargins(0, 0, 0, true);
        // ヘッダーの出力を無効化
        $this->pdf->setPrintHeader(false);
        // フッターの出力を無効化
        $this->pdf->setPrintFooter(false);

        // 手動で追加する場合
        $this->font       = new TCPDF_FONTS();
        $this->fontfamily = $this->font->addTTFFont(self::FONT);

        $this->pdf->SetFont($this->fontfamily, '', 11);

        // ページを追加
        $this->pdf->AddPage();
        // テンプレートを読み込み
        $this->pdf->setSourceFile(self::BASEPDF);
        $tplIdx = $this->pdf->importPage(1);
        // 読み込んだPDFの1ページ目をテンプレートとして使用
        $this->pdf->useTemplate($tplIdx, null, null, null, null, true);
        // 書き込む文字列の文字色を指定
        $this->pdf->SetTextColor(94, 61, 28);
        // デフォルト行間
        $default_cell_height_ratio = $this->pdf->getCellHeightRatio();

        // 自動改ページ @link http://www.t-net.ne.jp/~cyfis/tcpdf/tcpdf/SetAutoPageBreak.html
        $this->pdf->SetAutoPageBreak(false, 0);
    }


    /**
     * 名前を追記する
     *
     * @param $name
     * @param $suffix
     */
    public function name($name, $suffix)
    {
        $this->names_count++;
        $this->tate1(55, 32, $name . ' ' . $suffix, 38);
    }

    public function address($address_1, $address_2)
    {
        $this->tate1(85, 25, $address_1, 28);
        $this->tate1(75, 25, $address_2, 28);
    }

    /**
     * 郵便番号を設定する
     *
     * @param string $zipcode
     */
    public function zipcode($zipcode)
    {
        $this->pdf->SetFont($this->fontfamily, '', 20);
        $this->pdf->Text(45, 10, $zipcode[0]);
        $this->pdf->Text(52, 10, $zipcode[1]);
        $this->pdf->Text(59, 10, $zipcode[2]);
        $this->pdf->Text(67, 10, $zipcode[3]);
        $this->pdf->Text(74, 10, $zipcode[4]);
        $this->pdf->Text(81, 10, $zipcode[5]);
        $this->pdf->Text(88, 10, $zipcode[6]);
    }


    public function owner_zipcode($zipcode)
    {
        $this->pdf->SetFont($this->fontfamily, '', 12);
        $this->pdf->Text(3.75, 124.5, $zipcode[0]);
        $this->pdf->Text(7.75, 124.5, $zipcode[1]);
        $this->pdf->Text(11.75, 124.5, $zipcode[2]);
        $this->pdf->Text(17, 124.5, $zipcode[3]);
        $this->pdf->Text(21.25, 124.5, $zipcode[4]);
        $this->pdf->Text(25.5, 124.5, $zipcode[5]);
        $this->pdf->Text(29.75, 124.5, $zipcode[6]);
    }

    /**
     * @param $address_1
     * @param $address_2
     */
    public function owner_address($address_1, $address_2)
    {
        $fontsize = 11;
        $this->pdf->SetFont($this->fontfamily, '', $fontsize - 3);

        $this->tate1(29.75, 123, $address_1, $fontsize, true);
        $this->tate1(25.5, 123, $address_2, $fontsize, true);
    }

    public function owner_name($name_1, $name_2)
    {
        $fontsize = 11;
        $this->pdf->SetFont($this->fontfamily, '', $fontsize - 3);

        $this->tate1(24, 123, $name_1, $fontsize, true);

    }

    public function output($file)
    {
        //$fp = fopen($file, 'w');
        //fwrite($fp, $this->pdf->Output());
        //fclose($fp);
        $this->pdf->Output($file, 'F');
    }

    /**
     * TCPDFにカスタムサイズの定義。クラスがない…
     * @link https://stackoverflow.com/questions/3948818/tcpdf-custom-page-size
     */
    private function resizePDF()
    {
        $this->pdf = new CUSTOMPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
//Add a custom size
        $width       = 175;
        $height      = 266;
        $orientation = ($height > $width) ? 'P' : 'L';
        $this->pdf->addFormat("custom", $width, $height);
        $this->pdf->reFormat("custom", $orientation);
    }

    /**
     * 文字を縦書きに配置する関数
     * thanks! @link https://dbweb.0258.net/wiki.cgi?page=tcpdf%A4%C7%C6%FC%CB%DC%B8%EC%A4%CE%BD%C4%BD%F1%A4%AD
     *
     * @param $x
     * @param $y
     * @param $str
     * @param $size
     */
    private function tate2($x, $y, $str, $size)
    {
        // $this->pdf->SetFont('aoyagi-kouzan-font-gyousyo', '', $size);

        $fh = $this->pt2mm($size * 0.8); // 文字のサイズから算出されるオフセット

        $l = mb_strlen($str, 'UTF-8');

        $start = $y - $fh * $l;

        for ($i = 0; $i < $l; $i++) {
            $s1 = mb_substr($str, $i, 1, 'UTF-8');
            //print $s1."\n";
            $this->pdf->Text($x, $start + $fh * $i, $s1);
        }
    }

    /**
     * 文字を縦書きに配置する関数
     * thanks! @link https://dbweb.0258.net/wiki.cgi?page=tcpdf%A4%C7%C6%FC%CB%DC%B8%EC%A4%CE%BD%C4%BD%F1%A4%AD
     *
     * @param $x
     * @param $y
     * @param $str
     * @param $size
     * @param bool $sitatsuki 下付き文字（下段揃え）の文字列の場合。
     */
    private function tate1($x, $y, $str, $size, $sitatsuki = false)
    {
        // $this->pdf->SetFont('aoyagi-kouzan-font-gyousyo', '', $size);

        $fh = $this->pt2mm($size * 0.8); // 文字のサイズから算出されるオフセット
        $l  = mb_strlen($str, 'UTF-8');

        if ($sitatsuki) { // 下付きの場合には開始位置を事前に計算しておく
            $start = $y - $fh * $l;
        } else {
            $start = $y;
        }

        for ($i = 0; $i < $l; $i++) {
            $s1 = mb_substr($str, $i, 1, 'UTF-8');
            //print $s1."\n";
            $this->pdf->Text($x, $start + $fh * $i, $s1);
        }
    }

    /**
     *
     * 1 インチ = 25.4 ミリメートル
     * 1 ポイント = 1/72 インチ
     * 1mm　は、 25.4分の1インチ
     *
     * @param $mm
     *
     * @return float|int
     */
    private function mm2pt($mm)
    {
        // 1:25.4 = x: $mm
        // x = $mm / 25.4
        // $inch = $mm  / 25.4;
        // $pt = $inch / (1/72);
        // $pt = $inch * 72;
        $pt = $mm / 25.4 * 72;

        return $pt;
    }


    function pt2mm($pt)
    {
        // $pt = $mm / 25.4 * 72
        // $pt / 72  = $mm / 25.4
        // $pt / 72  * 25.4 = $mm
        // $mm = $pt / 72  * 25.4;
        $mm = $pt / 72 * 25.4;

        return $mm;
    }
}