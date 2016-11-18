#!/bin/bash

#cd /srv/www/htdocs/monitor/

json=$1
				
file="geonode_layers.xml"

curl "http://geonode.umaic.org/geoserver/wms?request=GetCapabilities&service=WMS&version=1.3" -o $file

i=0
r=""

while read line ; do
	
	#line=${l//$'\n'/}
	
	if [ $i -gt 0 ]; then
		if [[ $line == \<Name* ]] || [[ $line == \<Title* ]] || [[ $line == \<Abstract* ]]; then 
			if [ $i -eq 1 ]; then
				name=$(awk -F "[><]" '/Name/{print $3}' <<< $line)

			elif [ $i -eq 2 ]; then
				title=$(awk -F "[><]" '/Title/{print $3}' <<< $line)

			elif [ $i -eq 3 ]; then
				abstract=$(awk -F "[><]" '/Abstract/{print $3}' <<< $line)
				
				if [ $json -eq 0 ]; then
					r+="<li><div class='left chk'><input type='checkbox' value='$name' /></div><div class='left'> <h3>$title</h3><p class='nota'>$abstract</p></div><div class='clear'></div> </li>"
				else
					if [[ $title == *vereda* ]] || [[ $title == *Vereda* ]]; then
						
						if [ "$r" != "" ]; then
							r+=","
						fi
						
						r+="{\"nombre\":\"$title\",\"wms\":\"$name\"}"
					fi
				fi

				i=0
				
			fi

			let i=$i+1	
		fi
	fi
	
	if [ $i -eq 0 ]; then
		let i=$i+1
	fi
	
done < <( echo "cat //Layer/Layer/Name|//Layer/Layer/Title|//Layer/Layer/Abstract" | xmllint --shell $file )
#done < <( echo "cat //Layer/Layer[position()>2]/Name|//Layer/Layer[position()>2]/Title|//Layer/Layer[position()>2]/Abstract" | xmllint --shell $file )

if [ $json -eq 1 ]; then
	r="[$r]"
fi

echo "$r"
