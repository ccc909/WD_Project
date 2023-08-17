<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
	<title>Medical Clinic</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="styles.css">
</head>
<body>
	<header>
		<h1>Medical Clinic</h1>
		<div style="float: right;">
			<?php
			if (isset($_SESSION['user_id'])) {
				echo("Welcome " . $_SESSION['username']);
			}
			?>
		</div>
	</header>
	<nav>
		<ul>
			<li><a href="index.php">Home</a></li>
			<li><a href="doctors.php">Doctors</a></li>
			<?php
			if (isset($_SESSION['user_id'])) {
				echo('<li><a href="logout.php">Logout</a></li>');
			} else {
				echo('<li><a href="login_register.php">Login/Register</a></li>');
			}
			?>
		</ul>
	</nav>
	<main>
		<h2>Welcome to our Medical Clinic</h2>
		<p>We provide high-quality medical services to our patients. Our team of experienced doctors and nurses are dedicated to providing personalized care and treatment to meet your healthcare needs.</p>
		<p>Our services include:</p>
		<ul>
			<li>General medical consultations</li>
			<li>Health screenings and vaccinations</li>
			<li>Diagnostic testing</li>
			<li>Specialist referrals</li>
		</ul>
	</main>
</body>
</html>