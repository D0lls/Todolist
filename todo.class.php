<?php
include_once 'config.php';

//L'objet PDO est en global pour �tre utilis� dans toute la classe
global $db;
$db = new PDO('mysql:host='. DB_HOST .';dbname=' . DB_NAME, DB_USER, DB_PASS); 

class Todo
{
	public $id;
	public $todo;
	public $date;
	public $alert;
	
	
	public function __construct($todo, $date, $id = 0, $alert = 0)
	{
		$this->id = $id;
		$this->todo = $todo;
		$this->date = $date;
		$this->alert = $alert;
	}
	
	
	public function __get($var)
	{
		return $this->$var;
	}
	
	//Le todo est valide ?
	public function isValid()
	{
		return (!empty($this->todo) && !empty($this->id) && (empty($this->date) | (preg_match('#[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}#', $this->date) | preg_match('#[0-9]{1,2}/[0-9]{1,2}/[0-9]{2,4}#', $this->date))));
	}
	
	
	public function __toString()
	{
		return (string)var_dump($this);
	}
	
	
	
	public function add()
	{
		global $db;
		$this->checkValues();
		
		$req = $db->prepare('INSERT INTO todo (todo,date) VALUES (:todo, :date)'); 
		$ok = $req->execute(Array('todo' => $this->todo, 'date' => $this->date));
		$this->id = $db->lastInsertId();
		$erreur = $req->errorInfo(); 
		
		return array('status' => $ok, 'id' => $this->id, 'error' => $erreur); 
	}
	
	
	public function edit($todo, $date)
	{
		global $db;
		
		$this->todo = $todo;
		$this->date = $date;
		$this->checkValues();
		
        $req = $db->prepare('UPDATE todo SET todo=:todo, date=:date WHERE id=:id'); 
		$ok = $req->execute(Array('todo' => $this->todo, 'date' => $this->date, 'id' => $this->id));
		$erreur = $req->errorInfo(); 
		
		return array('status' => $ok, 'error' => $erreur); 
	}
	
	
	public function delete()
	{
		global $db;

		$req = $db->prepare('DELETE FROM todo WHERE id = :id'); 
		$ok = $req->execute(Array('id' => $this->id));
		$erreur = $req->errorInfo();
		
		return array('status' => $ok, 'error' => $erreur);
	}
	
	
	
	public static function getAllTodos()
	{
		global $db;
		
		$req = $db->prepare('SELECT id, todo, date, 
						IF(date BETWEEN CURDATE() AND CURDATE() + INTERVAL '. NB_DAY .' DAY,1,0) as alert 
						FROM todo
						ORDER BY alert DESC, IF (date IS NULL, 1, 0) DESC, id ASC');
		$req->execute();
		
		$tab_todo = array();
		
		while ($data = $req->fetch(PDO::FETCH_OBJ))
			$tab_todo[] = new Todo($data->todo, $data->date, $data->id, $data->alert);
		

		return $tab_todo;//$req->fetchAll(PDO::FETCH_OBJ);
	}
	
	//Contr�le et nettoyage des valeurs
	//Le todo passe dans rawurlencode() pour �viter les probl�mes d'accents
	//Si la date n'est pas reconnue au format fran�ais/am�ricain, elle est nulle
	//Si la date est dans moins de NB_DAY (par d�faut, 2), le todo est mis en 'urgence'
	public function checkValues()
	{
		$this->todo = htmlspecialchars($this->todo, ENT_NOQUOTES, 'ISO-8859-1', false);
		$this->EncodeThatBitch();
		
		if (!preg_match('#[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}#', $this->date))
		{
			if (preg_match('#[0-9]{1,2}/[0-9]{1,2}/[0-9]{2,4}#', $this->date))
			{
				$tab_date = explode('/', $this->date); 
				$this->date = $tab_date[2] . '-' . $tab_date[1] . '-' . $tab_date[0]; 
			} 
			else 
				$this->date = null;
		}
		
		if (isset($this->date))
		{
			$dt_date = new DateTime($this->date);
			$now =  new DateTime(date('Y-m-d'));
			
			$this->alert = ($dt_date >= $now && $dt_date <= $now->add(new DateInterval('P'. NB_DAY .'D'))) ? 1 : 0;
		}
		else
			$this->alert = 0;
	}
	
	
	public function EncodeThatBitch()
	{
		$txt = $this->todo;
		$ret = '';
		for ($i = 0, $n = strlen($txt); $i < $n; $i++)
			$ret .= '%' . dechex(ord($txt[$i]));

		$this->todo = $ret;
	}
	
	public function DecodeThatBitch($txt)
	{
		$ret = '';
		$tab = explode('%', $this->todo);
		
		for ($i = 1, $n = sizeof($tab); $i < $n; $i++)
			$ret .= chr(hexdec($tab[$i]));
		
		$this->todo = $ret;
	}
	
	
}

?>