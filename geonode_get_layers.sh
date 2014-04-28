#!/bin/bash

file="geonode_layers.xml"

#curl "http://geonode.salahumanitaria.co/geoserver/wms?request=GetCapabilities&service=WMS&version=1.3" -o $file

i=0

#echo "cat //Layer/Layer/Name|//Layer/Layer/Title|//Layer/Layer/Abstract" | xmllint --shell geonode_layers.xml |

echo "cat //Layer/Layer[position()>11]/Name|//Layer/Layer[position()>11]/Title|//Layer/Layer[position()>11]/Abstract" | xmllint --shell geonode_layers.xml |

#xmllint --xpath '//Layer/Layer[position()>11]/Name|//Layer/Layer[position()>11]/Title|//Layer/Layer[position()>11]/Abstract' $file |

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

				li="<li><div class='left chk'><input type='checkbox' value='$name' /></div><div class='left'> <h3>$title</h3><p class='nota'>$abstract</p></div><div class='clear'></div> </li>"
				
				i=0
				
				echo $li
				
			fi

			let i=$i+1	
		fi
	fi
	
	if [ $i -eq 0 ]; then
		let i=$i+1
	fi

	
	
    #title=$( grep -oPm1 "(?<=<Title>)[^<]+" <<< $line)
    #title=$(awk -F "[><]" '/Title/{print $3}' <<< $line)
    
done
