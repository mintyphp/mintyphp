#!/bin/bash
rsync -axv --delete --exclude=.git --exclude=config/app.php . lx55.nlware.com:public_html
