<?php

namespace GSSHagaki;

use DateTime;
use setasign\Fpdi\TcpdfFpdi;
use TCPDF_FONTS;

class Hagaki
{
    const FONT = __DIR__ . '/../fonts/migmix-2p-regular.ttf';

    const BASEPDF = __DIR__ . '/../misc/hagaki.pdf';

    /**
     * 横書きモードの際のマージン(mm)
     */
    const Y_MARGIN = 0.05;

    /**
     * テンプレートを使用するか否か。
     *
     * @var bool
     */
    public $use_template = false;

    /**
     * デバッグモード。
     *
     * @var bool
     */
    public $debug = false;

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

    /**
     * @var TCPDF_FONTS $font
     */
    private $font;


    /**
     * フォントファミリー名
     *
     * @var string
     */
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

        // 書き込む文字列の文字色を指定
        $this->pdf->SetTextColor(94, 61, 28);
        // デフォルト行間
        $default_cell_height_ratio = $this->pdf->getCellHeightRatio();

        // 自動改ページ @link http://www.t-net.ne.jp/~cyfis/tcpdf/tcpdf/SetAutoPageBreak.html
        $this->pdf->SetAutoPageBreak(false, 0);

        mb_internal_encoding('UTF-8');
    }

    /**
     * 改ページ。
     */
    public function addPage() {
        // ページを追加
        $this->pdf->AddPage();
        if ( (boolean)$this->use_template ) {
            // テンプレートを読み込み
            $this->pdf->setSourceFile(self::BASEPDF);
            $tplIdx = $this->pdf->importPage(1);
            // 読み込んだPDFの1ページ目をテンプレートとして使用
            $this->pdf->useTemplate($tplIdx, null, null, null, null, true);
        }
    }

    /**
     * 名前を追記する。
     *
     * @param $name
     * @param $suffix
     */
    public function name($name, $suffix)
    {
        $this->names_count++;
        $this->tate1(55, 32, $name . ' ' . $suffix, 20);
    }

    /**
     * 宛先の住所を入れる。
     * 住所１、住所２で改行する。
     *
     * @param $address_1
     * @param $address_2
     */
    public function address($address_1, $address_2)
    {
        if ( $this->mb_tate_strlen($address_1) < 14 ) {
            $this->tate1(85, 25, $address_1, 19);
        } else { // 13文字以上ははみ出す可能性が高いのでフォントを小さくする
            $this->tate1(85, 25, $address_1, 13);
        }
        if ( $this->mb_tate_strlen($address_2) < 14 ) {
            $this->tate1(75, 25, $address_2, 19);
        } else {
            $this->tate1(75, 25, $address_2, 13);
        }
    }

    /**
     * 郵便番号を設定する。
     *
     * @param string $zipcode
     */
    public function zipcode($zipcode)
    {
        $this->pdf->SetFont($this->fontfamily, '', 19);
        $this->pdf->Text(45, 10, $zipcode[0]);
        $this->pdf->Text(52, 10, $zipcode[1]);
        $this->pdf->Text(59, 10, $zipcode[2]);
        $this->pdf->Text(67, 10, $zipcode[3]);
        $this->pdf->Text(74, 10, $zipcode[4]);
        $this->pdf->Text(81, 10, $zipcode[5]);
        $this->pdf->Text(88, 10, $zipcode[6]);
    }

    /**
     * 差出人の郵便番号を設定する。
     *
     * @param $zipcode
     */
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
     * 差出人の住所を設定する。
     *
     * @param $address_1
     * @param $address_2
     */
    public function owner_address($address_1, $address_2)
    {
        $fontsize = 8;
        // $this->pdf->SetFont($this->fontfamily, '', $fontsize - 3);

        $this->tate1(29.75, 120, $address_1, $fontsize, true);
        $this->tate1(25.5, 120, $address_2, $fontsize, true);
    }

    /**
     * 差出人名を設定する。
     *
     * @param $name_1
     * @param string $name_2
     */
    public function owner_name($name_1, $name_2 = '')
    {
        $fontsize = 20;
        // $this->pdf->SetFont($this->fontfamily, '', $fontsize);

        $this->tate1(14, 121, $name_1, $fontsize, true);
    }

    /**
     * ファイルの書き出し。
     *
     * @param $file
     * @param $mode
     */
    public function output($file, $mode)
    {
        //$fp = fopen($file, 'w');
        //fwrite($fp, $this->pdf->Output());
        //fclose($fp);
        $this->pdf->Output($file, $mode);
    }

    /**
     * デバッグ用に現在の日付を入れる。
     */
    public function addVersion() {
        $this->pdf->SetFont($this->fontfamily, '', 6);
        $this->pdf->Text(3.75, 138.5, (new DateTime())->format('Y-m-d H:i:s'));
    }

    /**
     * 文字を縦書きに配置する関数
     * thanks! @link https://dbweb.0258.net/wiki.cgi?page=tcpdf%A4%C7%C6%FC%CB%DC%B8%EC%A4%CE%BD%C4%BD%F1%A4%AD
     *
     * @param $x
     * @param $base_y
     * @param $str
     * @param int $size
     * @param bool $sitatsuki 下付き文字（下段揃え）の文字列の場合。
     * @param float $height_ratio ここで指定されたサイズを基に縦書きの字間を計算する
     *
     * @internal param $y
     */
    private function tate1($x, $base_y, $str, $size, $sitatsuki = false, $height_ratio = 1.0)
    {
        $this->pdf->SetFont($this->fontfamily, '', $size);
        $fh = $this->pt2mm($size * $height_ratio); // 文字のサイズから算出される1文字の大きさ(高さ)
        $str = $this->hyphenation($str); // ハイフンを縦棒に

        $l = $this->mb_tate_strlen($str);
         if ($sitatsuki) { // 下付きの場合
            // 下付き（下段揃え）の場合には開始位置を事前に計算しておく。
             $l = $this->mb_tate_strlen($str);
            $y = $base_y - ( $fh * $l );
        } else {
            $y = $base_y;
        }

        $hankaku_str = '';
        for ($i = 0; $i < mb_strlen($str); $i++) { // 各文字でループ
            $c = mb_substr($str, $i, 1, 'UTF-8'); // 一文字だけ取り出す
            if ( $this->isHankaku($c) ) { // 半角文字列が来た場合ストックする
                $hankaku_str .= $c;
            } else { // 全角文字だった場合
                if ( !empty($hankaku_str) ) { // 全角文字が出るまでに半角文字がストックされていた場合、放出する
                    $this->hankakuYoko($x, $y, $size, $hankaku_str);
                    $hankaku_str = ''; // ストックをゼロに
                    $y += $fh; // 高さを一文字分だけ進める
                }
                $this->pdf->Text($x, $y, $c);
                $y += $fh; // 高さを一文字分だけ進める
            }
        }
        // ループが終わりきって半角がストックされていた場合、最後の出力を行う。
        if ( !empty($hankaku_str) ) {
            $this->hankakuYoko($x, $y, $size, $hankaku_str);
            $hankaku_str = ''; // ストックをゼロに
            $y += $fh; // 高さを一文字分だけ進める
        }
    }

    /**
     * 縦書きにした時の文字列長を計算する。
     * ポイントとしては、連続する半角文字については横書きになるので、１文字として計算する。
     * e.g. 'あいうABCえおCDほげ' => 9
     * e.g. 'あいうえおほげ' => 7
     *
     * @param $str
     *
     * @return int
     */
    private function mb_tate_strlen($str) {
        // e.g. 'あいうABCえおCDほげ' の場合、['ABC', 'CD']がそれぞれ1文字になるので、
        // 全文字列長( mb_strlen('あいうABCえおCDほげ') )から
        // -2 ( ABC.length() - 1), -1 (CD.length() -1 )) = -3 文字をオフセットで引きたい
        $length = mb_strlen($str);
        if (preg_match_all('/(?<hankaku>[A-z0-9\-]+)/', $str, $matches)){
            foreach ($matches['hankaku'] as $key => $value ){
                // 1文字だけの半角文字列だった場合には引かない。
                if (strlen($value) > 1 ) { // 2文字以上の連続する長さnの半角文字列だった場合、n-1分だけ引く。
                    $length -= strlen($value) - 1;
                }
            }
        }
        return $length;
    }

    /**
     * ハイフンを統一する。
     * ハイフンは日本の住所でよく使われている。
     * 縦書きの際にハイフンは縦書きにする必要があるので、ハイフンを全て全角縦棒「｜」に統一する。
     *
     * @param $str
     *
     * @return string
     */
    private function hyphenation($str) {
        $str = str_replace('ー', '｜', $str);
        $str = str_replace('−', '｜', $str);
        $str = str_replace('-', '｜', $str);
        return $str;
    }

    /**
     * 半角で横書きにする。
     * 半角文字の長さによって、全角文字の左上の位置から、x軸正の方向（右方向）にズラす幅
     * 1文字の場合 => +0.25em
     * 2文字の場合 =>  0em
     * 3文字の場合 => -0.25em
     * 4文字の場合 => -0.5em
     *
     * @param $x
     * @param $y
     * @param $size
     * @param $str
     */
    private function hankakuYoko($x, $y, $size, $str) {
        $length = mb_strlen($str); // 文字列長
        // 文字のサイズから算出される半角0.25em文字の大きさ(幅)
        // 元の$sizeは全角をベースとしているので、その半分を基準にする
        $fontWidth =  $this->pt2mm( $size );
        $x_offset = (0.5 - ($length * 0.25)) * $fontWidth; // 左にずらす大きさ(em)
        $this->pdf->Text($x + $x_offset, $y, $str);
    }

    /**
     * 半角、全角を判定する
     * @link https://singoro.net/note/count-utf8/
     *
     * @param string $c 文字
     *
     * @return bool
     */
    private function isHankaku( $c ) {
        if ( ( mb_strwidth(trim($c), 'UTF-8') / 2 ) === 0.5 ) {
            return true;
        } else {
            return false;
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