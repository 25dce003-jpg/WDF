
/* =================================
   Student HUB - Register
   JSON + localStorage
   ================================= */

// Get form
const form = document.getElementById("registerForm");


// Submit event
form.addEventListener("submit", function (event) {

    event.preventDefault();


    // ==============================
    // 1. Get form values
    // ==============================

    const fullname =
        document.getElementById("fullname").value.trim();

    const email =
        document.getElementById("email").value.trim();

    const mobile =
        document.getElementById("mobile").value.trim();

    const course =
        document.getElementById("course").value;

    const password =
        document.getElementById("password").value;

    const confirm =
        document.getElementById("confirm").value;


    // ==============================
    // 2. Name validation
    // ==============================

    const namePattern = /^[A-Za-z ]{3,}$/;

    if (!namePattern.test(fullname)) {

        alert("Please enter a valid name.");

        return;

    }


    // ==============================
    // 3. Email validation
    // ==============================

    const emailPattern =
        /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/;

    if (!emailPattern.test(email)) {

        alert("Please enter a valid email address.");

        return;

    }


    // ==============================
    // 4. Mobile validation
    // ==============================

    const mobilePattern = /^[0-9]{10}$/;

    if (!mobilePattern.test(mobile)) {

        alert("Mobile number must contain exactly 10 digits.");

        return;

    }


    // ==============================
    // 5. Course validation
    // ==============================

    if (course === "") {

        alert("Please select a course.");

        return;

    }


    // ==============================
    // 6. Password validation
    // ==============================

    const passwordPattern =
        /^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[!@#$%^&*]).{8,}$/;

    if (!passwordPattern.test(password)) {

        alert(
            "Password must contain 8 characters, uppercase, lowercase, number and special character."
        );

        return;

    }


    // ==============================
    // 7. Confirm password
    // ==============================

    if (password !== confirm) {

        alert("Password and Confirm Password do not match.");

        return;

    }


    // ==============================
    // 8. Create student object
    // ==============================

    const student = {

        id: Date.now(),

        fullname: fullname,

        email: email,

        mobile: mobile,

        course: course,

        password: password

    };


    // ==============================
    // 9. Get old students
    // ==============================

    let students = JSON.parse(
        localStorage.getItem("studentData") || "[]"
    );


    // ==============================
    // 10. Check duplicate email
    // ==============================

    const emailExists = students.some(
        existingStudent => existingStudent.email === email
    );

    if (emailExists) {

        alert("This email is already registered.");

        return;

    }


    // ==============================
    // 11. Add new student
    // ==============================

    students.push(student);


    // ==============================
    // 12. Convert array to JSON
    // ==============================

    const jsonData = JSON.stringify(students);


    // Show JSON in console
    console.log("Student JSON:", jsonData);


    // ==============================
    // 13. Save JSON in localStorage
    // ==============================

    localStorage.setItem(
        "studentData",
        jsonData
    );


    // Save current user
    localStorage.setItem(
        "userID",
        fullname
    );


    // ==============================
    // 14. Success
    // ==============================

    alert("Registration Successful!");


    // ==============================
    // 15. Go to login page
    // ==============================

    window.location.href = "login.html";

});


// ==============================
// Reset button
// ==============================

form.addEventListener("reset", function () {

    alert("Form has been reset.");

});