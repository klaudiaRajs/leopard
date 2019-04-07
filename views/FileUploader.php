<html>
<head>
    <title>Add your file</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
          integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
</head>
<body class="p-3 mb-2 text-white bg-secondary">
<div class="container">
    <div class="row">
        <div class="col align-self-start bg-dark p-5 rounded border border-warning">
            <form id="fileForm" action="">
                <div id="fields">
                    <label>Please select which naming convention your code follows</label>
                    <!-- Naming standard -->
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="convention" id="camelCaseConventionRadio"
                               value="camelCase" checked>
                        <label class="form-check-label" for="camelCaseConventionRadio">camelCaseConvention</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="convention" id="PascalCaseConvention"
                               value="PascalCase">
                        <label class="form-check-label" for="PascalCaseConvention">PascalCaseConvention</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="convention" id="underscore_convention"
                               value="underscore_convention">
                        <label class="form-check-label" for="underscore_convention">underscore_convention</label>
                    </div>
                    <!-- File upload -->
                    <label class=" mt-4">Please, choose files you want to analyse: </label>
                    <div class="form-group">
                        <input class="" type="button" value="Add one more file" onclick="addOneMore()"> <br>
                        <label for="exampleFormControlFile1" class="mt-2">File input</label>
                        <input type="file" class="form-control-file" id="fileInputNo">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Sign in</button>
            </form>
        </div>
    </div>
</div>
<script>
    $i = 1;

    function addOneMore() {
        $("#fields").append("<label for=\"exampleFormControlFile1\" class=\"mt-2\">File input</label><input type=\"file\" class=\"form-control-file\" name='file" + $i + "'>");
        $i++;
        return false;
    }
</script>
</body>
</html>
