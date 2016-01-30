<!DOCTYPE html>
<html>
<head lang="en-Us">
	<title>Recipes</title>
	<link href="cookingCSS.css" rel="stylesheet">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
	<script>
		$('document').ready(function() {
			
			//Attach a click handler to the addRecipeButton element.
			$("#addRecipeButton").click(function() {
				var $form = $("#recipeForm");
				var $main = $("#mainContent");
				$form.toggle("slow");
				$main.toggle("slow");
			});
			
			//Attach a click handler to the addRecipeButton element.
			$("#exitForm").click(function() {
				var $form = $("#recipeForm");
				var $main = $("#mainContent");
				$form.toggle("slow");
				$main.toggle("slow");
			});
			
			$("#exitButton").click(function() {
				$("#recipeDisplay").toggle();
				$("#mainContentPortal").toggle();
			});			
			
			//Prevent the default submit action of the form and instead validate via javascript
			$("#recipeForm").submit(function(e) {
				e.preventDefault();
				validateForm();
			});
			
			//Make request to server to create new row in the recipes table.
			function makeRequest(payLoad) {
				var main = $("#mainContent");
				var form = $("#recipeForm");
				var request = new XMLHttpRequest();
				var url = "http://localhost/Cooking_Folder/recipeCollection/mysql.php";
				
				request.onreadystatechange = handleStateChange;
				request.open("POST",url);
				request.send(payLoad);
				main.show();
				form.hide();
				
				
				function handleStateChange() {
					if(request.readyState === 4)	{
						if(request.status === 200)	{
							/*main.show();
							form.hide();*/
							var response = JSON.parse(request.responseText);
							//If response from php script is to send data about a single recipe,display it.
							if(response.action === "getRecipe") {
								displayRecipe(response);
							}
							//If response contains the title and id of a newly created recipe,create an li for it.
							else {
								createLi(response);
							}
							
						} else {
							console.log(request.statusText);
						}
					}
				}
			}
		
			
			//Validates form,then submits it.
			function validateForm()	{
				var form = document.getElementById("recipeForm");
				var title = form.formTitle.value;
				var ingredients = form.ingredients.value;
				var instructions = form.instructions.value;
				var checked = checkForChecked().value;
				var obj = {title: title,ingredients: ingredients,instructions: instructions,category: checked,action: "insert"};
				var payLoad = JSON.stringify(obj);
				makeRequest(payLoad);
				
				//Check the form for the recipe category checkbox that the user selected.
				function checkForChecked()	{
					var form = $("#recipeForm");
					var formInputs = document.getElementsByTagName("input");
					
					for(var i = 0;i < formInputs.length;i++)	{
						if(formInputs[i].type === "checkbox")	{
							var j = formInputs[i];
							if(j.checked === true)	{
								return formInputs[i];
							}
						}
					}
				}	
				
			}
			
			//Creat a new list item after user successfully creates a new recipe.
			function createLi(data) {
				var li = document.createElement("li");
				li.setAttribute("class","recipe");
				li.setAttribute("data-recipeId",data.id);
				li.innerHTML = data.title;
				var parentList;
				switch(data.category) {
					case 4:
						parentList = document.getElementById("meatList");
						break;
					case 5:
						parentList = document.getElementById("veggieList");
						break;
					case 6:
						parentList = document.getElementById("breadList");
						break;
					default:
						//do nothing
						break;
				}
				parentList.appendChild(li);
				setClickHandlers();
			}
			
			//Make request to server for content of each recipe and attach a click handler to display it.
			function setClickHandlers() {
				function showRecipe() {
					var obj = {
						id: this.getAttribute("data-recipeid"),
						action: "getRecipe"
					};
					var payLoad = JSON.stringify(obj);
					var response = makeRequest(payLoad);
				}	
			
				var recipeLis = document.querySelectorAll('li.recipe');
				for(var i = 0;i < recipeLis.length;i++)	{
					recipeLis[i].onclick = showRecipe;
				}	
				
			
			}
			
			function displayRecipe(recipeData) {
				var $recipePortal = $("#recipeDisplay");
				var $mainContentPortal = $("#mainContentPortal");
				var titlePortal = document.getElementById("recipeh1");
				var ingredientsPortal = document.getElementById("recipeBodyIngredients");
				var instructionsPortal = document.getElementById("recipeBodyInstructions");
				
				titlePortal.innerHTML = recipeData.title;
				ingredientsPortal.innerHTML = recipeData.ingredients;
				instructionsPortal.innerHTML = recipeData.instructions;
				$mainContentPortal.toggle();
				$recipePortal.toggle();
			}
			
			setClickHandlers();
			
			
		});
	</script>
	<?php
		
		#Get all recipes from database and return the resultant associative array.
		function load_recipes() {
			#connect to database
			$db = new mysqli('localhost','root','dbpass','recipes');
			#close function and mysqli instance if can't connect. 
			if($db->connect_errno > 0) {
				echo 'Unable to connect to database.';
				$db->close();
				$db = null;
				return;
			}
			
			#execute query and handle failure
			if($db->query("SELECT * FROM recipestbl") === false) {
				echo "Failure for some unknown reason.";
				$db->close();
				return;
			}
			#if query is successful return the associative array returned from method
			$result = $db->query("SELECT * FROM recipestbl");
			$result_array = $result->fetch_all(MYSQLI_ASSOC);
			return $result_array;
			
		}
		
		$items = load_recipes();
	?>
