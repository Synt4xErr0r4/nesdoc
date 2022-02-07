<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=htmlspecialchars($pageInfo['title'])?> &times; SyntaxError</title>

    <link rel="stylesheet" href="/static/css/<?=htmlspecialchars($pageInfo['script'])?>.css?t=<?=time()?>">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3"
        crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p"
        crossorigin="anonymous"
        defer></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Favicon -->

    <link rel="apple-touch-icon" sizes="57x57" href="/static/icon/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/static/icon/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/static/icon/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/static/icon/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/static/icon/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/static/icon/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/static/icon/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/static/icon/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/static/icon/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="/static/icon/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/static/icon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/static/icon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/static/icon/favicon-16x16.png">
    <link rel="manifest" href="/static/icon/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/static/icon/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="https://syntaxerror.at">SyntaxError</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="/">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="/6502">NES CPU</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item"><!-- TODO: change to https://syntaxerror.at/social/github -->
                            <a class="nav-link" href="https://github.com/Synt4xErr0r4/nes" target="_blank">
                                <i class="bi bi-github"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <br>

    <div class="container">