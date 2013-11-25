#!/bin/bash

colors=("#F985C2" "#85BCFA" "#84F9E2" "#9EEC58" "#F9DE99")

for ((i=0;i<=4;i++))
do
convert -background ${colors[$i]} -fill black -size 1280x720 -gravity center -font Arial -density 96 -pointsize 20 label:"Place here an Ad of your choice" ad${i}.jpg
done