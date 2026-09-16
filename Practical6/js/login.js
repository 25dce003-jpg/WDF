
/* ==========================================
   Student HUB - Login
   ========================================== */


// ==========================================
// 1. WAIT FOR PAGE TO LOAD
// ==========================================

document.addEventListener("DOMContentLoaded", function () {


    // ==========================================
    // 2. GET LOGIN FORM
    // ==========================================

    const loginForm =
        document.getElementById("loginForm");


    // Check if form exists
    if (!loginForm) {

        console.error("Login form not found.");

        return;

    }


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
            document.getElementById("studentpass").value;


        // ==========================================
        // 5. CHECK STUDENT ID EMPTY
        // ==========================================

        if (studentID === "") {

            alert("Please enter your Student ID.");

            return;

        }


        // ==========================================
        // 6. VALIDATE STUDENT ID
        // ==========================================

        const idCheck =
            /^[0-9]{2}[A-Z]{3}[0-9]{3}$/;


        if (!idCheck.test(studentID)) {

            alert(
                "Please enter a valid Student ID. Example: 24CE001"
            );

            return;

        }


        // ==========================================
        // 7. CHECK PASSWORD EMPTY
        // ==========================================

        if (password === "") {

            alert("Please enter your password.");

            return;

        }


        // ==========================================
        // 8. CHECK PASSWORD LENGTH
        // ==========================================

        if (password.length < 6) {

            alert(
                "Password must contain at least 6 characters."
            );

            return;

        }


        // ==========================================
        // 9. SAVE STUDENT ID
        // ==========================================

        localStorage.setItem(
            "userID",
            studentID
        );


        // ==========================================
        // 10. LOGIN SUCCESS
        // ==========================================

        alert("Login Successful!");


        // ==========================================
        // 11. GO TO DASHBOARD
        // ==========================================

        window.location.href = "index.html";


    });

});