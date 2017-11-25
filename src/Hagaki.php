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

    const FONT = __DIR__ . '/../fonts/ipag.ttf';

    const BASEPDF = __DIR__ . '/../misc/hagaki.pdf';

    /**
     * @var TCPDF_FONTS $font
     */
    private $font;

    public function defineHagaki()
    {
        $this->pdf = new TcpdfFpdi('P', 'mm');
        // PDFの余白(上左右)を設定
        $this->pdf->SetMargins(0, 0, 0);
        // ヘッダーの出力を無効化
        $this->pdf->setPrintHeader(false);
        // フッターの出力を無効化
        $this->pdf->setPrintFooter(false);

        // 手動で追加する場合
        $this->font = new TCPDF_FONTS();
        var_dump(self::FONT);
        $fontfamily = $this->font->addTTFFont(self::FONT);

        $this->pdf->SetFont($fontfamily, '', 11);

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
    }

    public function zipcode($zipcode)
    {
        $this->pdf->Text(38, 12, $zipcode);
    }

    public function output($file)
    {
        //$fp = fopen($file, 'w');
        //fwrite($fp, $this->pdf->Output());
        //fclose($fp);
        $this->pdf->Output($file, 'F');
    }
}