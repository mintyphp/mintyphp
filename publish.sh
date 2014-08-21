#!/bin/bash
rsync -axv --delete --exclude=.git --exclude=config/config.php . lx55.nlware.com:public_html