</head>
<body>
	<div id="mainContent">
		<div id="mainContentPortal">
			<h1 style="text-align: center;margin-left: 175px">Base Recipes</h1>
			<div id="columnOne" class="recipeColumns">
				<ul id="breadList">
					<li><h4>Breads</h4></li>
					<?php
						#loop through an array filled with associative arrays each representing a table row.
						foreach($items as $i) {
							#output any recipes that belong in the 'bread' section
							
							if($i["cat_id"] === "6") {
								echo '<li class="recipe" data-recipeId="' . $i["id"] . '">' . $i["title"] . '</li>';
							}
						}
					?>
				</ul>
			</div>
				
			<div id="columnTwo" class="recipeColumns">
				<ul id="meatList">
					<li><h4>Meat & Seafood</h4></li>
					<?php
						#loop through an array filled with assoc arrays each representing a table row.
						foreach($items as $i) {
							#output any recipes that belong in the 'meat & seafood' section
							
							if($i["cat_id"] === "4") {
								echo '<li class="recipe" data-recipeId="' . $i["id"] . '">' . $i["title"] . '</li>';
							}
						}
					?>
				</ul>
			</div>
				
			<div id="columnThree" class="recipeColumns">
				<ul id="veggieList">
					<li><h4>Vegetarian</h4></li>
					<?php
						#loop through an array filled with assoc arrays each representing table a row.
						foreach($items as $i) {
							#output any recipes that belong in the 'vegetarian' section
							
							if($i["cat_id"] === "5") {
								echo '<li class="recipe" data-recipeId="' . $i["id"] . '">' . $i["title"] . '</li>';
							}
						}
					?>				
				</ul>
			</div>
			
			
		</div>
		<div id="recipeDisplay">
			<h1 id="recipeh1">Hello World!</h1>
			<p id="recipeBody">
				<p id="recipeBodyIngredients"></p>
				<p id="recipeBodyInstructions"></p>
			</p>
			<button id="exitButton">Return to Recipes</button>
		</div>
	</div>
		
	<div id="rightNav">
		<nav id="mainNav">
			<ul>
				<li><a href="index.php">Recipes</a></li>
				<li><a href="techniques.html">Techniques</a></li>
				<li><a href="ingredients.html">Ingredients</a></li>
				<li><a href="tools.html">Tools</a></li>
			</ul>
		</nav>
		<button id="addRecipeButton">Add Recipe</button>
	</div>
		
	<form id="recipeForm" style="position: absolute;left: 28%;background-color: gray;border: solid 5px black;display: none;" action="mysql.php">
			<label for="formTitle">Enter recipe title:</label>
			<input type="text" id="formTitle" name="title" required>
			<br>
			<br>
			<label for="ingredients">Enter recipe ingredients:</label>
			<input type="text" id="ingredients" name="ingredients" required>
			<p>*Please use commas when entering recipe,once added the recipe ingredients will be displayed as you wrote them.</p>
			<br>
			<br>
			<p><label for="instructions">Enter recipe instructions below.</label></p>
			<textarea cols="55" rows="10" id="instructions" name="instructions" required></textarea>
			<br>
			<br>
			<p><label for="column">Choose under which classification this recipe will be placed under.</label></p>
			<label for="breadCheckBox">Breads</label>
			<input type="checkbox" id="breadCheckBox" value="bread" class="check" name="category">
			<label for="meatCheckBox">Meat & Fish</label>
			<input type="checkbox" id="meatCheckBox" value="meat" class="check" name="category">
			<label for="veggieCheckBox">Vegetarian</label>
			<input type="checkbox" id="veggieCheckBox" value="veggies" class="check" name="category">
			<br>
			<br>
			<span><button type="submit" id="submitForm">Submit</button></span>
			<span><button type="reset">Reset Form</button></span>
			<span><button type="button" id="exitForm">Exit Form</button></span>
			<br>
			<br>
	</form>

</body>
</html>