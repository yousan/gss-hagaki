# はじめに
gss-hagakiはGoogleスプレッドシートから宛名のPDFを作成します。

説明などは下記のページを参考にしてください。

[作った経緯について](https://2017.l2tp.org/archives/801)
[デモサイト（UPG）](https://hagaki.l2tp.org)
[UPGとgss-hagakiの使い方について](https://2017.l2tp.org/archives/809)


# 使いかた

1. リポジトリをクローンします
1. composer installします
1. Googleスプレッドシートでデータを作成します
1. 共有されたURLに対してgss-hagakiを掛けます

## リポジトリをクローンします

```
$ git clone https://github.com/yousan/gss-hagaki
```

## composer installします

```
$ composer install
```

## Googleスプレッドシートでデータを作成します


UPGとgss-hagakiの使い方について https://2017.l2tp.org/archives/809
を参考にしてください。

## 共有されたURLに対してgss-hagakiを掛けます

下記のようなコードを書いて実行します。
過去のバージョンではファイルに書き出していましたが、出力が標準出力に出るかもしれないので注意してください。


```
<?php
/**
 * Just do it.
 */

require_once(__DIR__.'/../vendor/autoload.php');

use GSSHagaki\GSSHagaki;

$url    = 'https://docs.google.com/spreadsheets/d/1yfMIdt8wgBPrMY3UwiCTsX3EN_2gcLCmPAEy8dfYeLY/export?usp=sharing&format=csv';
$hagaki = new GSSHagaki($url);

```

## ライセンスについて
gss-hagakiはGPLv3です。MigMixフォントについてはIPAフォントライセンスと鳴ります。
