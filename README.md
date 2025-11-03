# フリマアプリ"Coachtech"
    フリマアプリ「Coachtech」

## 作成した目的
    アイテムの出品と購入を行うためのフリマアプリを開発

## アプリケーションURL
    - 開発環境：http://localhost/
    - phpMyAdmin:http://localhost:8080/

## 機能一覧
    - ユーザー登録（メール認証付き）、ログイン、ログアウト機能
    - ユーザープロフィール登録と編集機能（プロフィール画像、ユーザー名、メールアドレス、住所）
    - 商品検索、商品詳細確認、お気に入り登録、解除、コメント登録機能
    - 商品購入機能（配送先変更、支払い方法選択、stripe決済機能）
    - 商品出品機能（商品画像、カテゴリ、商品の状態、商品名、ブランド名、商品の説明、販売価格）

## 使用技術（実行環境）
    - PHP 8.1
    - Laravel 8
    - MySQL 8.0.26

## テーブル設計

## ER図

## 環境構築

### Dockerビルド
    1. [git clone リンク]()
    2. docker-compose up -d --build

*MySQLは、OSによって起動しない場合があるのでそれぞれのPCに合わせてdocker-compose.ymlファイルを編集してください。

### Laravel環境構築
    1. docker-compose exec php bash
    2. composer install
    3. .env.exampleファイルから.envを作成し、環境変数を変更
        .envにSTRIPE_KEYを追加
        STRIPE_KEY=pk_test_xxxxxxxxxxxxxxx
        STRIPE_SECRET=sk_test_xxxxxxxxxxxxxxx
        MAIL_FROM_ADDRESS=flea_market@example.com
        MAIL_FROM_NAME="Coachtech Flea Market App"
	  4. php artisan key:generate
    5. php artisan migrate
    6. php artisan db:seed
    7. php artisan storage:link

### ダミーデータ説明
## ユーザー一覧
①鈴木太郎
Name: Taro Suzuki
Email: user1@gmail.com

②鈴木花子
Name: Hanako Suzuki
Email: user2@gmail.com

※パスワードは全て"password"

## 商品画像
商品画像はお手数ですがReleasesの「item_image」のZipファイルをダウンロードしていただき、Storageディレクトリ（src>storage>app>public>item_image）に保存をお願いいたします。

## Stripe決済
Stripe決済画面ではテスト用カード番号「4242 4242 4242 4242」をお使いください。

## mailhog
URL: http://localhost:8025
