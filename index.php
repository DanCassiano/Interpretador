<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="UTF-8">
	<title>Lietura</title>
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
</head>
<body>
	<?php 
		include "class/interpretador.class.php";
		$leitura = new Interpretador();
		$leitura->setArq('com_l.conf');
		$leitura->getFuncoes();
	?>
</body>
</html>