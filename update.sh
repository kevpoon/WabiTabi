#/bin/bash

git pull
yes | cp src/model/* ~/public_html/
chmod 711 ~/public_html/*
