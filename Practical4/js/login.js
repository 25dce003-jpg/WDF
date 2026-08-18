// ==========================================
// 1. WAIT FOR PAGE TO LOAD
// ==========================================

document.addEventListener("DOMContentLoaded", function () {


    // ==========================================
    // 2. GET LOGIN FORM
    // ==========================================

    const loginForm = document.getElementById("loginForm");


    // ==========================================
    // 3. LOGIN FORM SUBMIT
    // ==========================================

    loginForm.addEventListener("submit", function (event) {

        // Stop page from refreshing
        event.preventDefault();


        // ==========================================
        // 4. GET STUDENT ID AND PASSWORD
        // ==========================================

        const studentID =
            document.getElementById("studentID").value.trim();

        const password =
            document.getElementById("studentpass").value.trim();


        // ==========================================
        // 5. CHECK STUDENT ID
        // ==========================================

        if (studentID === "") {

            alert("Please enter your Student ID.");

            return;
        }


        // ==========================================
        // 6. CHECK PASSWORD
        // ==========================================

        if (password === "") {

            alert("Please enter your password.");

            return;
        }


        // ==========================================
        // 7. CHECK PASSWORD LENGTH
        // ==========================================

        if (password.length < 6) {

            alert("Password must contain at least 6 characters.");

            return;
        }


        // ==========================================
        // 8. SAVE STUDENT ID
        // ==========================================

        localStorage.setItem("userID", studentID);


        // ==========================================
        // 9. LOGIN SUCCESS
        // ==========================================

        alert("Login Successful!");


        // ==========================================
        // 10. GO TO DASHBOARD
        // ==========================================

        window.location.href = "index.html";

    });

});