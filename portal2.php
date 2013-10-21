<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head><title>Monitor Humanitario</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style type="text/css">
    body {
        font-family: Arial;
        margin: 0;
	scrollbar-base-color: #ff0000;!important
    }
    #monitor {
        width: 800px;
        height: 400px;
    }
    #map, #aaaa {
        width: 400px;
        height: 400px;
        margin: 0 !important;
    }
    #tabs div.tab {
        font-size: 11px;
    }
    /* Alto de la lista de eventos */
    #incidentes, #totles {
        width: 400px;
    }
    #incidentes {
        position: relative; /* Para que funcione perfectScrollbar */
        height: 400px;
        font-size: 11px;
        overflow: auto;
    }
</style>
</head>
<body>
    <div id="monitor">
<?php
        $_GET['layout'] = 'portal';
        $state = (isset($_GET['state'])) ? $_GET['state'] : 0;
        include dirname(__FILE__)."/index.php" 
        ?>
    </div>
</body>
</html>
