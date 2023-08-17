<?php
session_start();

//setup datbase vars
$servername = "localhost";
$username = "root";
$password = "";
$database = "medical_clinic";
$placeholder = base64_encode(file_get_contents("https://img.freepik.com/free-vector/doctor-clinic-illustration_1270-69.jpg")); 

//databse connection
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//add new doctor method
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add"]) && $_SESSION['admin']) {
    $name = $_POST["name"];
    $specialty = $_POST["specialty"];
//setup query
    $sql = "INSERT INTO doctors (name, specialty) VALUES ('$name', '$specialty')";

    if ($conn->query($sql) === true) {
        echo "Doctor added successfully.";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

//remove doctor method
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["remove"]) && $_SESSION['admin']) {
    $doctorId = $_GET["remove"];
//setup query
    $sql = "DELETE FROM doctors WHERE doctor_id = $doctorId";

    if ($conn->query($sql) === true) {
        echo "Doctor removed successfully.";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

//method for docotr edit
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["edit"]) && $_SESSION['admin']) {
    $doctorId = $_POST["doctor_id"];
    $name = $_POST["name"];
    $specialty = $_POST["specialty"];
//using bind_param since it might be faster for images, and this was the example i found for binary file uploading
    $imageData = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {//setup query with image
        $imageData = file_get_contents($_FILES['image']['tmp_name']);
        $sql = "UPDATE doctors SET name = ?, specialty = ?, data = ? WHERE doctor_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $name, $specialty, $imageData, $doctorId);
    } else {//setup query without image
        $sql = "UPDATE doctors SET name = ?, specialty = ? WHERE doctor_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $name, $specialty, $doctorId);
    }
    if ($stmt->execute()) {//run query and check for errors
        echo "Doctor updated successfully.";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

//get list from db
$sql = "SELECT * FROM doctors";
$result = $conn->query($sql);

//close connection
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Doctors - Medical Clinic</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="dstyle.css">

</head>
<body>
    <header>
        <h1>Our Doctors</h1>
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
				if(isset($_SESSION['user_id']))
					echo('<li><a href="logout.php">Logout</a></li>');
				else
					echo('<li><a href="login_register.php">Login/Register</a></li>')
		?>
			
		</ul>
	</nav>
    <main>
    	<?php
if (isset($_SESSION['admin']) && $_SESSION['admin']) {
    //form for adding doctors
    echo '<h2>Add Doctor</h2>';
    echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';
    echo '<div class="form-group">';
    echo '<label for="name">Name:</label>';
    echo '<input type="text" id="name" name="name" required>';
    echo '</div>';
    echo '<div class="form-group">';
    echo '<label for="specialty">Specialty:</label>';
    echo '<select id="specialty" name="specialty">';
    echo '<option value="Surgeon">Surgeon</option>';
    echo '<option value="Nurse">Nurse</option>';
    echo '<option value="ER Personnel">ER Personnel</option>';
    echo '</select>';   
    echo '</div>';
    echo '<div class="form-group">';
    echo '<input type="submit" name="add" value="Add Doctor" class="blue-button">';
    echo '</div>';
    echo '</form>';
}
?>

        <h2>Meet Our Team of Doctors</h2>
        <div class="doctor-grid">
            <?php
            //display the list of doctors

            while ($row = $result->fetch_assoc()) {
                if (isset($row['data'])) {
                    //check if doctor has picture and encode as base64
                    $data = base64_encode($row['data']);
                } else {
                    //else set default image
                    $data = $placeholder;   
                }
                echo '<div class="doctor-item">';
                //add prviously retrived image data as src
                echo '<img src="data:image/jpeg;base64,' . $data . '">';
                echo '<h3>' . $row["name"] . '</h3>';
                echo '<p>Specialty: ' . $row["specialty"] . '</p>';
                if (isset($_SESSION['admin']) && $_SESSION['admin']) {
                    //display edit buttons, and edit menu but keep it hidden
                    echo '<button class="edit-toggle blue-button">Edit</button>';
                    //set display to none to keep it hidden by default
                    echo '<div class="edit-menu" style="display:none;">';
                    echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '" enctype="multipart/form-data">';
                    echo '<input type="hidden" name="doctor_id" value="' . $row["doctor_id"] . '">';
                    echo '<label for="edit-username">Username:</label>';
                    echo '<input type="text" id="edit-username" name="name" value="' . $row["name"] . '" required>';
                    echo '<label for="edit-specialty">Specialty:</label>';
                    echo '<input type="text" id="edit-specialty" name="specialty" value="' . $row["specialty"] . '" required>';
                    echo '<label for="edit-image blue-button">Choose Image:</label>';
                    echo '<div><input type="file" id="edit-image" name="image"></div>';
                    echo '<div><button type="submit" name="edit" class="blue-button">Save</button>';
                    echo '<button type="button" class="cancel-edit blue-button">Cancel</button></div>';
					echo '</form>';
					echo '</div>';
					echo '<div><a href="?remove=' . $row["doctor_id"] . '"><button class="remove-doctor red-button">Remove</button></a></div>';
					}
			echo '</div>';
}
?>
</div>	

<script>
const editToggleLinks = document.querySelectorAll('.edit-toggle');
const cancelEditButtons = document.querySelectorAll('.cancel-edit');
const editMenus = document.querySelectorAll('.edit-menu');
const saveEditButtons = document.querySelectorAll('.save-edit');



        //add listener for toggling edit
        editToggleLinks.forEach((link, index) => {
            link.addEventListener('click', () => {
                link.style.display = 'none'; // Hide the "Edit" button
                link.nextElementSibling.style.display = 'inline';
            });
        });

        //listener for cancel edit
        cancelEditButtons.forEach((button, index) => {
            button.addEventListener('click', () => {
                editMenus[index].style.display = 'none';
                editToggleLinks[index].style.display = 'inline';

            });
        });

        //listener for submit
        saveEditButtons.forEach((button, index) => {
            button.addEventListener('click', () => {
                const form = editMenus[index].querySelector('form');
                form.submit();
            });
        });


    </script>
</main>
</body>
</html>