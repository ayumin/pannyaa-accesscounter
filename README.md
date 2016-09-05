# Dropbox Access Counter

[Original code](https://gist.github.com/PanNyaa/3652eafcc392199b2d5f) has writen by [PanNyaa](https://github.com/PanNyaa)

## Setup

Follow these commands to deploy to Heroku.

````
$ git clone https://github.com/ayumin/pannyaa-accesscounter.git
$ cd pannyaa-accesscounter
$ heroku create
$ heroku config:set DROPBOX_ACCESS_TOKEN={Your Dropbox Access Token}
$ git push heroku master
$ heroku open
````

## Generate Dropbox Access Token
see [here](https://blogs.dropbox.com/developers/2014/05/generate-an-access-token-for-your-own-account/).

Enjoy :)
