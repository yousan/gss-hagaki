# はじめに
gss-hagakiはGoogleスプレッドシートから宛名のPDFを作成します。

説明などは下記のページを参考にしてください。

[作った経緯について](https://2017.l2tp.org/archives/801)

[デモサイト（PosGo）](https://posgo.l2tp.org)

[PosGoとgss-hagakiの使い方について](https://2017.l2tp.org/archives/809)


# 使いかた

1. リポジトリをクローンします
1. composer update します
1. Googleスプレッドシートでデータを作成します
1. 共有されたURLに対してgss-hagakiを実行します

## リポジトリクローン

```
$ git clone https://github.com/yousan/gss-hagaki
```

## composer update します

```
$ composer update
```

## GoogleCloud でサービスアカウントを作成する

ローカル環境でテスト実行を行う方法です。サービスアカウントの作成、鍵の取得は一度行えば良いです。

1. GoogleCloudでサービスアカウントを作成する
2. サービスアカウントに対する鍵（secret.json）を作成する。
3. 鍵を設置する （gss-hagaki直下にsecret.json）
4. スプレッドシートに上記で作成したサービスユーザのメールアドレス（ID）に対して共有する。


## Googleスプレッドシートでデータを作成します

PosGoとgss-hagakiの使い方について https://2017.l2tp.org/archives/809
を参考にしてください。
スプレッドシートを作成しそのサービスアカウントに対して共有を行います。
例: `posgo-user@yousan.iam.gserviceaccount.com`
権限は閲覧者で大丈夫です。


## 共有されたURLに対してgss-hagakiを実行します

下記のようなコードを書いて実行します。
過去のバージョンではファイルに書き出していましたが、出力が標準出力に出るかもしれないので注意してください。


```
<?php
require_once(__DIR__.'/../vendor/autoload.php');

use GSSHagaki\GSSHagaki;

$url    = 'https://docs.google.com/spreadsheets/d/1yfMIdt8wgBPrMY3UwiCTsX3EN_2gcLCmPAEy8dfYeLY/export?usp=sharing&format=csv';
$hagaki = new GSSHagaki($url);
```


## ライセンスについて
gss-hagakiはGPLv3です。MigMixフォントについてはIPAフォントライセンスです。[^migmix]

[^migmix]: http://mix-mplus-ipa.osdn.jp/migmix/
