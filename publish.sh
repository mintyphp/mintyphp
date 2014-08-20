#!/bin/bash
rsync -axv --delete --exclude='.git' . lx55.nlware.com:public_html
