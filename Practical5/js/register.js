// Get the registration form
const form = document.querySelector("form");

form.addEventListener("submit", function (event) {

    
    event.preventDefault();

    // Get input values
    const fullname = document.getElementById("fullname").value.trim();
    const email = document.getElementById("email").value.trim();
    const mobile = document.getElementById("mobile").value.trim();
    const course = document.getElementById("course").value;
    const password = document.getElementById("password").value;
    const confirm = document.getElementById("confirm").value;

    
    if (fullname.length < 3) {
        alert("Full Name must contain at least 3 characters.");
        return;
    }

   
    const emailPattern = /^[a-zA-Z0-9]+@[a-z]+\.[a-zA-Z]+$/;
    
    if(!emailPattern.test(email)) {
        alert("Please enter a valid Eamil id");
    }

    
    const mobilePattern = /^[0-9]{10}$/;

    if (!mobilePattern.test(mobile)) {
        alert("Please enter a valid 10-digit mobile number.");
        return;
    }

    
    if (password.length < 6) {
        alert("Password must contain at least 6 characters.");
        return;
    }

    
    if (password !== confirm) {
        alert("Password and Confirm Password do not match.");
        return;
    }

    
    const student = {
        fullname: fullname,
        email: email,
        mobile: mobile,
        course: course,
        password: password
    };

    // Save student data
    localStorage.setItem("studentData", JSON.stringify(student));

    // Save username separately
    localStorage.setItem("userID", fullname);

    // Success message
    alert("Registration Successful!");

    // Redirect to login page
    window.location.href = "login.html";
});


// Reset button
form.addEventListener("reset", function () {
    alert("Form has been reset.");
});