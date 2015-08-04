#!/bin/bash

topojson -o mpios_topo.json mpios_geonode.json -q 1000 --id-property MUN_P_CODE -p MUNNAME
