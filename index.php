<?php
	require('typekit-client.php');
	require('settings.php');

	$typekit = new Typekit();
	$status = null;
	$error_message = null;
	$success_message = null;

	if(isset($_POST['action'])){
		if($_POST["action"] == 'edit' && $_POST["id"]){

			$status = $typekit->update($_POST["id"], [
				"name" => htmlspecialchars_decode($_POST["name"]),
				"domains" => $_POST['domains']
			], $typekit_token);

			if(is_null($status)){
				$error_message = "Action failed";
			}
			else {
				$success_message = "Kit successfully edited";
			}
		}

		if($_POST["action"] == 'duplicate' && $_POST["id"]){
			$kit_to_duplicate = $typekit->get($_POST["id"], $typekit_token)['kit'];
			unset($kit_to_duplicate['id']);

			$kit_to_duplicate['name'] = $kit_to_duplicate['name'] . ' ' . date('j/m/Y H:i:s');

			$status = $typekit->create($kit_to_duplicate, $typekit_token)['kit'];

			if(!is_null($status)){
				$status = $typekit->publish($status['id'], $typekit_token);
			}

			if(is_null($status)){
				$error_message = "Action failed";
			}
			else {
				$success_message = "Kit successfully duplicated";
			}
		}

		if($_POST["action"] == 'delete' && $_POST["id"]){
			$status = $typekit->remove($_POST["id"], $typekit_token);

			if(is_null($status)){
				$error_message = "Action failed";
			}
			else {
				$success_message = "Kit successfully removed";
			}
		}
	}


	$kits = $typekit->get(null, $typekit_token)['kits'];
?>


<!DOCTYPE html>
<html>
<head>
	<script src='//cdnjs.cloudflare.com/ajax/libs/jquery/2.0.3/jquery.min.js'></script>
	<script src='//cdnjs.cloudflare.com/ajax/libs/chosen/1.0/chosen.jquery.min.js'></script>
	<script src='//cdnjs.cloudflare.com/ajax/libs/knockout/3.0.0/knockout-min.js'></script>
	<script src='//netdna.bootstrapcdn.com/bootstrap/3.0.3/js/bootstrap.min.js'></script>
	<script src='script.js'></script>
	<script>
		var typekitKits = [
		<?php foreach ($kits as $kit) :
			echo json_encode($typekit->get($kit['id'], $typekit_token)['kit']) . ', ';
		endforeach ?>
		]
	</script>

	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css">
	<link rel="stylesheet" href="style.css">
</head>
<body>
	<div class="container">
		<div class="page-header">
			<h1>Rexsoftware Typekit libraries</h1>
		</div>

		<?php if(!is_null($error_message)): ?>
			<div class="alert alert-danger">
				<strong>Warning:</strong> <?php echo $error_message; ?>
			</div>
		<?php endif ?>

		<?php if(!is_null($success_message)): ?>
			<div class="alert alert-success">
				<strong>Success:</strong> <?php echo $success_message; ?>
			</div>
		<?php endif ?>

		<form role="form">
			<div class="form-group">
				<label for="searchFilter">Filter</label>
				<input type="email" class="form-control" id="searchFilter" placeholder="Enter search terms" data-bind="value: searchTerm, valueUpdate: 'afterkeydown'">
			</div>
		</form>
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="col-md-2"><h4>Name</h4></div>
					<div class="col-md-2"><h4>ID</h4></div>
					<div class="col-md-3"><h4>CSS Families</h4></div>
					<div class="col-md-3"><h4>Domains</h4></div>
					<div class="col-md-2"><h4>Actions</h4></div>
				</div>
			</div>
			<div class="col-md-12 kit-table" data-bind="foreach: filteredKits">
				<form class="form" role="form" action="/" method="post" onsubmit="return validate(this);">
					<div class="row">
						<div class="col-md-2">
							<ul class="list-group">
								<li class="list-group-item">
									<input type="text" name="name" class="form-control" data-bind="value: name">
								</li>
							</ul>
						</div>
						<div class="col-md-2">
							<ul class="list-group">
								<li class="list-group-item list-group-item-disabled" data-bind="text: id"></li>
							</ul>
						</div>
						<div class="col-md-3">
							<ul class="kit-families list-group" data-bind="foreach: families">
								<li class="list-group-item list-group-item-disabled">
									<div class="familiy-name" data-bind="text: name"></div>
								</li>
								<li class="list-group-item list-group-item-disabled">
									<div class="familiy-css" data-bind="text: css_stack"></div>
								</li>
							</ul>
						</div>
						<div class="col-md-3">
							<ul class="kit-domains list-group" data-bind="foreach: domains">
								<li class="list-group-item">
									<input type="text" name="domains[]" class="form-control" data-bind="value: $data">
								</li>
							</ul>
							<button class="btn btn-primary" data-bind="click: addNewDomain, visible: domains().length < 10">Add new</button>
						</div>
						<div class="col-md-2">
							<input type="hidden" name="id" data-bind="value: id">
							<div class="btn-group-vertical">
								<button type="submit" name="action" value="edit" class="btn btn-primary">Save</button>
								<button type="submit" name="action" value="duplicate" class="btn btn-success">Duplicate</button>
								<button type="submit" onclick="this.form.showDelConfirm=true;" name="action" value="delete" class="btn btn-danger">Delete</button>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</body>
</html>
