<html>
<head>
<style type="text/css">
    body {
        font-family: Arial;
        margin: 0;
    }
    #monitor {
        width: 900px;
        height: 400px;
    }
    #map, #aaaa {
        width: 400px;
        margin: 0 !important;
    }
    #tabs div.tab {
        font-size: 11px;
    }
    #incidentes, #totales {
        width: 400px;
    }
    /* Alto de la lista de eventos */
    #incidentes {
        position: relative; /* Para que funcione perfectScrollbar */
        height: 400px;
        font-size: 11px;
        overflow: hidden;
    }
</style>
</head>
<body>
    <div id="monitor">
        <?php
        $_GET['layout'] = 'portal';
        $state = (isset($_GET['state'])) ? $_GET['state'] : 0;
        include $_SERVER['DOCUMENT_ROOT']."/monitor/index.php" 
        ?>
    </div>
</body>
</html>
