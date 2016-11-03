<html>
    <head>
        <title>@yield('title')</title>
        <!-- Bootstrap CSS -->
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <link href="css/custom.css" rel="stylesheet">
        <!-- jQuery -->
        <script src="js/jquery.js"></script>
        <!-- Bootstrap JavaScript -->
        <script src="js/bootstrap.min.js"></script>
    </head>
    <body>
        <div class="row" style="margin-top:50px;">
            <div class="col-lg-offset-1 col-lg-10 col-md-offset-1 col-md-10 col-sm-12 col-xs-12">
                @yield('content')
            </div>
            
        </div>
    </body>
</html>