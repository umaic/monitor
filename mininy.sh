#!/bin/bash
echo Compressing CSS Files... 
saved=0
EXTS=(js css)
for ext in "${EXTS[@]}"; do
	#for f in `find -name "*.css" -not -name "*.min.css"`; do 
	for f in `find -name "*.$ext" -not -name "*.min.$ext"`; do 
	target=${f%.*}.min.$ext 
	echo "\t- "$f to $target 
	FILESIZE=$(stat -c%s "$f") 
	java -jar yuicompressor-2.4.7.jar --type $ext --nomunge -o $target $f 
	FILESIZEC=$(stat -c%s "$target") 
	diff=$(($FILESIZE - $FILESIZEC)) 
	saved=$(($saved + $diff)) 
	echo "\t $diff bytes saved" 
	done 
	echo "Done  Total saved: $saved bytes" 
done
#chown www-data.www-data *.min.css
