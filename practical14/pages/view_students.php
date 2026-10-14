<?php

$file = "../data/students.csv";

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Student Records</title>

</head>


<body>


<h2>Student Records</h2>


<?php

if (!file_exists($file)) {

    echo "<p>Student records file not found.</p>";

}
else {

    $handle = fopen($file, "r");

    if ($handle !== false) {

        echo "<table border='1' cellpadding='8'>";


        echo "<tr>";

        echo "<th>Student ID</th>";
        echo "<th>Full Name</th>";
        echo "<th>Email</th>";
        echo "<th>Mobile</th>";
        echo "<th>Course</th>";

        echo "</tr>";


        // Skip CSV header row

        fgetcsv($handle);


        while (($row = fgetcsv($handle)) !== false) {

            if (count($row) < 5) {

                continue;

            }


            echo "<tr>";

            echo "<td>"
                . htmlspecialchars($row[0])
                . "</td>";

            echo "<td>"
                . htmlspecialchars($row[1])
                . "</td>";

            echo "<td>"
                . htmlspecialchars($row[2])
                . "</td>";

            echo "<td>"
                . htmlspecialchars($row[3])
                . "</td>";

            echo "<td>"
                . htmlspecialchars($row[4])
                . "</td>";

            echo "</tr>";

        }


        echo "</table>";


        fclose($handle);

    }
    else {

        echo "<p>Unable to open student records file.</p>";

    }

}

?>


</body>

</html>