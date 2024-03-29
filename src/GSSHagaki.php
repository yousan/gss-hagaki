<?php

namespace GSSHagaki;

require( __DIR__ . '/../vendor/autoload.php' );

use Exception;
use Google\Service\Sheets;
use Google\Service\Sheets\Sheet;

class GSSHagaki {

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

	/**
	 * オプション配列。
	 *
	 * @var array
	 */
	private $options = [];

	/**
	 * GSSHagaki constructor.
	 *
	 * @param $url
	 * @param array $options
	 */
	public function __construct( $url, $options = [] ) {
		$this->hagaki = new Hagaki();
		$this->options( $options );
		$datas = $this->readData( $url );
		$this->writeData( $datas );
		$this->output();
	}

	/**
	 * オプションを設定する。
	 *
	 * @param $options
	 */
	private function options( $options ) {
		$this->options = $options;
		if ( isset( $options['template'] ) && (boolean) $options['template'] ) { // はがきテンプレートを表示する
			$this->hagaki->use_template = true;
		}
		if ( isset( $options['to_zenkaku'] ) && (boolean) $options['to_zenkaku'] ) { // 半角数字を全角にする
			$this->options['to_zenkaku'] = true;
		} else {
			$this->options['to_zenkaku'] = false;
		}
		if ( isset( $options['credit'] ) && (boolean) $options['credit'] ) { // クレジット表記
			$this->options['credit'] = true;
		} else {
			$this->options['credit'] = false;
		}
		if ( isset( $options['debug'] ) && (boolean) $options['debug'] ) { // デバッグ
			$this->options['debug'] = true;
		} else {
			$this->options['debug'] = false;
		}
		if ( isset ( $options['zipcode_x'] ) ) { // 差出人 下限高さ
			$this->hagaki->zipcode_x = (float) $options['zipcode_x'];
		}
		if ( isset ( $options['zipcode_gap'] ) ) { // 差出人 下限高さ
			$this->hagaki->zipcode_gap = (float) $options['zipcode_gap'];
		}
		if ( isset ( $options['owner_shita_takasa'] ) ) { // 差出人 下限高さ
			$this->hagaki->owner_shita_takasa = (float) $options['owner_shita_takasa'];
		}
	}

	/**
	 * URLからFileIDを取得する。
	 *
	 * インプット例 https://docs.google.com/spreadsheets/d/1yfMIdt8wgBPrMY3UwiCTsX3EN_2gcLCmPAEy8dfYeLY/edit?usp=sharing
	 *
	 * @param $url
	 *
	 * @return mixed|string
	 */
	private function getFileIdByURL( $url ) {
		$urls  = parse_url( $url );
		$paths = explode( '/', $urls['path'] );

		return $paths[3];
	}

	/**
	 * 情報を読み出して配列で返す。
	 *
	 * @return array
	 * @throws Exception
	 */
	private function getData( string $url ) {
		// スプレッドシートIDとシート名の設定
		// $spreadsheetId = '1yfMIdt8wgBPrMY3UwiCTsX3EN_2gcLCmPAEy8dfYeLY';
		// $sheetName     = 'サンプル（編集不可）';
		$spreadsheetId = $this->getFileIdByURL( $url );

		// 認証とAPIクライアントの設定
		$client = new \Google\Client( [ 'credentials' => __DIR__ . '/../secret.json' ] );
		$client->useApplicationDefaultCredentials();
		$client->addScope( Sheets::SPREADSHEETS_READONLY );

		// スプレッドシートデータの取得
		$spreadsheet = ( new Sheets( $client ) )->spreadsheets->get( $spreadsheetId );
		$sheetData   = $spreadsheet->getSheets();

		/** @var Sheet $sheet */
		$sheet     = array_pop( $sheetData );
		$sheetName = $sheet->getProperties()->title;
		$data = ( new \Google\Service\Sheets( $client ) )->spreadsheets_values->get( $spreadsheetId, $sheetName );
		return $data->values;
	}

