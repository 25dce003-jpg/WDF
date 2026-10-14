




document.addEventListener("DOMContentLoaded", function () {


   

    const loginForm =
        document.getElementById("loginForm");


    if (!loginForm) {

        console.error("Login form not found.");

        return;

    }



    loginForm.addEventListener("submit", function (event) {




        const studentID =
            document.getElementById("studentID").value.trim();


        const password =
            document.getElementById("studentpass").value;




        if (studentID === "") {

            event.preventDefault();

            alert("Please enter your Student ID.");

            return;

        }


      

        const idCheck =
            /^[0-9]{2}[A-Z]{3}[0-9]{3}$/;


        if (!idCheck.test(studentID)) {

            event.preventDefault();

            alert(
                "Please enter a valid Student ID. Example: 24CE001"
            );

            return;

        }


  
        if (password === "") {

            event.preventDefault();

            alert("Please enter your password.");

            return;

        }



        if (password.length < 8) {

            event.preventDefault();

            alert(
                "Password must contain at least 8 characters."
            );

            return;

        }



    });

});