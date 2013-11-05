<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head><title>Monitor Humanitario</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style type="text/css">
    html, body {
        width: 100%;
    }
    body {
        font-family: Arial;
        margin: 0;
	    scrollbar-base-color: #ff0000;!important
    }
    #map, #aaaa {
        width: 450px;
        margin: 0 !important;
    }
    #map {
        height: 400px;
    }
    /* Alto de la lista de eventos */
    #tabs {
        width: 380px;
        height: 460px;
        overflow: auto;
        margin: 0 0 0 5px,
    }
    #incidentes {
        position: relative; /* Para que funcione perfectScrollbar */
        font-size: 11px;
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
