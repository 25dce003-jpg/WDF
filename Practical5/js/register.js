// Get form
const form = document.getElementById("registerForm");

form.addEventListener("submit", function(event) {

    event.preventDefault();

    // Get values
    const fullname = document.getElementById("fullname").value.trim();
    const email = document.getElementById("email").value.trim();
    const mobile = document.getElementById("mobile").value.trim();
    const course = document.getElementById("course").value;
    const password = document.getElementById("password").value;
    const confirm = document.getElementById("confirm").value;


    // Name validation
    const namePattern = /^[A-Za-z ]{3,}$/;

    if (!namePattern.test(fullname)) {
        alert("Please enter a valid name.");
        return;
    }


    // Email validation
    const emailPattern = /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/;

    if (!emailPattern.test(email)) {
        alert("Please enter a valid email address.");
        return;
    }


    // Mobile validation
    const mobilePattern = /^[0-9]{10}$/;

    if (!mobilePattern.test(mobile)) {
        alert("Mobile number must contain exactly 10 digits.");
        return;
    }


    // Course validation
    if (course === "") {
        alert("Please select a course.");
        return;
    }


    // Password validation
    const passwordPattern =
        /^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[!@#$%^&*]).{8,}$/;

    if (!passwordPattern.test(password)) {
        alert("Password must contain 8 characters, uppercase, lowercase, number and special character.");
        return;
    }


    // Confirm password validation
    if (password !== confirm) {
        alert("Password and Confirm Password do not match.");
        return;
    }


    // Save student data
    const student = {
        fullname: fullname,
        email: email,
        mobile: mobile,
        course: course,
        password: password
    };

    localStorage.setItem(
        "studentData",
        JSON.stringify(student)
    );

    localStorage.setItem("userID", fullname);


    // Success message
    alert("Registration Successful!");


    // Go to login page
    window.location.href = "login.html";

});


// Reset button
form.addEventListener("reset", function() {
    alert("Form has been reset.");
});

