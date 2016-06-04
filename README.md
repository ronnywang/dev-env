# dev-env

想要讓新手跳坑學程式，總覺得光是環境架設就是個門檻，但是如果不架好環境學習就難以開始。為了減少學習門檻讓更多人能參與寫程式，我決定開發一個在 web 上面就能夠做簡易開發的平台，讓新手更容易在學習初期做些簡單的程式嘗試，了解到程式弄做到什麼之後，就更有動力去把自己的環境架設起來。

啟動方式
========
* git submodule update --init --recursive
* git submodule foreach --recursive git pull origin master # 將 submodule 讀進來
* php worker.php &  # 跑起負責跑指令的 worker
* php -S 0:8080 index.php  # 將平台跑在 http://localhost:8080/

注意事項
========
這個測試環境會以執行的使用者身份執行，因此他可能會讓網站上使用者可透過此使用者身份取得該身份的私密資料，包括 ssh private key 等，若有 sudo 權限甚至可能會得到 root 權限，因此本環境請以較小權限使用者執行，或者在全新獨立環境執行，以避免讓本測試環境成為跳版。

也可以選擇在 Heroku 執行，目前有一版本在 http://php-dev-env.herokuapp.com/ ，為防止被濫用執行攻擊行為，這個 heroku 的展示版本不允許超過 3 秒鐘的執行動作，若有超過 3 秒的需求，請自行架設。
(用 heroku config:set TIME_LIMIT=3 可以指定不能執行超過 3 秒)

在 Heroku 執行的話，由於本專案產生的檔案都是存放在 /tmp ，而 Heroku 在 30 分鐘無人連線就會將資源釋出，因此原先產生的東西都會消失。不過這樣也正好，可以成為免洗的測試平台。

Heroku 安裝方式
===============
* 裝好 [Heroku Toolbelt](https://toolbelt.heroku.com/)
* 建立新的 Heroku App
* git remote add heroku https://git.heroku.com/APP_NAME.git
* heroku buildpacks:set heroku/php
* heroku buildpacks:add --index 2 heroku/python
* heroku buildpacks:add --index 3 heroku/nodejs
* heroku buildpacks:add --index 4 heroku/ruby
* git push heroku master
* 打開 https://APP\_NAME.herokuapp.com/ 看看是否有東西

程式碼授權方式
==============
* 本系統主程式 (PHP & Javascript) 使用 BSD Licnese
* PIXNET 的 pixframework  http://framework.pixnet.net/license/
* codemirror https://codemirror.net/LICENSE

