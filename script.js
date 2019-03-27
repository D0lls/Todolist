
//Fonctions d'encodage/decodage
function Encodage(txt) 
{
	var ret = '';
	for (i = 0, n = txt.length; i < n; i++)
	{
		x = txt.charCodeAt(i);
		if (x > 0 && x < 255) //On reste dans la table ascii
			ret += '%' + ((x < 16) ? '0':'') + x.toString(16);
	}
	return ret;
}

function Decodage(txt)
{
	var ret = '', tab = txt.split('%');
	for (i = 1, n = tab.length; i < n; i++)
	{
		x = parseInt(tab[i], 16);
		if (x > 0 && x < 255)
			ret += String.fromCharCode(x);
	}
	return ret;
}


// Initialisation d'un objet XMLHttpRequest
function Getxhr()
{
	var xhr = null;

	if(window.XMLHttpRequest)
	   xhr = new XMLHttpRequest(); 
	else if(window.ActiveXObject)
		try { xhr = new ActiveXObject("Msxml2.XMLHTTP"); } catch (e) { xhr = new ActiveXObject("Microsoft.XMLHTTP"); }
	   
	return xhr;
}


//r�cup�ration puis affichage des todo
function LoadTodo()
{
	var xhr = Getxhr();
	xhr.open('GET', 'gestion.php?action=list_todo', true);
	xhr.onreadystatechange = function()
	{
		if(xhr.readyState == 4 && xhr.status == 200)
		{
			retour = eval('(' + xhr.responseText + ')');
			if (retour.status)
			{
				document.getElementById('liste_todo').innerHTML = '';
				document.getElementById('liste_todo').innerHTML += '<ul id="navigation"><li>Numero</li><li>Date</li><li>Tache</li><li>Actions</li></ul>'
				for (var i = 0, n = retour.retour.length; i < n; i++)
				{
					var date = '', x = retour.retour[i];
					
					if (x.date != null)
					{
						tab_date = x.date.split('-');
						date = tab_date[2] + '/' + tab_date[1] + '/' + tab_date[0];
					}
					
					document.getElementById('liste_todo').innerHTML += '<ul><div class="todo" id="div_todo-'+ x.id +' ">' + 
					'<li><span class="text">'+ x.id + '</span></li> '+
						'<li><span class="text" id="todo-' + x.id + '">' + Decodage(x.todo) + '</span></li>' + 
						'<li><span class="actions"><span id="date-'+ x.id +'">' + date + '</span></li>' +
						'<li><a href="" onclick="return EditChamp('+ x.id +');"><img src="img/edit.png"></a> ' +
						'<a href="" onclick="return DeleteTodo('+ x.id +');"><img src="img/delete.png"></a></span></li>' +
						'</div></ul>';
				}
			}
		}
	};
	xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhr.send(null);
	
	return false;
}



function AjoutTodo()
{
	var todo = document.getElementById('todo').value;
	var date = document.getElementById('date').value;
	
	if (todo.length > 0)
	{
		var xhr = Getxhr();

		xhr.open('POST', 'gestion.php?action=add', true);
		xhr.onreadystatechange = function()
		{
			if(xhr.readyState == 4 && xhr.status == 200)
			{
				retour = eval('(' + xhr.responseText + ')');
				
				if (retour.status)
				{
					var tab_date = date.split('/');
					var today = new Date();
					var date_todo = new Date(tab_date[2], tab_date[1] - 1, tab_date[0]); //constructeur de merde
					//date_todo.setDate((today.getDate()+nb_day));

					isAlert = ((date_todo - today) <= (NB_DAY * 24 * 3600 * 1000));
					todo = todo.replace(new RegExp('(&)', 'g'), '&amp;').replace(new RegExp('(<)', 'g'), '&lt;').replace(new RegExp('(>)', 'g'), '&gt;');
				
				
					
					document.getElementById('liste_todo').innerHTML += '<ul><div class="todo" id="div_todo-' + retour.id + ' ">' +
						'<li><span class="text">' + retour.id + '</span></li> ' +
						'<li><span class="text" id="todo-' + retour.id + '">' + todo + '</span></li>' +
						'<li><span class="actions"><span id="date-' + retour.id + '">' + date + '</span></li>' +
						'<li><a href="" onclick="return EditChamp(' + retour.id + ');"><img src="img/edit.png"></a> ' +
						'<a href="" onclick="return DeleteTodo(' + retour.id + ');"><img src="img/delete.png"></a></span></li>' +
						'</div></ul>';
					
					
				}
			}
		};
		xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xhr.send('todo=' + Encodage(todo) + '&date=' + date);
		
		document.getElementById('todo').value = document.getElementById('date').value = '';
	}
		return false;
}


