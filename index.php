<?php
/**
 * Show UPS Status in two tables, system and battery.
 *
 * @name      index.php
 * @version   0.99.1
 * @license   GPL v3 (see enclosed license.txt or <http://www.gnu.org/licenses/>)
 * @copyright DO NOT remove @author or @license or @copyright.
 *            This program is free software: you can redistribute it and/or modify
 *            it under the terms of the GNU General Public License as published by
 *            the Free Software Foundation, either version 3 of the License,
 *            or (at your option) any later version.
 *
 *            This program is distributed in the hope that it will be useful,
 *            but WITHOUT ANY WARRANTY; without even the implied warranty of
 *            MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *            GNU General Public License for more details.
 *
 *            You should have received a copy of the GNU General Public License
 *            along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * @author    Bradley Comerford
 *
 */

/* Include configuration */
if(file_exists("index.php")) {
	include("config.php");
} else {
	die("Error: Config does not exist");
}

/* Define array */
$ups = array();

/* Fetch data from socket */
$fp = fsockopen($config['server'], $config['port'], $errno, $errstr, 30);
if (!$fp) {
	echo "$errstr ($errno)<br />\n";
} else {
	fwrite($fp, "LIST VAR {$config['ups_name']}\nLOGOUT\n");
	while (!feof($fp)) {
		$line = trim(fgets($fp, 128));
		if(substr($line, 0, 2) == 'OK' ) {
			break;
		}

		/* Cut VAR ups */
		$line = str_replace("VAR {$config['ups_name']} ", '', $line);

		/* Write ups data to array... */
		$upsdata 	= explode(" ", $line, 2);
		$upsvar 	= trim(str_replace('"','',$upsdata[0]));
		$upsvalue 	= trim(str_replace('"','',$upsdata[1]));
		$ups[$upsvar] 	= $upsvalue;
	}
}
fclose($fp);
?>

