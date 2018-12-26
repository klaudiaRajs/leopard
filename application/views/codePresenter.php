<html>
<head>
    <title>Your file!</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
          integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
</head>
<body class="p-3 mb-2 text-white">
<div class="container">
    <div class="row">
        <div class="col align-self-start bg-dark p-5 rounded border border-warning">
            <code class="text-light">
                <?php
                        echo $fileContents;
                ?>
            </code>
        </div>
    </div>
</div>

</body>
</html>