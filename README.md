# Sugar CRM test rest API

### php poc

**You need an environment with php >= 7.3, composer and ext-json.**

I decide to use [doker container composer](https://hub.docker.com/_/composer).

- launch the container : `winpty docker run -v "your/local/path":/var/www/html --interactive --tty composer bash`.
- go to the mount point : `cd /var/www/html`
- make a vendor install : `composer install`
- run the poc in command line : `php php/poc.php`

If there is any trouble with OAouth2 token, just delete the file located here : `php/.token`, and run the poc again.

If you want to create a new Sugar CRM case, you have to uncomment some lines [in the poc file](https://github.com/Niafron/sugar-crm/blob/master/php/poc.php#L28).

### js poc
 
 **You just need a recent browser.**
 
If you still want to use an old fashioned browser, you have to make [some babelify build](https://github.com/Niafron/simple-site-minifier/blob/devel/gulpfile.js#L75).
 
 - open with your browser the file located at `js/poc.html`
 - and that's all
 
If there is any trouble with OAouth2 token, just click on the button for token deletion.
 
  