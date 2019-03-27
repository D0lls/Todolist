<!DOCTYPE html>
<?php include_once 'config.php'; ?>
<html>
<head>
<title>TODO</title>
<meta charset="ISO-8859-1"> 
<link rel="stylesheet" href="style.css" />
<script type="text/javascript">var NB_DAY = <?=NB_DAY; ?></script>
<script type="text/javascript" src="script.js"></script>

</head>
<body>
<h1 style="text-align:center"> Ma Belle TODO list</h1>
<br />



<div class="form">
<form name="form_todo" action="#" method="GET" onsubmit="return AjoutTodo();"> 
<label for="date"></label>
<input type="text" name="date" id="date" size="9" placeholder="dd/mm/YYYY">
<label for="todo"></label>
<textarea name="todo" id="todo" rows="2" cols="70" placeholder="Votre tache" required></textarea>
<input type="submit" value="Ajouter la tache">
</form>
</div>



<div class="liste_todo" id="liste_todo">



</body>
</html>