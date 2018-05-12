<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
            
        <title>KiFrame</title>

        <script src="/_3rd/jquery/test/data/jquery-1.9.1.js"></script>
        <script src="/_3rd/bootstrap/dist/js/bootstrap.min.js"></script>
        <link href="/_3rd/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
  
        <script src="/.js/support.js"></script>
        <script src="/.js/validate.js"></script>
        <script src="/.js/session.js"></script>
        <script src="/.js/logon.js"></script>


        <script>
        <? foreach (KiCONST::dump() as $cName=>$cCont)
            echo "var {$cName}= " .json_encode($cCont) .";\n";
        ?>


        </script>



<style>
html, body {
    height: 100%;
    margin: 0;
    padding: 0;
}


.img {
    padding: 0;
    display: block;
    margin: 0 auto;
    max-height: 90%;
    max-width: 90%;
}
</style>
</head>
<body style='background:#fff'>

<? include('head.php'); ?>

KiFrame
</body>
</html>

