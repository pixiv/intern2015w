# 用語

## slug

ログインおよびURLの一部として利用される識別子。

たとえばユーザーは`/@root`のようなURLを持つ。

# 技術メモ

## PHP

PHP 7.0を利用。

## ルーティング

[BaguettePHP/simple-routing](https://github.com/BaguettePHP/simple-routing)を利用。設計思想は[PHP - シンプルなルーティングがしたかった - Qiita](http://qiita.com/tadsan/items/bcaa14504d0ecdd9e096)にある。

## フレームワーク

[BaguettePHP/baguette](https://github.com/BaguettePHP/baguette)を利用。

## テンプレートエンジン

[Twig](http://twig.sensiolabs.org/)を利用。

## Markdown

[Parsedown](http://parsedown.org/)を利用。

## SQL

SQLite3を利用。もし自分でクエリを入力してDBを確認するには`sqlite3`パッケージを導入する。

```sh
sudo apt install sqlite3
cd /vagrant/
sqlite3 ./cache/db.sq3
```
