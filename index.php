<link rel="stylesheet" type="text/css" href="css/bootstrap.css">

<div class="alert alert-info fade in">
  <strong>Nota:</strong> es importante NO interrumpir ningún proceso, es decir, no cerrar la ventana y comprobar que se cuenta con una conexión estable de internet.
</div>

<div class="container">

	<?php if (isset($_GET['completado'])): ?>
		<div class="alert alert-success fade in">
		  <strong><?php echo ucfirst($_GET['completado']); ?></strong> importad<?php echo $_GET['completado'] == "personas" ? "a" : "o" ?>s con éxito
		</div>
	<?php endif; ?>

	<div class="btn-group">
		<a href="php/process.php?a=personas" class="btn btn-default">
			Procesar Maestro <br>
			<small>(duración aprox. 1 min.)</small>
		</a>
		<a href="php/process.php?a=resultados" class="btn btn-default">
			Procesar Resultados <br>
			<small>(duración aprox. 3 min.)</small>
		</a>
		<a href="php/process.php?a=indicadores&max=2500&p=0" class="btn btn-default">
			Procesar Indicadores <br>
			<small>(duración aprox. 4 hrs.)</small>
		</a>
		<a href="php/process.php?a=objetivos" class="btn btn-default">
			Procesar Objetivos <br>
			<small>(duración aprox. 30 min.)</small>
		</a>
	</div>
</div>