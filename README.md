# pixiv-winter-internship 2015課題

ピクシブの冬インターンシップでは二日間の短期メニューでpixivのバグフィックスおよび簡単な機能改修を体験してもらいます。

そのための課題として、PHPで書かれたコードへの改善を行ってください。

## 課題

あなたは**次世代インタラクティブコミュニケーションサービス**の開発チームにアサインされました。

チームメンバーは基本機能の実装が完了してリリース日を待つばかりだと浮かれ気分ですが、あなたはリリース直前にも関わらず多数の問題を発見してしまいました。

無事にサービスをリリースできるように、気がついた脆弱性について対処を行ってください。

### 任意課題

追加で以下のような改善を行っても構いません。

 * 性能改善
 * リファクタリング
 * UI改善

時間は有限です。気になるところを最小限直すだけでも構いません。最低限の工夫でよりよくするのも大切な観点です。

## 提出方法

このリポジトリをGitHubでforkし、Pull Requestの作成をもって提出とします。ただし、第三者が見てわかりやすい単位での`git commit`を心掛けてください。

* 提出後にpush/rebase/force-pushを行っても問題ありません
* どのような変更をしたかプルリクエストに記述してください
  * どうしてそういう変更をしたのか箇条書きなどで書いてください
  * 分かりやすければ形式は問いません

提出後、GitHubのメールアドレス宛に確認メールを送りますので、確認をお願いいたします。

## 開発環境について

Debian GNU/Linux + PHP 7の環境を利用してください。

[Vagrant](https://www.vagrantup.com/)で[rlerdorf/php7dev](https://github.com/rlerdorf/php7dev)のBoxを利用することがおすすめです。

 * Vagrantの動作には[VirtualBox](https://www.virtualbox.org/)が必要です
 * 一部のWindows機ではBIOSの設定により仮想環境が動作しない場合があるので確認してください

環境構築については[サポートチャット](https://idobata.io/organizations/pixiv/rooms/intern2015w_guest/join_request/fa64b276-3e72-4fe1-881f-b188091d3699)で支援しますので、わからないことは気軽に質問してください。

## セットアップ

```sh
git clone # リポジトリのURL
cd wintern
vagrant up # 初回はBoxのダウンロードが必要なのでインターネット接続が必要なのと時間がかかります
vagrant ssh # sshで仮想マシンの中に入れます

cd /vagrant/
composer install
php ./setup

# サーバー起動
php -S 0.0.0.0:3939 ./htdocs/index.php
```

ブラウザで [http://localhost:3939/](http://localhost:3939/) を開くと動作確認ができます。

## 質問チャットルーム

技術的な質問については idobata.io のチャットルームにてサポートいたします。

https://idobata.io/organizations/pixiv/rooms/intern2015w_guest/join_request/fa64b276-3e72-4fe1-881f-b188091d3699

 * 基本的に回答は営業時間内（平日10:00-19:00）のみ行います
 * 回答はピクシブ株式会社のエンジニアが行います
 * トラブルが起これば閉じる可能性があります
 * 選考に関する質問には回答できません

以上の点をあらかじめご了承ください。

## 注意

* ソースコードはライセンスの範囲内で利用可能ですが、**セキュリティホールが存在する**ため公衆から利用可能なサーバーに設置することは推奨しません

## Copyright

    Copyright (C) 2015 pixiv inc.

### [wall.jpg](https://www.flickr.com/photos/missbutterfly/20630854981/in/photolist-xr5wqe-8gYDbE-53Vts2-bv7mcy-pypa8W-cj1FNE-oPCf9i-nDHKRJ-eZTdu6-fRNYJt-rBbwpA-5xQJag-foM8Lk-zFKCcs-5eMsfq-nrAyGX-ncdJvQ-amLm3g-aVhz5n-98wgNj-8suDTx-qFKXCX-8pFYik-6YgxJ6-o76w6Q-nK7dKV-4PhUdE-fxzBSk-dN895J-5NBj93-2H4Hwi-4fj2Sc-741VDU-9H6FrD-cYcrDG-btDqqB-snfcc9-9Nke5x-aq6YDK-9LzoPF-adBqvw-5NBj9K-e4MNz2-NMuah-8ACb9x-7Cyxxf-6QKN8G-c8D39m-sDtsoH-ajBPqC)

by [Julie Missbutterflies](https://www.flickr.com/photos/missbutterfly/) - [CC BY-SA 2.0](https://creativecommons.org/licenses/by-sa/2.0/)
