<html>
<head>
    <title>Your file!</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
          integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <style>
        .min-width{
            min-width: 80%;
        }
    </style>
</head>
<body class="p-3 mb-2 text-white bg-secondary">

<div class="container">
    <div class="row ml-5 mr-5 justify-content-md-center">
        <div class="col col-md-auto align-self-start bg-dark p-5 rounded border border-warning  min-width">
            <code class="text-light">
                <?php if (isset($fileContents)) {
                    echo $fileContents;
                } ?>
            </code>
        </div>
    </div>
</div>

</body>
</html>