	/**
	 * @param $url
	 *
	 * @return array
	 * @throws Exception
	 */
	private function readData( $url ) {
		try {
			$rows = $this->getData( $url );
		}catch ( Exception $e ) {
			// エラー処理を入れていないので、下記のメッセージが直接表示されます。
			echo 'スプレッドシートデータの取得に失敗しました。共有設定がされていない可能性があります。<br>'.
			     '該当のスプレッドシートに<br>'.
			     '<pre> posgo-user@yousan.iam.gserviceaccount.com </pre><br>'.
			     'を共有してください。';
			exit;
		}
		$header = []; // カラム名が記載された見出し行
		$datas  = [];

		// CSVデータを読み出す
		foreach ( $rows as $key => $row ) {
			if ( ! count( $header ) ) { // 見出し行が無かった場合、読み込んでいる行を見出し行として処理する
				$header = $row;
				continue;
			}
			if ( count( $row ) === 1 ||  // 該当行が空行だったりした場合や
			     empty( $row[0] ) // 最初の列が空の行は省く
			) {
				continue;
			}
			// 見出し行を連想配列のキーに設定する
			// array_combineは２つのサイズが一緒じゃないと動作しないため、同じ長さにする @see https://stackoverflow.com/questions/4769213/combine-2-arrays-of-different-lengths
			// $data = array_combine(array_intersect_key($header, $row), array_intersect_key($row, $header));
			$count = max( count( $header ), count( $row ) );
			$data  = array_combine( $header, array_pad( $row, $count, null ) );
			// var_dump($data);
			$datas[] = $data;
		}

		return $datas;
	}

	/**
	 * データを書き込む。
	 *
	 * @param $datas
	 *
	 * @return mixed
	 */
	private function writeData( $datas ) {
		$this->hagaki->defineHagaki();
		foreach ( $datas as $data ) {
			if ( empty( $data['zipcode'] ) && $data['address_1'] ) {
				// 郵便番号と住所1がない場合にはスキップする
				continue;
			}
			$this->hagaki->addPage();
			$this->hagaki->zipcode( $data['zipcode'] );
			$this->hagaki->address(
				$this->to_zenkaku( $data['address_1'] ),
				$this->to_zenkaku( $data['address_2'] )
			);


			$names = [];
			for ( $i = 0; $i < 4; $i ++ ) {
				$names[ $i ]['first_name'] = $data[ 'first_name_' . ( $i + 1 ) ] ?? '';
				$names[ $i ]['suffix']     = $data[ 'suffix_' . ( $i + 1 ) ] ?? '';
			}
			$this->hagaki->names( $data['family_name'], $names );

			$this->hagaki->owner_zipcode( $data['owner_zipcode'] );
			$this->hagaki->owner_address(
				$this->to_zenkaku( $data['owner_address_1'] ),
				$this->to_zenkaku( $data['owner_address_2'] )
			);
			$this->hagaki->owner_name( $data['owner_name'] );

			if ( isset( $this->options['debug'] ) && $this->options['debug'] ) {
				$this->hagaki->addVersion();
			}
			if ( isset( $this->options['credit'] ) && $this->options['credit'] ) {
				$this->hagaki->credit();
			}
		}

		return $datas;
	}

	/**
	 * 半角数字を全角にする
	 *
	 * @param string $str
	 */
	private function to_zenkaku( $str ) {
		if ( $str ) {
			return str_replace( [ 0, 1, 2, 3, 4, 5, 6, 7, 8, 9 ],
				[ '〇', '一', '二', '三', '四', '五', '六', '七', '八', '九' ],
				$str
			);
		} else {
			return '';
		}
	}

	/**
	 * ブラウザに出力する。
	 *
	 * @link  https://qiita.com/horimislime/items/325848fcf1e3dc6bd53a
	 * 下記のリンクは恐らく古い情報で、'O'は未サポート。 @see method of tcpdf
	 * @link https://stackoverflow.com/questions/31198949/how-to-send-the-file-inline-to-the-browser-using-php-with-tcpdf
	 */
	private function output() {
		// header("Content-type: application/pdf");
		header( "Content-type: force-download" );
		// $this->hagaki->Output('name.pdf', 'O');
		// If the above does not work, it is just better to use the php header() function.
		$this->hagaki->output( 'hagaki.pdf', 'D' );
	}
}
