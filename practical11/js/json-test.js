
// FETCH STUDENT DATA FROM JSON


fetch("../data/student.json")

    .then(function (response) {

        // Check response
        if (!response.ok) {

            throw new Error("JSON file not found");

        }

        // Convert response into JSON
        return response.json();

    })

    .then(function (students) {

        console.log("Student data loaded successfully");
        console.log(students);

        // Get Student ID from localStorage
        let loggedInID = localStorage.getItem("userID");

        // Temporary ID for testing
        if (!loggedInID) {

            loggedInID = "25DCE001";

        }

        console.log("Logged-in Student ID:", loggedInID);

        // Find student using Student ID
        const student = students.find(function (student) {

            return student.studentID === loggedInID;

        });

        // If student is found
        if (student) {

            // Display student information
            document.getElementById("studentName").textContent =
                student.name;

            document.getElementById("studentCourse").textContent =
                student.course;

            document.getElementById("studentUniversity").textContent =
                student.university;

            document.getElementById("studentSemester").textContent =
                student.semester;

            document.getElementById("studentEmail").textContent =
                student.email;

            document.getElementById("studentMobile").textContent =
                student.mobile;

        }

        // If student is not found
        else {

            document.getElementById("studentName").textContent =
                "Student not found";

            document.getElementById("studentCourse").textContent =
                "-";

            document.getElementById("studentUniversity").textContent =
                "-";

            document.getElementById("studentSemester").textContent =
                "-";

            document.getElementById("studentEmail").textContent =
                "-";

            document.getElementById("studentMobile").textContent =
                "-";

        }

    })

    .catch(function (error) {

        console.log("Error loading student data:", error);

    });