<!DOCTYPE html>
	<html lang="en">
		<head>
			<meta charset="utf-8" />
			<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
			<meta name="description" content="PHP interface for Network UPS Tools" />
			<meta name="author" content="Bradley Comerford" />
			<title><?php echo $config['title']; ?></title>
			<!-- Favicon-->
			<link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
			<!-- Core theme CSS (includes Bootstrap)-->
			<link href="css/styles.css" rel="stylesheet" />
			
			<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
			<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
		</head>
    <body>
		<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
			<div class="container">
				<div class="navbar-brand"><?= $config['title']; ?></div>
				<p class="navbar-text">API Version: <?= $config['version'] ?></p>
			</div>
		</nav>
		<div class="hero-unit">
			<div class="container">
				<br>
				<h4><i class="fa-solid fa-computer"></i> System</h4>
				<table class="table table-striped">
					<?php if(isset($ups['device.serial'])) { ?>
					<tr>
						<td>Serial Number</td>
						<td><?= $ups['device.serial'] ?></td>
					</tr>
					<tr>
						<td>Device Make</td>
						<td><?= $ups['device.mfr'] ?></td>
					</tr>
					<tr>
						<td>Device Model</td>
						<td><?= $ups['device.model'] ?></td>
					</tr>
					<tr>
						<td>UPS Firmware</td>
						<td><?= $ups['ups.firmware'] ?></td>
					</tr>
					<tr>
						<td>UPS Driver</td>
						<td><?= $ups['driver.name'] ?></td>
					</tr>
					<tr>
						<td>Driver Version</td>
						<td><?= $ups['driver.version'] ?></td>
					</tr>
					<?php } if(isset($ups['battery.mfr.date'])) { ?>
					<tr>
						<td>Produktionsdatum Batterie</td>
						<td><?= date('d.m.Y', strtotime($ups['battery.mfr.date'])) ?></td>
					</tr>
					<?php } if(isset($ups['ups.mfr.date'])) { ?>
					<tr>
						<td>Produktionsdatum USV</td>
						<td><?= date('d.m.Y', strtotime($ups['ups.mfr.date'])) ?></td>
					</tr>
					<?php } if(isset($ups['ups.realpower.nominal'])) { ?>
					<tr>
						<td>Output Wattage</td>
						<td><?= $ups['ups.realpower.nominal'] ?> Watt</td>
					</tr>
					<?php } ?>
				</table>
				<h4><i class="fa-solid fa-battery-full"></i> Battery</h4>
				<table class="table table-striped">
					<?php if(isset($ups['ups.status'])) { ?>
					<tr>
						<td><span class="fa-solid fa-power-off" aria-hidden="true"></span> Status</td>
						<td>
						<?php
						switch($ups['ups.status']) {
							case 'OL':
								echo '<span class="badge badge-pill bg-success">Online</span>';
								break;;
							case 'OB DISCHRG':
								echo '<span class="badge badge-pill bg-danger">On Battery</span>';
								break;;
							case 'OL CHRG':
								echo '<span class="badge badge-pill bg-warning">Online (Charging)</span>';
								break;;
							case 'OL CHRG LB':
								echo '<span class="badge badge-pill bg-warning">Laden (Batterie fast leer)</span>';
								break;;
							default:
								echo '<spann class="badge badge-pill bg-info">Unknown</span>';
						}
						?></td>
					</tr>
					<?php } if(isset($ups['battery.charge'])) { ?>
					<tr>
						<td><span class="fa-solid fa-signal" aria-hidden="true"></span> Charge Status</td>
						<td>
							<div class="progress">
								<?php
									if($ups['battery.charge'] < 26) {
										$charged_status = "danger";
									} elseif ($ups['battery.charge'] < 50) {
									$charged_status = "warning";
										} elseif ($ups['battery.charge'] < 75) {
										$charged_status = "info";
									} elseif ($ups['battery.charge'] == 100) {
										$charged_status = "success";
									}
								?>
								<div class="progress-bar progress-bar-striped bg-<?php echo $charged_status; ?>" role="progressbar" aria-valuenow="<?php echo $ups['battery.charge']; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo round($ups['battery.charge'],0); ?>%;"><?php echo round($ups['battery.charge'],0); ?>% charged</div>
							</div>
						</td>
					</tr>
					<?php } if(isset($ups["ups.load"])) { ?>
					<tr>
						<td><span class="fa-solid fa-gauge" aria-hidden="true"></span> UPS Load</td>
						<td>
							<div class="progress">
								<div class="progress-bar bg-info progress-bar-striped" role="progressbar" aria-valuenow="<?php echo $ups["ups.load"] / 100; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo round($ups["ups.load"],0); ?>%;"><?php echo round($ups["ups.load"],0); ?>%</div>
							</div>
						</td>
					</tr>
					<?php } if(isset($ups['battery.runtime'])) { ?>
					<tr>
						<td><span class="fa-solid fa-clock" aria-hidden="true"></span> Load Time</td>
						<td><? echo round($ups['battery.runtime']/60,2); ?> min</td>
					</tr>
					<?php } if(isset($ups['input.voltage'])) { ?>
					<tr>
						<td><span class="fa-solid fa-bolt" aria-hidden="true"></span> Input Voltage</td>
						<td><? echo $ups['input.voltage']; ?> Volt</td>
					</tr>
					<?php } if(isset($ups['output.voltage']) && isset($ups['input.voltage'])) { ?>
					<tr>
						<td><span class="glyphicon glyphicon-minus" aria-hidden="true"></span> Output Voltage</td>
						<td><? echo $ups['output.voltage']; ?> Volt (<?php if($ups['output.voltage']-$ups['input.voltage'] > 0) { echo "+"; } echo $ups['output.voltage']-$ups['input.voltage']; ?>V)</td>
					</tr>
					<?php } if(isset($ups['output.voltage.nominal'])) { ?>
					<tr>
						<td><span class="fa-solid fa-bolt" aria-hidden="true"></span> Battery Voltage</td>
						<td><? echo $ups['output.voltage.nominal']; ?> Volt</td>
					</tr>
					<?php } if(isset($ups['battery.temperature'])) { ?>
					<tr>
						<td><span class="glyphicon glyphicon-fire" aria-hidden="true"></span> Battery Temperature</td>
						<td><? echo $ups['battery.temperature']; ?> °C</td>
					</tr>
					<?php } ?>
				</table>
<?php
#if(isset($config['debug']) && $config['debug'] == 'true') {
#	echo '<div class="debug">';
#        echo '<pre>';
#        var_dump($ups);
#        echo '</pre>';
#	echo '</div>';
#}
?>
			</div>
		<br />
		</div>
		<div class="container">
			<footer class="d-flex flex-wrap justify-content-between align-items-center py-3 my-4 border-top">
				<div class="col-md-4 d-flex align-items-center">
				<span class="mb-3 mb-md-0 text-muted">© <?php echo date('Y') . ' ' . $config['copyright']; ?></span>
				</div>

				<ul class="nav col-md-4 justify-content-end list-unstyled d-flex">
				<li class="ms-3"><a class="text-muted" href="#"><svg class="bi" width="24" height="24"><use xlink:href="#twitter"></use></svg></a></li>
				<li class="ms-3"><a class="text-muted" href="#"><svg class="bi" width="24" height="24"><use xlink:href="#instagram"></use></svg></a></li>
				<li class="ms-3"><a class="text-muted" href="#"><svg class="bi" width="24" height="24"><use xlink:href="#facebook"></use></svg></a></li>
				</ul>
			</footer>
		</div>
	</body>
	<!-- Bootstrap core JS-->

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
        <!-- Core theme JS-->
        <script src="js/scripts.js"></script>
</html>
