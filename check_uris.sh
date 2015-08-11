#!/bin/bash

# check http response code for each image request
INPUT=input/Lyon-0604-liens.csv
OLDIFS=$IFS
IFS=,
[ ! -f $INPUT ] && { echo "$INPUT file not found"; exit 99; }
while read -r a b c d e f g h i j
do
    # echo "Ark : $Ark"
    url=$a$b$c$d$e$f$g$h$i$j
    #echo $url
    #curl -sL -w "%{http_code} %{url_effective}\\n" "$url" -o /dev/null
    curl -s -I -w "%{http_code} %{url_effective}\\n" "$url" -o /dev/null
    #curl "$baseUrl$Ark" -o "$Ark.xml"
    #curl "$baseUrl$Ark"
done < $INPUT
IFS=$OLDIFS