//Si on annule l'edit, on restaure les valeurs avec ces variables
var save_todo;
var save_date;
var save_id;

//Insertion de champs text pour l'�dition
function EditChamp(id)
{
	if (!document.getElementById('edit_todo'))
	{
		var div_todo = document.getElementById('todo-' + id);
		var div_date = document.getElementById('date-' + id);
		var todo = div_todo.innerHTML;
		var date = div_date.innerHTML;
		
		save_todo = todo;
		save_date = date;
		save_id = id;
		
		//reconversion des caract�res html pour l'edit
		todo = todo.replace(new RegExp('(&amp;)', 'g'), '&').replace(new RegExp('(&lt;)', 'g'), '<').replace(new RegExp('(&gt;)', 'g'), '>');
		
		div_todo.innerHTML = div_date.innerHTML = '';

		var edit_todo = document.createElement('textarea');
		edit_todo.setAttribute('cols', '60');
		edit_todo.setAttribute('rows', todo.split('\n').length + 1);
		edit_todo.setAttribute('name', 'edit_todo');
		edit_todo.setAttribute('id', 'edit_todo');
		edit_todo.setAttribute('placeholder', 'Votre t�che');
		edit_todo.appendChild(document.createTextNode(todo));
		div_todo.appendChild(edit_todo); 
		
		var edit_date = document.createElement('input');
		edit_date.setAttribute('type', 'text');
		edit_date.setAttribute('size', '9');
		edit_date.setAttribute('name', 'edit_date');
		edit_date.setAttribute('id', 'edit_date');
		edit_date.setAttribute('placeholder', 'dd/mm/YYYY');
		edit_date.setAttribute('value', date);
		div_todo.appendChild(edit_date); 

		div_date.innerHTML = '<a href="" onclick="return EditTodo('+ id +');"><img src="img/valid.png"></a>';
	}
	else
	{
		document.getElementById('todo-' + save_id).innerHTML = save_todo;
		document.getElementById('date-' + save_id).innerHTML = save_date;
	}
	
	return false;
}


function EditTodo(id)
{
	var todo = document.getElementById('edit_todo').value;
	var date = document.getElementById('edit_date').value;
	
	if (todo.length > 0)
	{
		var xhr = Getxhr();

		xhr.open('POST', 'gestion.php?action=update', true);
		xhr.onreadystatechange = function()
		{
			if(xhr.readyState == 4 && xhr.status == 200)
			{
				retour = eval('(' + xhr.responseText + ')');
				if (retour.status)
				{
					document.getElementById('todo-' + id).innerHTML = todo;
					document.getElementById('date-' + id).innerHTML = date;						
				}
			}
		};
		xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xhr.send('id='+ id + '&todo=' + Encodage(todo) + '&date=' + date);
	}
	
	return false;
}


function DeleteTodo(id)
{
	if (confirm('O RLY ?'))
	{
		var data = 'id='+ id;	
		var xhr = Getxhr();

		xhr.open('POST', 'gestion.php?action=delete', true);
		xhr.onreadystatechange = function()
		{
			if(xhr.readyState == 4 && xhr.status == 200)
			{
				retour = eval('(' + xhr.responseText + ')');
				
				if (retour.status)
					document.getElementById('liste_todo').removeChild(document.getElementById('div_todo-' + id));
			}
		};
		xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xhr.send(data);
	}
	
	return false;
}

window.onload = function() {
	LoadTodo();
}