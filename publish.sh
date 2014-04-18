#!/bin/bash
rsync -axv --delete --exclude='.git' . server.nlware.com:public_html
