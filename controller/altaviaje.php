</div>
	<div class="container h-100">
		
<?php

function render($vars = [])
{
	include('php/conexion.php');

	//Si el usuario no esta logeado, no le permite ingresar.
	isset($_SESSION['userId'])?: header('Location: /home');

/* TEST
	if(isset($_POST['intervalo_rep'])){
			echo $_POST['intervalo_rep'];
			if(is_numeric($_POST['intervalo_rep'])){
				echo "OK";
			}
	}
*/
	//Cuando recive el formulario cargado
	$rep = false;
	$ok = true;

	!isset($_POST['localidad_origen'])?:include('php/alta_viaje.php');


	{
		$asientos = array();
		$provincias = [
			'Buenos Aires',
			'Catamarca',
			'Chaco',
			'Chubut',
			'Córdoba',
			'Corrientes',
			'Entre Ríos',
			'Formosa',
			'Jujuy',
			'La Pampa',
			'La Rioja',
			'Mendoza',
			'Misiones',
			'Neuquén',
			'Río Negro',
			'Salta',
			'San Juan',
			'San Luis',
			'Santa Cruz',
			'Santa Fe',
			'Santiago del Estero',
			'Tierra del Fuego',
			'Tucumán'	
		];

		//echo "HOLA :D";
?>

	<script type="text/javascript">
		function estimated_time()
		{
			let loc_or = document.getElementById('localidad_origen').value;
			let prov_or = document.getElementById('prov_origen').value;
			let loc_dest = document.getElementById('localidad_destino').value;
			let prov_dest = document.getElementById('prov_destino').value;

			//document.getElementById('tiempo_estimado').placeholder = 'TEST';
			let service = new google.maps.DistanceMatrixService();
			service.getDistanceMatrix(
			  {
			    origins: [loc_or, prov_or],
			    destinations: [loc_dest, prov_dest],
			    travelMode: 'DRIVING',
			  }, function(a,b){
			  	console.log(a);
			  	console.log(b);
			  	if(b == 'OK')
			  	{
			  		if(a['rows']['0']['elements']['0']['status'] == 'NOT_FOUND')
			  		{
			  			return false;
			  		}
			  		let estimado = a['rows']['0']['elements']['0']['duration']['text'];
			  		document.getElementById('tiempo_estimado').placeholder = 'Tiempo estimado de viaje: ' + estimado;
			  	}
			  });
		}

		function is_valid_location(loc,prov)
		{
				let localidad = document.getElementById(loc).value;
				let provincia = document.getElementById(prov).value;
				return(
					fetch('https://maps.googleapis.com/maps/api/geocode/json?address='+localidad+','+provincia+',+AR&key=AIzaSyBCmsUIxdjkHChho9s5V1T7Xl4axSmR3-w',{method: 'GET'})
						.then(function(response) {
						//console.log(response);
						response.json().then(function(data){
							//console.log(data);
							if(data['status'] == 'OK'){
								if(data['results']['0']['types']['0'] == 'locality')
								{
									document.getElementById(loc).setCustomValidity('');
									estimated_time();
									return true;
								}
							}
							document.getElementById(loc).setCustomValidity('Localidad no encontrada :(');
							console.log(document.getElementById(loc).checkValidity());
							return false
						})
					})
				);
		}

		function mod_asientos()
		{
			var id_v = document.getElementById('vehiculo').value;
			document.getElementById('asientos').max = aux[id_v];
			console.log('id -> ' + id_v);
			console.log('asientos -> ' + aux[id_v]);
		}

		function swtich(id)
		{
			let elem = document.getElementById(id);
			if(elem.hidden)
			{
				elem.hidden = false;
				document.getElementById('intervalo_rep').value="";
				document.getElementById('cant_intervalos').value="";
				return 0
			}

			elem.hidden = true;
			return 1
		}

	</script>

		<div class="row h-100 justify-content-center align-items-center">
			<div class="card bg-white" style="max-width: 600px; margin-top: 20px; margin-bottom: 20px">
				<div class="card-header"><h2 class="text-center">Nuevo Viaje!</h2></div>
				<div class="card-body">
					<form action="/altaviaje" method="post">
						<div class="form-group">
							<label for="prov_origen">Provincia de Origen</label>
							<select class="form-control" onchange="is_valid_location('localidad_origen','prov_origen')" name="prov_origen" id="prov_origen"> 
								<?php
									foreach ($provincias as $each) {
										echo '<option value="' . $each . '">' . $each . '</option>';
									}
								?>
							</select>
						</div>
						
						<div class="form-group">
							<label for="loc_origen">Localidad de Origen</label>
							<input onchange="is_valid_location('localidad_origen','prov_origen')" type="text" name="localidad_origen" class="form-control" id="localidad_origen" placeholder="Ingrese la localidad de origen" required>
						</div>
						<div class="form-group">
							<label for="prov_destino">Provincia de Destino</label>
							<select class="form-control" onchange="is_valid_location('localidad_destino','prov_destino')" name="prov_destino" id="prov_destino"> 
								<?php
									foreach ($provincias as $each) {
										echo '<option value="' . $each . '">' . $each . '</option>';
									}
								?>
							</select>
						</div>
						
						<div class="form-group ">
							<label for="localidad_destino">Localidad de Destino</label>
							<input onchange="is_valid_location('localidad_destino','prov_destino')" type="text" name="localidad_destino" class="form-control" id="localidad_destino" placeholder="Ingrese la localidad de origen" required>
						</div>


						<div class="form-group">
							<label>Fecha de salida</label>
							<input type="date" name="fecha_salida" class="form-control" min="<?php echo date('Y-m-d') ?>" id="fecha_salida" <?php if(isset($fecha_salida)){ echo 'value="' . $fecha_salida . '"';}?> required>
						</div>

						<div class="checkbox">
  							<label><input type="checkbox" id='recurrente' value="" onchange="swtich('interval_div') "> Viaje Recurrente</label>
						</div>

						<div class="form-group" id="interval_div" hidden="">
							<label for="intervalo_rep" >Cada cuantos días?</label>
							<input type="number" name="intervalo_rep" class="form-control" placeholder="Ingrese de cuantos días será el intervalo" value="0" min="0" id="intervalo_rep">
							<label for="cant_intervalos">Cuantas veces se repetirá?</label>
							<input type="number" name="cant_intervalos" id="cant_intervalos" class="form-control" min="0" placeholder="Ingrese la cantidad de veces que se repetira el viaje">
						</div>


						<?php
							$vehiculos = mysqli_query($conexion,"SELECT * from vehiculo where idPropietario = '$_SESSION[userId]' ");
						?>

						<div class="form-group">
							<label for="vehilculos">Selecciona el vehiculo</label>
							<select onchange="mod_asientos()"name="vehiculo" id="vehiculo" class="form-control">
								<?php
									while ($v = mysqli_fetch_array($vehiculos)) {
										echo '<option value="' . $v['idVehiculo'] . '" >' . $v['marca'] . ' ' . $v['modelo'] . ', ' . $v['patente'] . " ( " . $v['cant_asientos'] . " Asientos ) </option>";
										$asientos[$v['idVehiculo']] = $v['cant_asientos'];
									}

								?>
							</select>
						</div>

						<div class="form-group ">
							<label for="asientos">Cantidad de asientos a postular</label>
							<input type="number" class="form-control" name="asientos" min="1" id="asientos" placeholder="Ingrese la cantidad de vacantes que postulará" required>
						</div>

						<div class="form-group ">
							<label for="precio">Costo total del viaje</label>
							<input type="number" class="form-control" name="precio" id="precio" placeholder="Ingrese el valor total del viaje" required>
							<small id="priceHelp" class="form-text text-muted">El precio ingresado sera repartido en la cantidad de asientos que compartas.</small>
						</div>
						
						<div class="form-group">
							<label for="tiempo_estimado">Tiempo estimado de viaje (hh:mm)</label>
							<input type="text" class="form-control" name="tiempo_estimado" id="tiempo_estimado" pattern="[0-9]{2,3}([:]{1}[0-9]{2}){0,1}" placeholder="Ingrese el tiempo estimado de viaje">
						</div>

						<div class="container-fluid" style="margin-top:.5rem; padding: 0">
							<input type="submit" name="registro" value="Registrar!" class="btn btn-success form-control form-control-lg" >
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>

<!-- fin parche = abro container -->
<div class="container">
<script type="text/javascript">
		var aux = [];
		<?php
			foreach ($asientos as $key => $value)
			{
				echo "aux['". $key ."'] = '" . $value ."';" ;
			}
		?>

		mod_asientos();
</script>
<script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBCmsUIxdjkHChho9s5V1T7Xl4axSmR3-w">
    </script>
<?php		
	}
}