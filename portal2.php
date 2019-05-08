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
    .map_portal {
        width: 450px;
        margin: 5px 0 0 0 !important;
    }
    .map_portal {
        height: 400px;
    }
    .map_portal_home {
        width: 100%;
        margin: 5px 0 0 0 !important;
    }
    .map_portal_home {
        height: 400px;
    }
    /* Alto de la lista de eventos */
    #tabs {
        width: 380px;
        height: 460px;
        overflow: hidden;
        margin: 0 0 0 5px;
        /* position: relative; Para perfect scroll bar */
    }
    #tabs:hover {
        overflow: auto;
    }
    #incidentes {
        font-size: 11px;
    }
</style>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-139902127-1"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'UA-139902127-1');
    </script>
</head>
<body>
    <div id="monitor">
<?php
        $state = (isset($_GET['state'])) ? $_GET['state'] : 0;

        if (empty($_GET['layout'])) {
            $_GET['layout'] = 'portal';
        }

        include dirname(__FILE__)."/index.php" 
        ?>
    </div>
</body>
</html>
