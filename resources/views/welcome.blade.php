<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>404 not found</title>
        <link rel="stylesheet" href="{{asset("style/main.css")}}"/>

    </head>
    <body class="antialiased">
        <div id="notfound">
            <div class="notfound">
                <div class="notfound-404">
                    <h1>Oops!</h1>
                    <h2>404 - The Page can't be found</h2>
                </div>
                <a href="#">Go TO Homepage</a>
            </div>
        </div>

    </body>
</html>
