<h1>Webservers</h1>
<p>The following webservers have been tested:</p>
<h2>Apache + mod_php</h2>
<pre>
&lt;VirtualHost *:80&gt;
        ServerAdmin webmaster@localhost
        ServerName mindaphp.dev

        DocumentRoot /home/maurits/public_html/mindaphp/web
        &lt;Directory /home/maurits/public_html/mindaphp/web&gt;
                AllowOverride All
        &lt;/Directory&gt;

        ErrorLog ${APACHE_LOG_DIR}/error.log

        # Possible values include: debug, info, notice, warn, error, crit,
        # alert, emerg.
        LogLevel warn

        CustomLog ${APACHE_LOG_DIR}/access.log combined

&lt;/VirtualHost&gt;
</pre>
<p>Benchmarks:</p>
<pre>
ab -n 2000 -c 10 http://mindaphp.dev/hello/world
Requests per second:    861.30 [#/sec] (mean)
</pre>
<h2>Nginx + HHVM (Linux)</h2>
<p>Install HHVM:</p>
<pre>
sudo add-apt-repository ppa:mapnik/boost
wget -O - http://dl.hhvm.com/conf/hhvm.gpg.key | sudo apt-key add -
echo deb http://dl.hhvm.com/ubuntu precise main | sudo tee /etc/apt/sources.list.d/hhvm.list
sudo apt-get update
sudo apt-get install hhvm
</pre>
<p>from: https://github.com/facebook/hhvm/wiki/Prebuilt-packages-on-ubuntu-12.04</p>
<p>Configure Nginx:</p>
<pre>
$ cat /etc/nginx/sites-enabled/mindaphp 
server {
    listen 80;
    server_name mindaphp.dev;
    
    root /home/maurits/public_html/mindaphp/web;
    try_files $uri @proxy;

    location ~ \.php$ {
        return 403;
    }

    location ~ ^/debugger/$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $document_root/debugger/index.php;
        include fastcgi_params;
    }

    location @proxy {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $document_root/index.php;
        include fastcgi_params;
    }

}
</pre>
<p>Benchmarks:</p>
<pre>
ab -n 2000 -c 10 http://localhost:8080/hello/world
Requests per second:    2836.15 [#/sec] (mean)
</pre>