<html>
<head>
<style type="text/css">
    body {
        font-family: Arial;
    }
    #monitor {
        width: 800px;
        height: 400px;
    }
    #mapa, #menu {
        width: 400px;
    }
    #tabs div.tab {
        font-size: 11px;
    }
    /* Alto de la lista de eventos */
    #incidentes {
        width: 400px;
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
        include $_SERVER['DOCUMENT_ROOT']."/monitor/index.php" 
        ?>
    </div>
</body>
</html>
