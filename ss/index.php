<?php 

if (!isset($_GET['x']) || !isset($_GET['q'])) {
    die('a');
}
    
$preg = preg_match('/([dv])(\d+)/', $_GET['q'], $matches);

if (empty($preg)) {
    die;
}

$dv = $matches[1];
$id = $matches[2];

$file = $dv.'/'.$id.'.pdf';

if (file_exists($file)) {
    ?>
    <iframe width="100%" height="100%" src="<?php echo $file ?>"></iframe>
    <?php
}
else {
    echo 'El original no se encuentra disponible';
}
?>
