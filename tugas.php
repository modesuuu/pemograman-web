<?php

$colors = [
    'biru' => 'blue',
    'merah' => 'red',
    'hijau' => 'green',
    'kuning' => 'yellow',
    'ungu' => 'purple',
    'oranye' => 'orange',
    'putih' => 'white',
    'hitam' => 'black',
];

$bgColor = 'white';

if (isset($_GET["value"]) && $_GET["value"] !== '') {
    $input = strtolower(trim($_GET['value']));
    if (array_key_exists($input, $colors)) {
        $bgColor = $colors[$input];
    } else {
        $bgColor = htmlspecialchars($input);
    }
};

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        body {

            background-color: <?= $bgColor ?>;
        }
    </style>
</head>

<body>

    <form action="" method="get">

        <input type="text" name="value" value="<?= isset($_GET['value']) ? htmlspecialchars($_GET['value']) : '' ?>">
        <input type="submit" value="Kirim">
    </form>

</body>
</html> 
    