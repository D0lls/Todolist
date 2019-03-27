<?php 
include_once 'config.php';
include_once 'todo.class.php';

/*
//Ca peut etre utile
session_destroy();
$_SESSION = null;
die;
*/

$tab_todo = array();
$action = $_GET['action'];


//R�cup�ration des todos, depuis $_SESSION ou la base
if (sizeof($_SESSION['tab_todo']) > 0)
{
	foreach($_SESSION['tab_todo'] as $myTodo)
		$tab_todo[] = unserialize($myTodo);
}
else
{
	$tab_todo = Todo::getAllTodos();
}


//Insertion de nouvelles taches 
if ($action == 'add')
{	
	if (!empty($_POST['todo']))
	{
		$myTodo = new Todo($_POST['todo'], $_POST['date']);
		$tab = $myTodo->add();
		$tab_todo[] = $myTodo;
    }
	else
		$tab = array('status' => false, 'error' => 'Indiquez un todo'); 

	echo json_encode($tab);
}


//Modif
if ($action == 'update')
{
	if (!empty($_POST['todo']) && !empty($_POST['id']))
	{
		for ($i = 0, $n = sizeof($tab_todo); $i < $n; $i++)
			if ($tab_todo[$i]->id == $_POST['id'])
				$tab = $tab_todo[$i]->edit($_POST['todo'], $_POST['date']);
	}
	else
		$tab = array('status' => false, 'error' => 'Param�tre(s) manquant(s)');
	
	
	echo json_encode($tab);
}


//Suppression
if ($action == 'delete')
{
	if (!empty($_POST['id']))
	{
		for ($i = 0, $n = sizeof($tab_todo); $i < $n; $i++)
			if ($tab_todo[$i]->id == $_POST['id'])
			{
				$tab = $tab_todo[$i]->delete();
				$tab_todo[$i] = null;
			}
	}
	else
		$tab = array('status' => false, 'error' => 'Param�tre(s) manquant(s)');
		
	
	echo json_encode($tab);
}


//Liste des todo
if ($action == 'list_todo')
{
	$tab = array();
	$tab['retour'] = $tab_todo;
	$tab['status'] = (sizeof($tab_todo) > 0);
	
	echo json_encode($tab);
}


if ($tab['status']) //Si la liste est modif
{
	//tri des todos
	usort($tab_todo, 'Tri');


	$_SESSION['tab_todo'] = array();
	for ($i = 0, $n = sizeof($tab_todo); $i < $n; $i++)
	{
		if (!empty($tab_todo[$i]) && $tab_todo[$i]->isValid())
			$_SESSION['tab_todo'][] = serialize($tab_todo[$i]);
	}
}


//Fonction de tri; dans l'ordre des dates, puis des ID (les urgences d'abord, et ensuite par ordre d'ajout)
function Tri($a, $b)
{
	$x = 0;
	
	if (empty($a->date) && !empty($b->date))
		$x = 1;
	else if (!empty($a->date) && empty($b->date))
		$x = -1;
	else if (empty($a->date) && empty($b->date))
	{
		if ($a->id < $b->id)
			$x = -1;
		else if ($a->id > $b->id)
			$x = 1;
		else
			$x = 0;
	}
	else
	{
		if (new DateTime($a->date) < new DateTime($b->date))
			$x = 1;
		else if (new DateTime($a->date) > new DateTime($b->date))
			$x = -1;
		else
			$x = 0;
	}
	
	return $x;
}
?> 