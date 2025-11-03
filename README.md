# "COACHTECH"
    フリマアプリ「COACHTECH」

## 作成した目的
    アイテムの出品と購入を行うための独自のフリマアプリを開発したい。
    他社サービスは機能や画面が複雑で使いづらいため。

## アプリケーションURL
    - 開発環境：http://localhost/
    - phpMyAdmin:http://localhost:8080/

## 機能一覧
    - ユーザー登録（メール認証付き）、ログイン、ログアウト機能
    - 商品一覧の確認、商品検索、いいね登録/解除、コメント機能
    - 商品購入機能（支払い方法選択、stripe決済機能、配送先住所変更機能付き）
    - ユーザープロフィール編集機能（プロフィール画像、ユーザー名、住所）
    - 商品出品機能

## 使用技術（実行環境）
    - PHP 8.1
    - Laravel 8
    - MySQL 8.0.26

## テーブル設計
<img width="956" height="1626" alt="image" src="https://github.com/user-attachments/assets/69ec9c8a-44f0-4e94-8944-c6422eb55f04" />

## ER図
<img width="1121" height="811" alt="er2" src="https://github.com/user-attachments/assets/8dddf936-6024-492e-8f0f-d60b47a3246a" />

## 環境構築

### Dockerビルド
    1. [git clone リンク](git@github.com:HarukoS/flea-market-app-new.git)
    2. docker-compose up -d --build
    *MySQLは、OSによって起動しない場合があるのでそれぞれのPCに合わせてdocker-compose.ymlファイルを編集してください。

### Laravel環境構築
    1. docker-compose exec php bash
    2. composer install
    3. .env.exampleファイルから.envを作成し、環境変数を変更
        .envにSTRIPE_KEYを追加
        STRIPE_KEY=pk_test_xxxxxxxxxxxxxxx
        STRIPE_SECRET=sk_test_xxxxxxxxxxxxxxx
        MAIL_FROM_NAME="Coachtech Flea Market App"
    4. php artisan key:generate
    5. php artisan migrate
    6. php artisan db:seed
    7. php artisan storage:link

### ダミーデータ説明
## ユーザー一覧
    1. 鈴木太郎
       Name: Taro Suzuki
       Email: user1@gmail.com
    2. 鈴木花子
       Name: Hanako Suzuki
       Email: user2@gmail.com
    ※パスワードは全て"password"

## 商品画像
    商品画像はお手数ですがReleasesの「item_image」のZipファイルをダウンロードしていただき、Storageディレクトリ（src>storage>app>public>item_image）に保存をお願いいたします。

## Stripe決済
    Stripe決済画面ではテスト用カード番号「4242 4242 4242 4242」をお使いください。

## mailhog
    URL: http://localhost:8025

### PHP Unitテスト環境構築
    1. MySQLコンテナ上でテスト用データベース作成
        $ mysql -u root -p
        > CREATE DATABASE demo_test;
        > SHOW DATABASES;
    2. .envファイルをコピーして.env.testingを作成し、環境変数を変更
        APP_ENV=test
        APP_KEY=
        DB_CONNECTION=mysql_test
        DB_DATABASE=demo_test
        DB_USERNAME=root
        DB_PASSWORD=root
    4. php artisan key:generate --env=testing
    5. php artisan config:clear
    6. php artisan migrate --env=testing
    7. php artisan test --testsuite=Feature
