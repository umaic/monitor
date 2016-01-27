#!/bin/bash

java -jar yuicompressor-2.4.8.jar media/css/fe.css -o media/css/fe.min.css
java -jar yuicompressor-2.4.8.jar media/css/nouislider.css -o media/css/nouislider.min.css
#java -jar yuicompressor-2.4.8.jar media/css/geostats.css -o media/css/geostats.min.css
#java -jar yuicompressor-2.4.8.jar media/css/popover.css -o media/css/popover.min.css
#java -jar yuicompressor-2.4.8.jar media/css/orange.css -o media/css/orange.min.css
java -jar yuicompressor-2.4.8.jar media/css/brand.css -o media/css/brand.min.css

java -jar yuicompressor-2.4.8.jar media/js/fe.js -o media/js/fe.min.js
java -jar yuicompressor-2.4.8.jar media/js/map.js -o media/js/map.min.js
#java -jar yuicompressor-2.4.8.jar media/js/map-ol2.js -o media/js/map-ol2.min.js
#java -jar yuicompressor-2.4.8.jar media/js/fe-ol2.js -o media/js/fe-ol2.min.js

cd media/css

cat ol.css fe.min.css brand.min.css orange.min.css jquery-ui-1.8.22.custom.min.css fa/css/font-awesome.min.css jquery.dataTables.min.css popover.min.css geostats.min.css nouislider.min.css > m.css

cd ../js

cat jquery.min.js jquery-ui.min.js ol.js fe.min.js map.min.js url_tools.min.js highcharts.js icheck.min.js geostats.min.js jquery.dataTables.min.js tooltip.js popover.js focus-element-overlay.min.js nouislider.min.js > m.js
